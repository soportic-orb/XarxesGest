<?php

declare(strict_types=1);

use App\Actions\Analytics\GenerateMonthlyReport;
use App\Ai\Agents\MonthlyReportAnalyst;
use App\Enums\SocialAccount\Platform;
use App\Jobs\Analytics\BootstrapAccountAnalytics;
use App\Jobs\Analytics\CollectAccountDailySnapshot;
use App\Jobs\Analytics\GenerateMonthlyReportJob;
use App\Models\AnalyticsPublication;
use App\Models\AnalyticsPublicationDailySnapshot;
use App\Models\MonthlyReport;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Ai\Prompts\AgentPrompt;

beforeEach(function () {
    config(['trypost.self_hosted' => false]);
    Queue::fake([BootstrapAccountAnalytics::class, CollectAccountDailySnapshot::class]);

    $this->user = User::factory()->create(['timezone' => 'UTC']);
    $this->workspace = Workspace::factory()->create([
        'account_id' => $this->user->account_id,
        'user_id' => $this->user->id,
        'name' => 'Torns',
    ]);
    $this->workspace->members()->attach($this->user->id, membershipPivot('admin'));
    $this->user->update(['current_workspace_id' => $this->workspace->id]);
    $this->user = $this->user->fresh();
    subscribeAccount($this->user->account);

    $this->account = SocialAccount::factory()->create([
        'workspace_id' => $this->workspace->id,
        'platform' => Platform::Instagram,
        'username' => 'torns.app',
    ]);
    $this->lastMonth = CarbonImmutable::now('UTC')->subMonthNoOverflow()->startOfMonth();
});

/** A measured Instagram post published on that day of last month. */
function monthlyReportPost(SocialAccount $account, CarbonImmutable $day, string $excerpt, int $reactions): void
{
    $publication = AnalyticsPublication::factory()->create([
        'workspace_id' => $account->workspace_id,
        'social_account_id' => $account->id,
        'social_account_key' => $account->id,
        'network' => $account->platform->network(),
        'platform_user_id' => $account->platform_user_id,
        'platform' => $account->platform,
        'excerpt' => $excerpt,
        'account_username' => $account->username,
        'provider_published_at' => $day->setTime(12, 0),
    ]);
    AnalyticsPublicationDailySnapshot::factory()->create([
        'publication_id' => $publication->id,
        'date' => $day->toDateString(),
        'reactions_count' => $reactions,
        'comments_count' => 3,
        'engagement_count' => $reactions + 3,
        'exposure_count' => 1000,
    ]);
}

function monthlyReportSummaryFake(): void
{
    MonthlyReportAnalyst::fake([[
        'headline' => 'A strong month on Instagram: 2 posts and 123 reactions.',
        'highlights' => ['The rota checklist got the most reactions.', '', 'Engagement grew.'],
        'recommendations' => ['Post another checklist next month.'],
    ]]);
}

test('generating a month stores its numbers, its best posts and the summary Claude wrote', function () {
    monthlyReportSummaryFake();
    monthlyReportPost($this->account, $this->lastMonth->addDays(4), 'Rota checklist for December', 100);
    monthlyReportPost($this->account, $this->lastMonth->addDays(14), 'Behind the scenes', 20);
    monthlyReportPost($this->account, $this->lastMonth->subDays(3), 'From the month before', 999);

    $report = app(GenerateMonthlyReport::class)->execute($this->workspace, $this->lastMonth->addDays(10));

    expect($report->month->toDateString())->toBe($this->lastMonth->toDateString())
        ->and(data_get($report->data, 'range'))->toBe(['start' => $this->lastMonth->toDateString(), 'end' => $this->lastMonth->endOfMonth()->toDateString()])
        ->and(data_get($report->data, 'summary.posts.value'))->toBe(2)
        ->and(data_get($report->data, 'summary.reactions.value'))->toBe(120)
        ->and(data_get($report->data, 'top_posts.0.excerpt'))->toBe('Rota checklist for December')
        ->and(data_get($report->data, 'top_posts.0.username'))->toBe('torns.app')
        ->and(data_get($report->data, 'performance.0.posts.value'))->toBe(2)
        ->and($report->summary)->toBe([
            'headline' => 'A strong month on Instagram: 2 posts and 123 reactions.',
            'highlights' => ['The rota checklist got the most reactions.', 'Engagement grew.'],
            'recommendations' => ['Post another checklist next month.'],
        ]);

    MonthlyReportAnalyst::assertPrompted(function (AgentPrompt $prompt): bool {
        $instructions = $prompt->agent->instructions();

        return str_contains($instructions, 'Torns')
            && str_contains($instructions, 'Rota checklist for December')
            && str_contains($instructions, 'torns.app (Instagram)')
            && ! str_contains($instructions, 'From the month before');
    });
});

test('generating a month again replaces its report', function () {
    monthlyReportSummaryFake();
    MonthlyReport::factory()->create(['workspace_id' => $this->workspace->id, 'month' => $this->lastMonth->toDateString()]);
    monthlyReportPost($this->account, $this->lastMonth->addDays(4), 'Rota checklist', 10);

    app(GenerateMonthlyReport::class)->execute($this->workspace, $this->lastMonth);

    expect($this->workspace->monthlyReports()->count())->toBe(1)
        ->and(data_get($this->workspace->monthlyReports()->sole()->data, 'summary.posts.value'))->toBe(1);
});

test('a month without activity is stored without asking Claude', function () {
    MonthlyReportAnalyst::fake();

    $report = app(GenerateMonthlyReport::class)->execute($this->workspace, $this->lastMonth);

    expect($report->summary)->toBeNull();
    MonthlyReportAnalyst::assertNeverPrompted();
});

