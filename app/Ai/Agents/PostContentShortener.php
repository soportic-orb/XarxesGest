<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Concerns\ResolvesTemperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class PostContentShortener implements Agent
{
    use Promptable;
    use ResolvesTemperature;

    public function __construct(
        public string $platformLabel,
        public int $limit,
    ) {}

    public function temperature(): ?float
    {
        return $this->temperatureFor(0.2);
    }

    public function instructions(): string
    {
        return view('prompts.post_content.shortener', [
            'platform_label' => $this->platformLabel,
            'limit' => $this->limit,
            'target' => max(1, (int) floor($this->limit * 0.95)),
        ])->render();
    }
}
