<?php

declare(strict_types=1);

namespace App\Support\Analytics;

/**
 * When a channel's own posts did best, by local day of week (0 = Sunday, as in
 * PostingSchedule) and hour. Scores are averaged per slot and scaled so the
 * best slot is 1.0; a slot without posts has no score.
 */
final readonly class BestPostingTimes
{
    /**
     * @param  array<int, array<int, array{score: float|null, count: int}>>  $grid  [day][hour]
     * @param  list<array{0: int, 1: int}>  $windows  [day, hour] pairs, best first
     */
    public function __construct(
        public int $sampleSize,
        public int $minimumSample,
        public array $grid,
        public array $windows,
        public string $timezone,
    ) {}

    public function isReliable(): bool
    {
        return $this->sampleSize >= $this->minimumSample;
    }

    /**
     * @return array{sample_size: int, minimum_sample: int, reliable: bool, timezone: string, grid: array<int, array<int, array{score: float|null, count: int}>>, windows: list<array{day: int, hour: int}>}
     */
    public function toArray(): array
    {
        return [
            'sample_size' => $this->sampleSize,
            'minimum_sample' => $this->minimumSample,
            'reliable' => $this->isReliable(),
            'timezone' => $this->timezone,
            'grid' => $this->grid,
            'windows' => array_map(fn (array $window): array => ['day' => $window[0], 'hour' => $window[1]], $this->windows),
        ];
    }
}
