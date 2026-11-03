<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Concerns\ResolvesTemperature;
use App\Enums\Media\ImageAspect;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * Turns a post's text into a prompt for the image model. The prompt is always
 * written in English (image models follow it best); the user reads and edits
 * it before anything is generated.
 */
class ImagePromptWriter implements Agent
{
    use Promptable;
    use ResolvesTemperature;

    public const int MAX_LENGTH = 1000;

    public function __construct(
        public string $postContent,
        public ImageAspect $aspect,
    ) {}

    public function temperature(): ?float
    {
        return $this->temperatureFor(0.7);
    }

    public function instructions(): string
    {
        return view('prompts.post_image.generator', [
            'post_content' => $this->postContent,
            'orientation' => $this->aspect->promptOrientation(),
            'max_length' => self::MAX_LENGTH,
        ])->render();
    }
}
