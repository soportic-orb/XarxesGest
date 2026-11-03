<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Ai\Agents\ImagePromptWriter;
use App\Enums\Media\ImageAspect;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class WriteImagePrompt
{
    public static function execute(string $postContent, ImageAspect $aspect): string
    {
        $response = (new ImagePromptWriter($postContent, $aspect))
            ->prompt('Write the image prompt for this post.');

        $prompt = Str::limit(trim((string) $response), ImagePromptWriter::MAX_LENGTH, '');

        if ($prompt === '') {
            throw ValidationException::withMessages([
                'content' => __('posts.composer.ai_image.errors.prompt_failed'),
            ]);
        }

        return $prompt;
    }
}
