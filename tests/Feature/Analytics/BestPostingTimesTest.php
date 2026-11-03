<?php

declare(strict_types=1);

use App\Actions\Analytics\ComputeBestPostingTimes;
use App\Actions\SocialAccount\GeneratePostingSchedule;
use App\Actions\SocialAccount\RegeneratePostingSchedule;
use App\Enums\SocialAccount\Platform;
use App\Jobs\Analytics\BootstrapAccountAnalytics;
use App\Jobs\Analytics\CollectAccountDailySnapshot;
use App\Models\AnalyticsPublication;
use App\Models\AnalyticsPublicationDailySnapshot;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Support\RandomMinute;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Queue::fake([BootstrapAccountAnalytics::class, CollectAccountDailySnapshot::class]);
    $this->app->instance(RandomMinute::class, new class extends RandomMinute
    {
        public function __invoke(): int
        {
            return 17;
        }
    });

    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->user->account_id,
        'user_id' => $this->user->id,
    ]);
    $this->workspace->members()->attach($this->user->id, membershipPivot('admin'));
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
    $this->instagram = SocialAccount::factory()->create([
        'workspace_id' => $this->workspace->id,
        'platform' => Platform::Instagram,
        'timezone' => 'Europe/Madrid',
    ]);
});

/**
 * A measured post `$weeksAgo` weeks back on that local weekday and hour of the channel's zone.
 */
function bestTimesPost(SocialAccount $account, int $weeksAgo, int $dayOfWeek, int $hour, int $engagement, int $reach): void
{
    $local = CarbonImmutable::now($account->timezone)
        ->subWeeks($weeksAgo)
        ->startOfWeek(CarbonImmutable::SUNDAY)
        ->addDays($dayOfWeek)
        ->setTime($hour, 5);

    $publication = AnalyticsPublication::factory()->create([
        'workspace_id' => $account->workspace_id,
        'social_account_id' => $account->id,
        'social_account_key' => $account->id,
        'network' => $account->platform->network(),
        'platform_user_id' => $account->platform_user_id,
        'platform' => $account->platform,
        'provider_published_at' => $local->utc(),
    ]);
    AnalyticsPublicationDailySnapshot::factory()->create([
        'publication_id' => $publication->id,
        'date' => $local->utc()->toDateString(),
        'engagement_count' => $engagement,
        'reach_count' => $reach,
        'exposure_count' => $reach,
    ]);
}

/** Tuesdays at 10:00 do five times better than Fridays at 18:00. */
function seedBestTimes(SocialAccount $account, int $tuesdays = 10, int $fridays = 6): void
{
    foreach (range(1, $tuesdays) as $week) {
        bestTimesPost($account, $week, CarbonImmutable::TUESDAY, 10, 50, 500);
    }
    foreach (range(1, $fridays) as $week) {
        bestTimesPost($account, $week, CarbonImmutable::FRIDAY, 18, 10, 500);
    }
}

test('scores each local day and hour by engagement rate, best slot first', function () {
    seedBestTimes($this->instagram);

    $best = app(ComputeBestPostingTimes::class)->handle($this->instagram);

    expect($best->sampleSize)->toBe(16)
        ->and($best->isReliable())->toBeTrue()
        ->and($best->windows)->toBe([[CarbonImmutable::TUESDAY, 10], [CarbonImmutable::FRIDAY, 18]])
        ->and(data_get($best->grid, '2.10'))->toBe(['score' => 1.0, 'count' => 10])
        ->and(data_get($best->grid, '5.18'))->toBe(['score' => 0.2, 'count' => 6])
        ->and(data_get($best->grid, '1.9'))->toBe(['score' => null, 'count' => 0]);
});

test('posts outside the last six months and of other channels are ignored', function () {
    seedBestTimes($this->instagram);
    bestTimesPost($this->instagram, 40, CarbonImmutable::MONDAY, 8, 999, 1000);
    $other = SocialAccount::factory()->create(['workspace_id' => $this->workspace->id, 'platform' => Platform::Instagram]);
    bestTimesPost($other, 1, CarbonImmutable::MONDAY, 8, 999, 1000);

    $best = app(ComputeBestPostingTimes::class)->handle($this->instagram);

    expect($best->sampleSize)->toBe(16)
        ->and(data_get($best->grid, '1.8.count'))->toBe(0);
});

test('under the minimum sample the times are shown as not reliable yet', function () {
    seedBestTimes($this->instagram, tuesdays: 5, fridays: 2);

    $best = app(ComputeBestPostingTimes::class)->handle($this->instagram);

    expect($best->sampleSize)->toBe(7)
        ->and($best->isReliable())->toBeFalse();
});

test('generating posting times puts the channel best slots first once reliable', function () {
    seedBestTimes($this->instagram);

    $schedule = app(GeneratePostingSchedule::class)->handle(Platform::Instagram, 3, $this->instagram);

    expect($schedule->days()[CarbonImmutable::TUESDAY]['times'])->toContain('10:17')
        ->and($schedule->days()[CarbonImmutable::FRIDAY]['times'])->toContain('18:17')
        ->and($schedule->slotCount())->toBe(3);
});

test('without enough data generating posting times keeps the network windows', function () {
    seedBestTimes($this->instagram, tuesdays: 3, fridays: 1);

    $withAccount = app(GeneratePostingSchedule::class)->handle(Platform::Instagram, 3, $this->instagram);
    $withoutAccount = app(GeneratePostingSchedule::class)->handle(Platform::Instagram, 3);

    expect($withAccount->days())->toBe($withoutAccount->days());
});

test('regenerating a channel schedule uses its measured best times', function () {
    seedBestTimes($this->instagram);

    app(RegeneratePostingSchedule::class)->handle($this->instagram, 2);

    expect($this->instagram->fresh()->posting_schedule->days()[CarbonImmutable::TUESDAY]['times'])->toContain('10:17');
});

test('the channel insights page loads the best times as a deferred prop', function () {
    seedBestTimes($this->instagram);

    $this->actingAs($this->user)
        ->get(route('app.channels.insights', $this->instagram))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('bestTimes')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->where('bestTimes.reliable', true)
                ->where('bestTimes.sample_size', 16)
                ->where('bestTimes.timezone', 'Europe/Madrid')
                ->where('bestTimes.windows.0', ['day' => CarbonImmutable::TUESDAY, 'hour' => 10])));
});
