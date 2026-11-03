<?php

declare(strict_types=1);

namespace App\Actions\Analytics;

use App\Ai\Agents\MonthlyReportAnalyst;
use App\Dto\Analytics\DateRange;
use App\Enums\SocialAccount\Platform;
use App\Enums\User\Locale;
use App\Enums\User\WeekStart;
use App\Models\MonthlyReport;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Builds a workspace's report for one calendar month from the same numbers the
 * Insights page shows, asks Claude for its summary and stores both, replacing
 * an earlier report of that month.
 */
class GenerateMonthlyReport
{
    public const int TOP_POSTS = 5;

    private const array SUMMARY_METRICS = [
        'posts', 'followers', 'net_followers', 'reactions', 'comments', 'engagement_rate',
        'impressions', 'reach', 'views', 'clicks', 'shares', 'saves', 'reposts',
    ];

    public function __construct(private readonly BuildWorkspaceAnalyticsReport $analytics) {}

    /**
     * @param  CarbonImmutable  $month  Any day of the month to report on.
     * @param  User|null  $requester  Who asked for it; the summary is written in their language, else the owner's.
     */
    public function execute(Workspace $workspace, CarbonImmutable $month, ?User $requester = null): MonthlyReport
    {
        $author = $requester ?? $workspace->owner;
        $timezone = $author?->timezone ?? 'UTC';
        $first = CarbonImmutable::parse($month->format('Y-m-01'), 'UTC');
        $range = new DateRange($first, $first->endOfMonth(), timezone: $timezone);

        $report = $this->analytics->forRange($workspace, $range, null, null, $author?->week_starts_on ?? WeekStart::DEFAULT);
        $data = $this->data($report);

        return MonthlyReport::query()->updateOrCreate(
            ['workspace_id' => $workspace->id, 'month' => $first->toDateString()],
            [
                'timezone' => $timezone,
                'data' => $data,
                'summary' => $this->summary($workspace, $data, $author?->locale ?? Locale::DEFAULT, $first),
                'generated_at' => now(),
            ],
        );
    }

    /**
     * Keeps the raw values the report shows and nothing tied to the moment
     * (live statuses, avatars, media), so the report reads the same later.
     *
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function data(array $report): array
    {
        $account = fn (array $row): array => [
            'platform' => data_get($row, 'platform'),
            'name' => data_get($row, 'name'),
            'username' => data_get($row, 'username'),
        ];

        return [
            'range' => data_get($report, 'range'),
            'previous_range' => data_get($report, 'previous_range'),
            'summary' => collect((array) data_get($report, 'summary', []))->only(self::SUMMARY_METRICS)->all(),
            'performance' => collect((array) data_get($report, 'performance', []))
                ->map(fn (array $row): array => [
                    ...$account($row),
                    ...collect($row)->only(BuildPublicationAnalyticsReport::PERFORMANCE_METRICS)->all(),
                ])
                ->values()
                ->all(),
            'followers' => [
                'accounts' => collect((array) data_get($report, 'followers.accounts', []))
                    ->map(fn (array $row): array => [
                        ...$account($row),
                        'value' => data_get($row, 'value'),
                        'growth' => data_get($row, 'growth'),
                    ])
                    ->values()
                    ->all(),
            ],
            'top_posts' => collect((array) data_get($report, 'top_posts.reactions', []))
                ->take(self::TOP_POSTS)
                ->map(fn (array $row): array => [
                    ...$account($row),
                    'content_type' => data_get($row, 'content_type'),
                    'published_at' => data_get($row, 'published_at'),
                    'permalink' => data_get($row, 'permalink'),
                    'excerpt' => Str::limit(trim((string) data_get($row, 'excerpt')), 280),
                    'reactions' => data_get($row, 'reactions'),
                    'comments' => data_get($row, 'comments'),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * The summary Claude writes, or null when the month has nothing to read
     * or the AI provider fails: the report still shows its numbers.
     *
     * @param  array<string, mixed>  $data
     * @return array{headline: string, highlights: list<string>, recommendations: list<string>}|null
     */
    private function summary(Workspace $workspace, array $data, Locale $locale, CarbonImmutable $month): ?array
    {
        if (! $this->hasActivity($data)) {
            return null;
        }

        try {
            $response = (new MonthlyReportAnalyst(
                $locale,
                $workspace->name,
                $month->format('Y-m'),
                (array) $workspace->brand_profile,
                collect((array) data_get($data, 'summary'))
                    ->map(fn (mixed $comparison, string $metric): array => [
                        'metric' => $metric,
                        'value' => data_get($comparison, 'value'),
                        'previous' => data_get($comparison, 'previous'),
                        'change' => data_get($comparison, 'change'),
                    ])
                    ->values()
                    ->all(),
                collect((array) data_get($data, 'performance'))
                    ->map(fn (array $row): array => [
                        'channel' => $this->channelName($row),
                        'network' => $this->network($row),
                        'posts' => data_get($row, 'posts.value'),
                        'reactions' => data_get($row, 'reactions.value'),
                        'comments' => data_get($row, 'comments.value'),
                        'engagement_rate' => data_get($row, 'engagement_rate.value'),
                    ])
                    ->all(),
                collect((array) data_get($data, 'top_posts'))
                    ->map(fn (array $row): array => [
                        'channel' => $this->channelName($row),
                        'network' => $this->network($row),
                        'excerpt' => (string) data_get($row, 'excerpt'),
                        'reactions' => data_get($row, 'reactions'),
                        'comments' => data_get($row, 'comments'),
                    ])
                    ->all(),
            ))->prompt('Write the summary of this monthly report.');
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $headline = trim((string) data_get($response, 'headline'));

        return $headline === '' ? null : [
            'headline' => $headline,
            'highlights' => $this->sentences(data_get($response, 'highlights'), MonthlyReportAnalyst::MAX_HIGHLIGHTS),
            'recommendations' => $this->sentences(data_get($response, 'recommendations'), MonthlyReportAnalyst::MAX_RECOMMENDATIONS),
        ];
    }

    /** @param array<string, mixed> $data */
    private function hasActivity(array $data): bool
    {
        return (int) data_get($data, 'summary.posts.value', 0) > 0
            || data_get($data, 'summary.followers.value') !== null;
    }

    /** @return list<string> */
    private function sentences(mixed $items, int $limit): array
    {
        return collect(is_array($items) ? $items : [])
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->take($limit)
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $row */
    private function channelName(array $row): string
    {
        return (string) (data_get($row, 'username') ?: data_get($row, 'name') ?: $this->network($row));
    }

    /** @param array<string, mixed> $row */
    private function network(array $row): string
    {
        return Platform::tryFrom((string) data_get($row, 'platform'))?->label() ?? (string) data_get($row, 'platform');
    }
}
