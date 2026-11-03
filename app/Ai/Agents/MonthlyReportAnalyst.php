<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Concerns\ResolvesTemperature;
use App\Enums\User\Locale;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

/**
 * Reads a workspace's numbers for one month and writes the summary at the top
 * of the monthly report: a headline, what stood out and what to try next.
 */
class MonthlyReportAnalyst implements Agent, HasStructuredOutput
{
    use Promptable;
    use ResolvesTemperature;

    public const int MAX_HIGHLIGHTS = 4;

    public const int MAX_RECOMMENDATIONS = 3;

    /**
     * @param  array<string, string>  $brandProfile
     * @param  list<array{metric: string, value: int|float|null, previous: int|float|null, change: int|float|null}>  $metrics
     * @param  list<array{channel: string, network: string, posts: int|float|null, reactions: int|float|null, comments: int|float|null, engagement_rate: int|float|null}>  $channels
     * @param  list<array{channel: string, network: string, excerpt: string, reactions: int|null, comments: int|null}>  $topPosts
     */
    public function __construct(
        public Locale $locale,
        public string $workspaceName,
        public string $period,
        public array $brandProfile,
        public array $metrics,
        public array $channels,
        public array $topPosts,
    ) {}

    public function temperature(): ?float
    {
        return $this->temperatureFor(0.4);
    }

    public function instructions(): string
    {
        return view('prompts.reports.monthly', [
            'workspace' => $this->workspaceName,
            'period' => $this->period,
            'business' => (string) data_get($this->brandProfile, 'business', ''),
            'audience' => (string) data_get($this->brandProfile, 'audience', ''),
            'metrics' => $this->metrics,
            'channels' => $this->channels,
            'top_posts' => $this->topPosts,
            'highlights' => self::MAX_HIGHLIGHTS,
            'recommendations' => self::MAX_RECOMMENDATIONS,
            'language' => $this->locale->promptLanguage(),
        ])->render();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'headline' => $schema->string()->required(),
            'highlights' => $schema->array()->items($schema->string())->required(),
            'recommendations' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
