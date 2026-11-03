<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Enums\Media\ImageAspect;
use App\Enums\Media\Source;
use App\Enums\Media\Type as MediaType;
use App\Models\Media;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Image;

/**
 * Generates one image with the configured image provider and stores it as a
 * temporary upload of the workspace, exactly like an Unsplash pick: the first
 * save of a post adopts it, and an image the user discards expires with the
 * other unused uploads.
 */
final class GenerateImage
{
    public const int TIMEOUT_SECONDS = 120;

    public static function execute(Workspace $workspace, string $prompt, ImageAspect $aspect): Media
    {
        $image = Image::of($prompt)
            ->size($aspect->size())
            ->timeout(self::TIMEOUT_SECONDS)
            ->generate()
            ->firstImage();

        $mime = $image->mime();
        $contents = $image->content();

        if (MediaType::fromMime($mime) !== MediaType::Image || $contents === '' || strlen($contents) > MediaType::Image->maxSizeInBytes()) {
            throw ValidationException::withMessages([
                'prompt' => __('posts.composer.ai_image.errors.generation_failed'),
            ]);
        }

        $temp = tempnam(sys_get_temp_dir(), 'ai_image_');

        try {
            file_put_contents($temp, $contents);

            $extension = Str::after($mime, '/') === 'jpeg' ? 'jpg' : Str::after($mime, '/');

            $media = $workspace->addMediaFromPath(
                $temp,
                "ai-image-{$aspect->value}.{$extension}",
                Media::COLLECTION_UPLOADS,
                ['source' => Source::Ai->value, 'source_meta' => ['prompt' => Str::limit($prompt, 1000, '')]],
                mimeType: $mime,
            );
        } finally {
            @unlink($temp);
        }

        $media->issueUploadToken();

        return $media;
    }
}
