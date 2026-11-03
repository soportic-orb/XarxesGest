<?php

declare(strict_types=1);

namespace App\Jobs\Analytics;

use App\Actions\Analytics\GenerateMonthlyReport;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Builds one workspace's monthly report off the request: the analytics read
 * and Claude's summary take a while. One run per workspace and month at a
 * time, and no retry, since each attempt is billed by the AI provider.
 */
class GenerateMonthlyReportJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    /**
     * @param  string  $month  The month as `YYYY-MM`.
     */
    public function __construct(
        public string $workspaceId,
        public string $month,
        public ?string $requesterId = null,
    ) {}

    public function uniqueId(): string
    {
        return "{$this->workspaceId}:{$this->month}";
    }

    public function handle(GenerateMonthlyReport $reports): void
    {
        $workspace = Workspace::query()->with('owner')->find($this->workspaceId);

        if ($workspace === null) {
            return;
        }

        $requester = $this->requesterId !== null
            ? $workspace->members()->whereKey($this->requesterId)->first()
            : null;

        $reports->execute(
            $workspace,
            CarbonImmutable::createFromFormat('!Y-m', $this->month, 'UTC'),
            $requester instanceof User ? $requester : null,
        );
    }
}
