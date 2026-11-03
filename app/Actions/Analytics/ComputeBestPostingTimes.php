<?php

declare(strict_types=1);

namespace App\Actions\Analytics;

use App\Models\SocialAccount;
use App\Support\Analytics\BestPostingTimes;
use App\Support\Analytics\EngagementRate;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Reads the channel's published posts of the last months with their latest
 * metrics and scores each one by its engagement rate (EngagementRate, over the
 * same exposure the insights use), or by its raw engagement when the network
 * reports no exposure for most posts. Scores are averaged per
 * local day and hour of the channel's time zone.
 */
class ComputeBestPostingTimes
{
    public const int LOOKBACK_DAYS = 180;

    public const int MINIMUM_SAMPLE = 15;

    /** A slot needs this many posts to be recommended on its own. */
    public const int MINIMUM_SLOT_SAMPLE = 2;

    public function __construct(
        private readonly ResolveAnalyticsAccountKey $accountKeys,
        private readonly QueryLatestPublicationSnapshots $snapshots,
    ) {}

    public function handle(SocialAccount $account): BestPostingTimes
    {
        $timezone = $account->timezone ?: 'UTC';
        $end = CarbonImmutable::now('UTC');

        $scores = $this->scores($account, $end->subDays(self::LOOKBACK_DAYS), $end);

        $buckets = [];
        foreach ($scores as [$publishedAt, $score]) {
            $local = $publishedAt->setTimezone($timezone);
            $buckets[$local->dayOfWeek][$local->hour][] = $score;
        }

        $averages = [];
        foreach ($buckets as $day => $hours) {
            foreach ($hours as $hour => $values) {
                $averages[$day][$hour] = ['average' => array_sum($values) / count($values), 'count' => count($values)];
            }
        }

        $best = collect($averages)->flatten(1)->max('average') ?: 0.0;

        $grid = [];
        foreach (range(0, 6) as $day) {
            foreach (range(0, 23) as $hour) {
                $slot = data_get($averages, "{$day}.{$hour}");
                $grid[$day][$hour] = [
                    'score' => $slot === null ? null : ($best > 0 ? round(data_get($slot, 'average') / $best, 3) : 0.0),
                    'count' => (int) data_get($slot, 'count', 0),
                ];
            }
        }

        $windows = collect($averages)
            ->flatMap(fn (array $hours, int $day): array => collect($hours)
                ->filter(fn (array $slot): bool => data_get($slot, 'count') >= self::MINIMUM_SLOT_SAMPLE)
                ->map(fn (array $slot, int $hour): array => ['day' => $day, 'hour' => $hour, 'average' => data_get($slot, 'average')])
                ->values()
                ->all())
            ->sortByDesc('average')
            ->map(fn (array $slot): array => [data_get($slot, 'day'), data_get($slot, 'hour')])
            ->values()
            ->all();

        return new BestPostingTimes($scores->count(), self::MINIMUM_SAMPLE, $grid, $windows, $timezone);
    }

    /**
     * One [published at, score] pair per post that has metrics.
     *
     * @return Collection<int, array{0: CarbonImmutable, 1: float}>
     */
    private function scores(SocialAccount $account, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $rows = $this->snapshots
            ->execute($account->workspace_id, [$this->accountKeys->for($account)], $start, $end)
            ->whereNotNull('metric.publication_id')
            ->select([
                'publication.provider_published_at',
                'metric.engagement_count',
                'metric.reactions_count',
                'metric.comments_count',
                'metric.shares_count',
                'metric.saves_count',
                'metric.exposure_count',
                'metric.reach_count',
                'metric.impressions_count',
                'metric.views_count',
            ])
            ->get();

        $withExposure = $rows->filter(fn (object $row): bool => $this->exposure($row) > 0)->count();
        $useRates = $rows->isNotEmpty() && $withExposure * 2 >= $rows->count();

        return $rows
            ->filter(fn (object $row): bool => filled($row->provider_published_at) && (! $useRates || $this->exposure($row) > 0))
            ->map(fn (object $row): array => [
                CarbonImmutable::parse($row->provider_published_at, 'UTC'),
                $useRates ? (float) EngagementRate::of($this->engagement($row), $this->exposure($row)) : (float) $this->engagement($row),
            ])
            ->values();
    }

    private function engagement(object $row): int
    {
        return filled($row->engagement_count)
            ? (int) $row->engagement_count
            : (int) $row->reactions_count + (int) $row->comments_count + (int) $row->shares_count + (int) $row->saves_count;
    }

    private function exposure(object $row): int
    {
        return (int) ($row->exposure_count ?: $row->reach_count ?: $row->impressions_count ?: $row->views_count ?: 0);
    }
}
