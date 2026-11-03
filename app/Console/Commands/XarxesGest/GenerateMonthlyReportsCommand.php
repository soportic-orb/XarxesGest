<?php

declare(strict_types=1);

namespace App\Console\Commands\XarxesGest;

use App\Jobs\Analytics\GenerateMonthlyReportJob;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Monthly reports: on the first of the month, queues last month's report for
 * every workspace with a channel that may use the app (self-hosted, or an
 * account with app access).
 */
class GenerateMonthlyReportsCommand extends Command
{
    protected $signature = 'xarxesgest:generate-monthly-reports {--month= : The month to report on as YYYY-MM; last month by default}';

    protected $description = 'Queue the monthly analytics report of every workspace with a connected channel';

    public function handle(): int
    {
        $month = (string) ($this->option('month') ?: now('UTC')->subMonthNoOverflow()->format('Y-m'));

        if (! Str::isMatch('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('The month must be written as YYYY-MM.');

            return self::INVALID;
        }

        $queued = 0;

        Workspace::query()
            ->whereHas('socialAccounts')
            ->with('account.subscriptions')
            ->lazyById()
            ->filter(fn (Workspace $workspace): bool => config('trypost.self_hosted') || (bool) $workspace->account?->hasAppAccess())
            ->each(function (Workspace $workspace) use ($month, &$queued): void {
                GenerateMonthlyReportJob::dispatch($workspace->id, $month);
                $queued++;
            });

        $this->info("Queued the {$month} report for {$queued} workspace(s).");

        return self::SUCCESS;
    }
}
