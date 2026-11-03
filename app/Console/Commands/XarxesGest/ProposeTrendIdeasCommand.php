<?php

declare(strict_types=1);

namespace App\Console\Commands\XarxesGest;

use App\Jobs\Idea\ProposeTrendIdeas;
use App\Models\Workspace;
use Illuminate\Console\Command;

/**
 * Weekly trend proposals: queues one search per workspace that has a brand
 * profile and may use AI (self-hosted, or an account with app access, the
 * same rule as AccountPolicy::useAi).
 */
class ProposeTrendIdeasCommand extends Command
{
    protected $signature = 'xarxesgest:propose-trend-ideas';

    protected $description = 'Queue this week\'s trend-based post proposals for every workspace with a brand profile';

    public function handle(): int
    {
        $queued = 0;

        Workspace::query()
            ->whereNotNull('brand_profile')
            ->with('account.subscriptions')
            ->lazyById()
            ->filter(fn (Workspace $workspace): bool => $workspace->hasBrandProfile()
                && (config('trypost.self_hosted') || (bool) $workspace->account?->hasAppAccess()))
            ->each(function (Workspace $workspace) use (&$queued): void {
                ProposeTrendIdeas::dispatch($workspace->id);
                $queued++;
            });

        $this->info("Queued trend proposals for {$queued} workspace(s).");

        return self::SUCCESS;
    }
}
