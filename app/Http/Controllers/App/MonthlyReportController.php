<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Analytics\BuildMonthlyReportDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Insights\StoreMonthlyReportRequest;
use App\Http\Resources\App\MonthlyReportResource;
use App\Jobs\Analytics\GenerateMonthlyReportJob;
use App\Models\MonthlyReport;
use App\Support\Analytics\InsightsExportWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Monthly reports under Insights: the list, one report with Claude's summary,
 * its PDF, and generating a month on demand (the 1st of each month generates
 * last month's on its own).
 */
class MonthlyReportController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->user()->currentWorkspace;
        $this->authorize('view', $workspace);

        return Inertia::render('insights/reports/Index', [
            'reports' => Inertia::scroll(fn () => MonthlyReportResource::collection(
                $workspace->monthlyReports()
                    ->orderByDesc('month')
                    ->paginate((int) config('app.pagination.default')),
            )),
            'hasData' => $workspace->monthlyReports()->exists(),
            'canGenerate' => $this->canGenerate($request),
            'lastMonth' => now($request->user()->timezone ?? 'UTC')->subMonthNoOverflow()->format('Y-m'),
        ]);
    }

    public function show(Request $request, MonthlyReport $report, BuildMonthlyReportDocument $document): Response
    {
        $this->authorizeReport($request, $report);

        return Inertia::render('insights/reports/Show', [
            'report' => [
                ...MonthlyReportResource::make($report)->resolve($request),
                'summary' => $report->summary,
                'document' => $document->execute($report),
            ],
            'canGenerate' => $this->canGenerate($request),
        ]);
    }

    public function download(Request $request, MonthlyReport $report, BuildMonthlyReportDocument $document): StreamedResponse
    {
        $this->authorizeReport($request, $report);

        $pdf = InsightsExportWriter::pdf($document->execute($report, compact: true), $report->summary);

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf;
            },
            "socialgest-report-{$report->monthKey()}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function store(StoreMonthlyReportRequest $request): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace;
        $this->authorize('createPost', $workspace);

        $gate = Gate::inspect('useAi', $workspace->account);
        if ($gate->denied()) {
            return back()->with('flash.error', $gate->message());
        }

        $month = (string) ($request->validated('month') ?? now($request->user()->timezone ?? 'UTC')->subMonthNoOverflow()->format('Y-m'));

        GenerateMonthlyReportJob::dispatch($workspace->id, $month, $request->user()->id);

        return back()->with('flash.success', __('analytics.reports.queued'));
    }

    private function authorizeReport(Request $request, MonthlyReport $report): void
    {
        $workspace = $request->user()->currentWorkspace;
        $this->authorize('view', $workspace);

        abort_unless($report->workspace_id === $workspace->id, HttpResponse::HTTP_NOT_FOUND);
    }

    private function canGenerate(Request $request): bool
    {
        $workspace = $request->user()->currentWorkspace;

        return $request->user()->can('createPost', $workspace)
            && Gate::allows('useAi', $workspace->account);
    }
}
