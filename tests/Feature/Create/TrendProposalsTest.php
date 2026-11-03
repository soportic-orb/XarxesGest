<?php

declare(strict_types=1);

use App\Actions\Idea\GenerateTrendProposals;
use App\Ai\Agents\TrendProposalGenerator;
use App\Enums\SocialAccount\Platform;
use App\Jobs\Idea\ProposeTrendIdeas;
use App\Models\AnalyticsPublication;
use App\Models\AnalyticsPublicationDailySnapshot;
use App\Models\Idea;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Providers\Tools\WebSearch;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    config(['trypost.self_hosted' => false]);

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

function trendBrandProfile(Workspace $workspace): void
{
    $workspace->update(['brand_profile' => [
        'business' => 'Shift planning software for small shops',
        'audience' => 'Shop owners in Catalonia',
        'tone' => 'Close and practical',
    ]]);
}

function trendProposalsFake(): void
{
    TrendProposalGenerator::fake([[
        'proposals' => [
            ['title' => 'New working hours law', 'body' => 'Explain what changes for shops.', 'network' => 'LinkedIn', 'trend' => 'The new law announced this week (BOE).'],
            ['title' => 'Christmas shifts checklist', 'body' => 'A checklist to plan December.', 'network' => 'Instagram', 'trend' => 'Christmas campaign starts.'],
            ['title' => '', 'body' => 'Dropped: no title.', 'network' => 'X', 'trend' => ''],
        ],
    ]]);
}

test('an admin saves the brand profile, trimmed and without empty fields', function () {
    $this->actingAs($this->user)
        ->put(route('app.workspace.brand-profile.update'), [
            'business' => '  Shift planning software  ',
            'audience' => 'Shop owners',
            'tone' => '',
            'topics' => null,
        ])
        ->assertRedirect()
        ->assertSessionHas('flash.success', __('settings.flash.brand_profile_updated'));

    expect($this->workspace->fresh()->brand_profile)->toEqual(['business' => 'Shift planning software', 'audience' => 'Shop owners']);
});

test('clearing every field removes the brand profile', function () {
    trendBrandProfile($this->workspace);

    $this->actingAs($this->user)->put(route('app.workspace.brand-profile.update'), ['business' => ''])->assertRedirect();

    expect($this->workspace->fresh()->brand_profile)->toBeNull()
        ->and($this->workspace->fresh()->hasBrandProfile())->toBeFalse();
});

test('a member who is not an admin cannot change the brand profile', function () {
    $member = workspaceMember($this->workspace);

    $this->actingAs($member)->put(route('app.workspace.brand-profile.update'), ['business' => 'Other'])->assertForbidden();

    expect($this->workspace->fresh()->brand_profile)->toBeNull();
});

test('each brand profile field is bounded', function () {
    $this->actingAs($this->user)
        ->put(route('app.workspace.brand-profile.update'), ['business' => str_repeat('a', 1001)])
        ->assertSessionHasErrors('business');
});

test('asking for proposals queues the search for the workspace', function () {
    Queue::fake();
    trendBrandProfile($this->workspace);

    $this->actingAs($this->user)
        ->postJson(route('app.create.ideas.proposals.store'))
        ->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJsonPath('message', __('create.ideas.proposals.queued'));

    Queue::assertPushed(ProposeTrendIdeas::class, fn (ProposeTrendIdeas $job): bool => $job->workspaceId === $this->workspace->id
        && $job->requesterId === $this->user->id);
});

test('without a brand profile no search is queued', function () {
    Queue::fake();

    $this->actingAs($this->user)
        ->postJson(route('app.create.ideas.proposals.store'))
        ->assertUnprocessable()
        ->assertJsonPath('message', __('create.ideas.proposals.errors.no_brand_profile'));

    Queue::assertNothingPushed();
});

