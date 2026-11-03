<?php

declare(strict_types=1);

namespace App\Actions\SocialAccount;

use App\Actions\Analytics\ComputeBestPostingTimes;
use App\Enums\SocialAccount\Platform;
use App\Models\SocialAccount;
use App\Support\PostingSchedule;
use App\Support\RandomMinute;

class GeneratePostingSchedule
{
    public function __construct(
        private readonly RandomMinute $randomMinute,
        private readonly ComputeBestPostingTimes $bestPostingTimes,
    ) {}

    /**
     * Picks `$goal` weekly times. With a channel whose own posts are enough to
     * tell (ComputeBestPostingTimes), its best slots come first; the network's
     * recommended windows fill the rest, and are all a new channel gets.
     */
    public function handle(Platform $platform, int $goal, ?SocialAccount $account = null): PostingSchedule
    {
        $goal = max(1, min(PostingSchedule::MAX_GOAL, $goal));
        $schedule = PostingSchedule::empty();

        foreach (array_slice($this->windows($platform, $account), 0, $goal) as [$day, $hour]) {
            $schedule = $schedule->withTime($day, sprintf('%02d:%02d', $hour, ($this->randomMinute)()));
        }

        return $schedule;
    }

    /**
     * Distinct [day, hour] windows, at most PostingSchedule::MAX_TIMES_PER_DAY per day.
     *
     * @return list<array{0: int, 1: int}>
     */
    private function windows(Platform $platform, ?SocialAccount $account): array
    {
        $measured = [];

        if ($account !== null) {
            $best = $this->bestPostingTimes->handle($account);
            $measured = $best->isReliable() ? $best->windows : [];
        }

        $windows = [];
        $perDay = [];

        foreach ([...$measured, ...$platform->recommendedPostingWindows()] as [$day, $hour]) {
            if (isset($windows["{$day}:{$hour}"]) || ($perDay[$day] ?? 0) >= PostingSchedule::MAX_TIMES_PER_DAY) {
                continue;
            }

            $windows["{$day}:{$hour}"] = [$day, $hour];
            $perDay[$day] = ($perDay[$day] ?? 0) + 1;
        }

        return array_values($windows);
    }
}
