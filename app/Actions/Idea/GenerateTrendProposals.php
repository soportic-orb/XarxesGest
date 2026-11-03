<?php

declare(strict_types=1);

namespace App\Actions\Idea;

use App\Actions\Analytics\QueryLatestPublicationSnapshots;
use App\Ai\Agents\TrendProposalGenerator;
use App\Enums\SocialAccount\Platform;
use App\Enums\User\Locale;
use App\Models\Idea;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceLabel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Asks Claude, with web search, for this week's post proposals for the
 * workspace's brand and adds them to the ideas board (no stage, tagged with
 * the proposals label) so the team reviews them like any other idea.
 */
class GenerateTrendProposals
{
    public const int TOP_POSTS = 5;

    public const int TOP_POSTS_LOOKBACK_DAYS = 90;

    public const string LABEL_COLOR = '#7C3AED';

    public function __construct(private readonly QueryLatestPublicationSnapshots $snapshots) {}

    /**
     * @return Collection<int, Idea> the ideas created
     */
    public function execute(Workspace $workspace, ?User $requester = null): Collection
    {
        $author = $requester ?? $workspace->owner;
        if ($author === null) {
            return collect();
        }

        $locale = $author->locale ?? Locale::DEFAULT;
        $today = CarbonImmutable::now($author->timezone ?? 'UTC');

        $response = (new TrendProposalGenerator(
            $locale,
            (array) $workspace->brand_profile,
            $this->topPosts($workspace, $today),
            $this->networks($workspace),
            $today->toDateString(),
        ))->prompt('Find this week\'s opportunities for the brand and propose posts.');

        $label = $this->label($workspace, $locale);

        return collect((array) data_get($response, 'proposals', []))
            ->take(TrendProposalGenerator::PROPOSALS)
            ->map(function (mixed $proposal) use ($workspace, $author, $label): ?Idea {
                $title = Str::limit(trim((string) data_get($proposal, 'title')), 255, '');
                if ($title === '') {
                    return null;
                }

                $network = trim((string) data_get($proposal, 'network'));
                $body = collect([
                    trim((string) data_get($proposal, 'body')),
                    $network !== '' ? "— {$network}" : null,
                    trim((string) data_get($proposal, 'trend')),
                ])->filter()->implode("\n\n");

                return CreateIdea::execute($workspace, $author, [
                    'title' => $title,
                    'body' => $body,
                    'idea_stage_id' => null,
                    'label_ids' => [$label->id],
                ]);
            })
            ->filter()
            ->values();
    }

    /**
     * The workspace's best published posts of the last months, by engagement.
     *
     * @return list<array{network: string, excerpt: string, engagement: int}>
     */
    private function topPosts(Workspace $workspace, CarbonImmutable $today): array
    {
        return $this->snapshots
            ->execute($workspace->id, null, $today->subDays(self::TOP_POSTS_LOOKBACK_DAYS)->utc(), $today->utc())
            ->whereNotNull('metric.engagement_count')
            ->whereNotNull('publication.excerpt')
            ->orderByDesc('metric.engagement_count')
            ->limit(self::TOP_POSTS)
            ->get(['publication.platform', 'publication.excerpt', 'metric.engagement_count'])
            ->map(fn (object $row): array => [
                'network' => Platform::tryFrom((string) $row->platform)?->label() ?? (string) $row->platform,
                'excerpt' => Str::limit(trim((string) $row->excerpt), 280),
                'engagement' => (int) $row->engagement_count,
            ])
            ->values()
            ->all();
    }

    /**
     * Names of the networks the workspace publishes to.
     *
     * @return list<string>
     */
    private function networks(Workspace $workspace): array
    {
        $networks = $workspace->socialAccounts()
            ->get(['id', 'platform'])
            ->map(fn (SocialAccount $account): string => $account->platform->label())
            ->unique()
            ->values()
            ->all();

        return $networks === [] ? [Platform::LinkedIn->label(), Platform::Instagram->label(), Platform::X->label()] : $networks;
    }

    private function label(Workspace $workspace, Locale $locale): WorkspaceLabel
    {
        $name = __('create.ideas.proposals.label', [], $locale->value);

        return $workspace->labels()->firstOrCreate(['name' => $name], ['color' => self::LABEL_COLOR]);
    }
}
