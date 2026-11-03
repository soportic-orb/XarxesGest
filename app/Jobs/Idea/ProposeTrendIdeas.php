<?php

declare(strict_types=1);

namespace App\Jobs\Idea;

use App\Actions\Idea\GenerateTrendProposals;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs the trend search off the request: web search plus generation takes a
 * minute or more. One run per workspace at a time, and no retry, since each
 * attempt is billed by the AI provider.
 */
class ProposeTrendIdeas implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    public function __construct(
        public string $workspaceId,
        public ?string $requesterId = null,
    ) {}

    public function uniqueId(): string
    {
        return $this->workspaceId;
    }

    public function handle(GenerateTrendProposals $proposals): void
    {
        $workspace = Workspace::query()->with('owner')->find($this->workspaceId);

        if ($workspace === null || ! $workspace->hasBrandProfile()) {
            return;
        }

        $requester = $this->requesterId !== null
            ? $workspace->members()->whereKey($this->requesterId)->first()
            : null;

        $proposals->execute($workspace, $requester instanceof User ? $requester : null);
    }
}
