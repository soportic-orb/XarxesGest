<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Concerns\ResolvesTemperature;
use App\Enums\User\Locale;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebSearch;

/**
 * Searches the web for what is moving in the brand's field this week and
 * proposes posts, grounded in the brand profile and the posts that worked best.
 */
class TrendProposalGenerator implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;
    use ResolvesTemperature;

    public const int PROPOSALS = 6;

    public const int MAX_SEARCHES = 5;

    /**
     * @param  array<string, string>  $brandProfile
     * @param  list<array{network: string, excerpt: string, engagement: int}>  $topPosts
     * @param  list<string>  $networks
     */
    public function __construct(
        public Locale $locale,
        public array $brandProfile,
        public array $topPosts,
        public array $networks,
        public string $today,
    ) {}

    public function temperature(): ?float
    {
        return $this->temperatureFor(0.7);
    }

    public function instructions(): string
    {
        return view('prompts.ideas.trends', [
            'business' => (string) data_get($this->brandProfile, 'business', ''),
            'audience' => (string) data_get($this->brandProfile, 'audience', ''),
            'tone' => (string) data_get($this->brandProfile, 'tone', ''),
            'topics' => (string) data_get($this->brandProfile, 'topics', ''),
            'references' => (string) data_get($this->brandProfile, 'references', ''),
            'top_posts' => $this->topPosts,
            'networks' => $this->networks,
            'today' => $this->today,
            'count' => self::PROPOSALS,
            'language' => $this->locale->promptLanguage(),
        ])->render();
    }

    public function tools(): iterable
    {
        return [new WebSearch(self::MAX_SEARCHES)];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'proposals' => $schema->array()
                ->items($schema->object(fn (JsonSchema $item): array => [
                    'title' => $item->string()->required(),
                    'body' => $item->string()->required(),
                    'network' => $item->string()->required(),
                    'trend' => $item->string()->required(),
                ]))
                ->required(),
        ];
    }
}
