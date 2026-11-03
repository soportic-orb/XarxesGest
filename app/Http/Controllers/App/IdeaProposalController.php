<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Jobs\Idea\ProposeTrendIdeas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Propose from trends" on the ideas board: queues the search and answers at
 * once; the proposals land on the board when the job finishes.
 */
class IdeaProposalController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace;

        $this->authorize('createPost', $workspace);

        $gate = Gate::inspect('useAi', $workspace->account);
        if ($gate->denied()) {
            return response()->json(['message' => $gate->message()], Response::HTTP_PAYMENT_REQUIRED);
        }

        if (! $workspace->hasBrandProfile()) {
            return response()->json(['message' => __('create.ideas.proposals.errors.no_brand_profile')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ProposeTrendIdeas::dispatch($workspace->id, $request->user()->id);

        return response()->json(['message' => __('create.ideas.proposals.queued')], Response::HTTP_ACCEPTED);
    }
}
