<?php

declare(strict_types=1);

namespace App\Enums\Media;

/**
 * Shapes the composer offers when generating an image with AI. Each maps to a
 * size the image model supports; networks crop from there at publish.
 */
enum ImageAspect: string
{
    case Square = 'square';
    case Portrait = 'portrait';
    case Landscape = 'landscape';

    public const self DEFAULT = self::Square;

    /**
     * The `laravel/ai` size alias for this shape.
     */
    public function size(): string
    {
        return match ($this) {
            self::Square => '1:1',
            self::Portrait => '2:3',
            self::Landscape => '3:2',
        };
    }

    public function promptOrientation(): string
    {
        return match ($this) {
            self::Square => 'square',
            self::Portrait => 'vertical (portrait)',
            self::Landscape => 'horizontal (landscape)',
        };
    }
}
