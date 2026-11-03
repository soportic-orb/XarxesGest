<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Ai\GenerateImage;
use App\Enums\Media\ImageAspect;
use App\Enums\Media\Source;
use App\Enums\PostHog\AiEvent;
use App\Http\Requests\App\Ai\StoreAiImageRequest;
use App\Http\Resources\App\MediaResource;
use App\Jobs\PostHog\CaptureAccountEvent;
use App\Services\PostHogService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Laravel\Ai\Exceptions\AiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates an image for the composer and returns it as a temporary upload,
 * the same shape the Unsplash pick returns.
 */
class AiImageController extends Controller
{
    public function store(StoreAiImageRequest $request): MediaResource|JsonResponse
    {
        $workspace = $request->user()->currentWorkspace;

        $this->authorize('createPost', $workspace);

        $gate = Gate::inspect('useAi', $workspace->account);
        if ($gate->denied()) {
            return response()->json(['message' => $gate->message()], Response::HTTP_PAYMENT_REQUIRED);
        }

        abort_unless(Source::Ai->isEnabled(), Response::HTTP_NOT_FOUND);

        $aspect = $request->enum('aspect', ImageAspect::class) ?? ImageAspect::DEFAULT;

        try {
            $media = GenerateImage::execute($workspace, $request->string('prompt')->toString(), $aspect);
        } catch (AiException|RequestException|ConnectionException) {
            return response()->json(['message' => __('posts.composer.ai_image.errors.generation_failed')], Response::HTTP_BAD_GATEWAY);
        }

        rescue(function () use ($request, $workspace, $aspect): void {
            CaptureAccountEvent::dispatchIf(
                PostHogService::isEnabled(),
                (string) $request->user()->id,
                (string) $workspace->account_id,
                AiEvent::ImageGenerated,
                [
                    'aspect' => $aspect->value,
                    'workspace_id' => (string) $workspace->id,
                ],
            );
        });

        return new MediaResource($media);
    }
}
