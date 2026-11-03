<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Ai\WriteImagePrompt;
use App\Enums\Media\ImageAspect;
use App\Http\Requests\App\Ai\StoreAiImagePromptRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Laravel\Ai\Exceptions\AiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asks Claude (the text provider) for an image prompt built from the post's
 * text, which the user edits before generating.
 */
class AiImagePromptController extends Controller
{
    public function store(StoreAiImagePromptRequest $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace;

        $this->authorize('createPost', $workspace);

        $gate = Gate::inspect('useAi', $workspace->account);
        if ($gate->denied()) {
            return response()->json(['message' => $gate->message()], Response::HTTP_PAYMENT_REQUIRED);
        }

        try {
            $prompt = WriteImagePrompt::execute(
                $request->string('content')->toString(),
                $request->enum('aspect', ImageAspect::class) ?? ImageAspect::DEFAULT,
            );
        } catch (AiException|RequestException|ConnectionException) {
            return response()->json(['message' => __('posts.composer.ai_image.errors.prompt_failed')], Response::HTTP_BAD_GATEWAY);
        }

        return response()->json(['prompt' => $prompt]);
    }
}
