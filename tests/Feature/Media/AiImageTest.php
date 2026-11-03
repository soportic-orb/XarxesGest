<?php

declare(strict_types=1);

use App\Ai\Agents\ImagePromptWriter;
use App\Enums\Media\Source;
use App\Enums\Media\Type as MediaType;
use App\Models\Media;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Image;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Prompts\ImagePrompt;
use Symfony\Component\HttpFoundation\Response;

/** A valid 1×1 PNG, so the stored file is a real image. */
const AI_IMAGE_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

beforeEach(function () {
    Storage::fake(config('filesystems.default'));
    config([
        'trypost.self_hosted' => false,
        'ai.default_for_images' => 'openai',
        'ai.providers.openai.key' => 'sk-test',
    ]);

    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->user->account_id,
        'user_id' => $this->user->id,
    ]);
    $this->workspace->members()->attach($this->user->id, membershipPivot('admin'));
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
    $this->user = $this->user->fresh();
    subscribeAccount($this->user->account);
});

test('generates an image and stores it as a temporary upload of the workspace', function () {
    Image::fake([AI_IMAGE_PNG]);

    $response = $this->actingAs($this->user)
        ->postJson(route('app.media.ai.store'), ['prompt' => 'A bright coworking space, editorial photo', 'aspect' => 'portrait'])
        ->assertCreated()
        ->assertJsonPath('meta.source', Source::Ai->value)
        ->assertJsonPath('meta.source_meta.prompt', 'A bright coworking space, editorial photo');

    $media = Media::query()->sole();

    expect($media->collection)->toBe(Media::COLLECTION_UPLOADS)
        ->and($media->type)->toBe(MediaType::Image)
        ->and($response->json('upload_token'))->not->toBeNull();
    Storage::disk(config('filesystems.default'))->assertExists($media->path);
    Image::assertGenerated(fn (ImagePrompt $prompt): bool => $prompt->prompt === 'A bright coworking space, editorial photo'
        && $prompt->size === '2:3');
});

test('the prompt and a known shape are required', function (array $payload, string $field) {
    Image::fake();

    $this->actingAs($this->user)->postJson(route('app.media.ai.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

    Image::assertNothingGenerated();
})->with([
    'no prompt' => [['aspect' => 'square'], 'prompt'],
    'unknown shape' => [['prompt' => 'A red bicycle', 'aspect' => 'panorama'], 'aspect'],
]);

test('without an image provider key the generator is not available', function () {
    Image::fake();
    config(['ai.providers.openai.key' => null]);

    $this->actingAs($this->user)
        ->postJson(route('app.media.ai.store'), ['prompt' => 'A red bicycle', 'aspect' => 'square'])
        ->assertNotFound();

    Image::assertNothingGenerated();
});

test('a denied AI gate returns 402 and generates nothing', function () {
    Image::fake();
    Gate::before(fn (User $user, string $ability): ?AuthResponse => $ability === 'useAi'
        ? AuthResponse::deny(__('billing.flash.subscription_required'))
        : null);

    $this->actingAs($this->user)
        ->postJson(route('app.media.ai.store'), ['prompt' => 'A red bicycle', 'aspect' => 'square'])
        ->assertStatus(Response::HTTP_PAYMENT_REQUIRED);

    Image::assertNothingGenerated();
    expect(Media::query()->count())->toBe(0);
});

test('a provider failure is reported without storing anything', function () {
    Image::fake(fn () => throw new ProviderOverloadedException('overloaded'));

    $this->actingAs($this->user)
        ->postJson(route('app.media.ai.store'), ['prompt' => 'A red bicycle', 'aspect' => 'square'])
        ->assertStatus(Response::HTTP_BAD_GATEWAY)
        ->assertJsonPath('message', __('posts.composer.ai_image.errors.generation_failed'));

    expect(Media::query()->count())->toBe(0);
});

test('a user outside the workspace cannot generate images', function () {
    Image::fake();
    $outsider = workspaceOutsider($this->workspace);

    $this->actingAs($outsider->fresh())
        ->postJson(route('app.media.ai.store'), ['prompt' => 'A red bicycle', 'aspect' => 'square'])
        ->assertForbidden();

    Image::assertNothingGenerated();
});

test('writes an image description from the post text', function () {
    ImagePromptWriter::fake(['A team planning shifts on a whiteboard, editorial photo']);

    $this->actingAs($this->user)
        ->postJson(route('app.media.ai.prompt'), ['content' => 'New shift planner for small shops', 'aspect' => 'landscape'])
        ->assertOk()
        ->assertExactJson(['prompt' => 'A team planning shifts on a whiteboard, editorial photo']);

    ImagePromptWriter::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->agent->instructions(), 'New shift planner for small shops')
        && str_contains($prompt->agent->instructions(), 'horizontal'));
});

test('the description needs the post text first', function () {
    ImagePromptWriter::fake();

    $this->actingAs($this->user)
        ->postJson(route('app.media.ai.prompt'), ['content' => '', 'aspect' => 'square'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['content' => __('posts.composer.ai_image.errors.content_required')]);

    ImagePromptWriter::assertNeverPrompted();
});
