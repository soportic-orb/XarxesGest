<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Concerns\ResolvesTemperature;
use App\Enums\User\Locale;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class IdeaGenerator implements Agent, HasStructuredOutput
{
    use Promptable;
    use ResolvesTemperature;

    public function __construct(
        public Locale $locale,
        public string $business,
        public string $audience,
        public ?string $notes = null,
    ) {}

    public function temperature(): ?float
    {
        return $this->temperatureFor(0.8);
    }

    public function instructions(): string
    {
        return view('prompts.ideas.generator', [
            'business' => $this->business,
            'audience' => $this->audience,
            'notes' => $this->notes,
            'language' => $this->locale->promptLanguage(),
        ])->render();
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'body' => $schema->string()->required(),
        ];
    }
}