test('a denied AI gate queues nothing', function () {
    Queue::fake();
    trendBrandProfile($this->workspace);
    Gate::before(fn (User $user, string $ability): ?AuthResponse => $ability === 'useAi'
        ? AuthResponse::deny(__('billing.flash.subscription_required'))
        : null);

    $this->actingAs($this->user)
        ->postJson(route('app.create.ideas.proposals.store'))
        ->assertStatus(Response::HTTP_PAYMENT_REQUIRED);

    Queue::assertNothingPushed();
});

test('a user outside the workspace cannot ask for proposals', function () {
    Queue::fake();
    trendBrandProfile($this->workspace);

    $this->actingAs(workspaceOutsider($this->workspace)->fresh())
        ->postJson(route('app.create.ideas.proposals.store'))
        ->assertForbidden();

    Queue::assertNothingPushed();
});

test('proposals land on the board without a stage and with the proposals label', function () {
    trendBrandProfile($this->workspace);
    trendProposalsFake();

    $ideas = app(GenerateTrendProposals::class)->execute($this->workspace, $this->user);

    expect($ideas)->toHaveCount(2)
        ->and(Idea::query()->count())->toBe(2);

    $idea = Idea::query()->where('title', 'New working hours law')->sole();
    expect($idea->idea_stage_id)->toBeNull()
        ->and($idea->user_id)->toBe($this->user->id)
        ->and($idea->body)->toContain('Explain what changes for shops.')
        ->and($idea->body)->toContain('— LinkedIn')
        ->and($idea->body)->toContain('The new law announced this week (BOE).')
        ->and($idea->labels->pluck('name')->all())->toBe([__('create.ideas.proposals.label')]);

    expect($this->workspace->labels()->where('name', __('create.ideas.proposals.label'))->count())->toBe(1);
});

test('the search reads the brand profile, the best posts and the connected networks, with web search', function () {
    trendBrandProfile($this->workspace);
    trendProposalsFake();
    $account = SocialAccount::factory()->create(['workspace_id' => $this->workspace->id, 'platform' => Platform::Instagram]);
    $publication = AnalyticsPublication::factory()->create([
        'workspace_id' => $this->workspace->id,
        'social_account_id' => $account->id,
        'social_account_key' => $account->id,
        'network' => $account->platform->network(),
        'platform_user_id' => $account->platform_user_id,
        'platform' => $account->platform,
        'excerpt' => 'Our best post about rota planning',
        'provider_published_at' => now()->subDays(10),
    ]);
    AnalyticsPublicationDailySnapshot::factory()->create([
        'publication_id' => $publication->id,
        'date' => now()->subDays(9)->toDateString(),
        'engagement_count' => 120,
    ]);

    app(GenerateTrendProposals::class)->execute($this->workspace, $this->user);

    TrendProposalGenerator::assertPrompted(function (AgentPrompt $prompt): bool {
        $instructions = $prompt->agent->instructions();
        $tools = collect($prompt->agent->tools());

        return str_contains($instructions, 'Shift planning software for small shops')
            && str_contains($instructions, 'Close and practical')
            && str_contains($instructions, 'Our best post about rota planning')
            && str_contains($instructions, 'Instagram')
            && $tools->contains(fn (mixed $tool): bool => $tool instanceof WebSearch);
    });
});

test('the job does nothing when the brand profile was removed meanwhile', function () {
    TrendProposalGenerator::fake();

    (new ProposeTrendIdeas($this->workspace->id, $this->user->id))->handle(app(GenerateTrendProposals::class));

    TrendProposalGenerator::assertNeverPrompted();
    expect(Idea::query()->count())->toBe(0);
});

test('the weekly command queues a search per workspace with a brand profile and app access', function () {
    Queue::fake();
    trendBrandProfile($this->workspace);
    Workspace::factory()->create();
    $unpaid = Workspace::factory()->create();
    trendBrandProfile($unpaid);

    $this->artisan('xarxesgest:propose-trend-ideas')->assertSuccessful();

    Queue::assertPushed(ProposeTrendIdeas::class, 1);
    Queue::assertPushed(ProposeTrendIdeas::class, fn (ProposeTrendIdeas $job): bool => $job->workspaceId === $this->workspace->id && $job->requesterId === null);
});