test('when Claude fails the report keeps its numbers without a summary', function () {
    MonthlyReportAnalyst::fake(fn () => throw new RuntimeException('Provider down'));
    monthlyReportPost($this->account, $this->lastMonth->addDays(4), 'Rota checklist', 10);

    $report = app(GenerateMonthlyReport::class)->execute($this->workspace, $this->lastMonth);

    expect($report->summary)->toBeNull()
        ->and(data_get($report->data, 'summary.posts.value'))->toBe(1);
});

test('the reports page lists the workspace reports, newest month first', function () {
    $older = MonthlyReport::factory()->create(['workspace_id' => $this->workspace->id, 'month' => $this->lastMonth->subMonthNoOverflow()->toDateString()]);
    $newer = MonthlyReport::factory()->create(['workspace_id' => $this->workspace->id, 'month' => $this->lastMonth->toDateString()]);
    MonthlyReport::factory()->create();

    $this->actingAs($this->user)
        ->get(route('app.insights.reports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('insights/reports/Index')
            ->where('hasData', true)
            ->where('canGenerate', true)
            ->where('lastMonth', $this->lastMonth->format('Y-m'))
            ->has('reports.data', 2)
            ->where('reports.data.0.id', $newer->id)
            ->where('reports.data.0.headline', data_get($newer->summary, 'headline'))
            ->where('reports.data.1.id', $older->id));
});

test('a report shows its summary and translated tables', function () {
    $report = MonthlyReport::factory()->create(['workspace_id' => $this->workspace->id, 'month' => $this->lastMonth->toDateString()]);

    $this->actingAs($this->user)
        ->get(route('app.insights.reports.show', $report))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('insights/reports/Show')
            ->where('report.summary.headline', data_get($report->summary, 'headline'))
            ->where('report.document.meta.0', [__('analytics.reports.workspace'), 'Torns'])
            ->where('report.document.sections.0.title', __('analytics.dashboard.summary'))
            ->where('report.document.sections.3.title', __('analytics.reports.top_posts')));
});

test('a report of another workspace is not found', function () {
    $foreign = MonthlyReport::factory()->create();

    $this->actingAs($this->user)->get(route('app.insights.reports.show', $foreign))->assertNotFound();
    $this->actingAs($this->user)->get(route('app.insights.reports.download', $foreign))->assertNotFound();
});

test('a report downloads as a PDF', function () {
    $report = MonthlyReport::factory()->create(['workspace_id' => $this->workspace->id, 'month' => $this->lastMonth->toDateString()]);

    $response = $this->actingAs($this->user)->get(route('app.insights.reports.download', $report));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload("socialgest-report-{$this->lastMonth->format('Y-m')}.pdf");
    expect($response->streamedContent())->toStartWith('%PDF');
});

test('generating last month on demand queues it for the requester', function () {
    Queue::fake();

    $this->actingAs($this->user)
        ->post(route('app.insights.reports.store'))
        ->assertRedirect()
        ->assertSessionHas('flash.success', __('analytics.reports.queued'));

    Queue::assertPushed(GenerateMonthlyReportJob::class, fn (GenerateMonthlyReportJob $job): bool => $job->workspaceId === $this->workspace->id
        && $job->month === $this->lastMonth->format('Y-m')
        && $job->requesterId === $this->user->id);
});

test('only a finished month can be generated', function () {
    Queue::fake();

    $this->actingAs($this->user)
        ->post(route('app.insights.reports.store'), ['month' => now('UTC')->format('Y-m')])
        ->assertSessionHasErrors('month');

    Queue::assertNothingPushed();
});

test('a denied AI gate queues no report', function () {
    Queue::fake();
    Gate::before(fn (User $user, string $ability): ?AuthResponse => $ability === 'useAi'
        ? AuthResponse::deny(__('billing.flash.subscription_required'))
        : null);

    $this->actingAs($this->user)
        ->post(route('app.insights.reports.store'))
        ->assertRedirect()
        ->assertSessionHas('flash.error', __('billing.flash.subscription_required'));

    Queue::assertNothingPushed();
});

test('the job generates the report in the requester language', function () {
    monthlyReportSummaryFake();
    monthlyReportPost($this->account, $this->lastMonth->addDays(4), 'Rota checklist', 10);
    $this->user->update(['locale' => 'ca']);

    (new GenerateMonthlyReportJob($this->workspace->id, $this->lastMonth->format('Y-m'), $this->user->id))->handle(app(GenerateMonthlyReport::class));

    expect($this->workspace->monthlyReports()->sole()->month->toDateString())->toBe($this->lastMonth->toDateString());
    MonthlyReportAnalyst::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->agent->instructions(), 'Catalan'));
});

test('the monthly command queues last month for every workspace with a channel and app access', function () {
    Queue::fake();
    Workspace::factory()->create();
    $unpaid = Workspace::factory()->create();
    SocialAccount::factory()->create(['workspace_id' => $unpaid->id]);

    $this->artisan('xarxesgest:generate-monthly-reports')->assertSuccessful();

    Queue::assertPushed(GenerateMonthlyReportJob::class, 1);
    Queue::assertPushed(GenerateMonthlyReportJob::class, fn (GenerateMonthlyReportJob $job): bool => $job->workspaceId === $this->workspace->id
        && $job->month === $this->lastMonth->format('Y-m')
        && $job->requesterId === null);
});

test('the monthly command refuses a month that is not YYYY-MM', function () {
    $this->artisan('xarxesgest:generate-monthly-reports', ['--month' => 'last'])->assertFailed();
});
