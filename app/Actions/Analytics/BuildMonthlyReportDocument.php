<?php

declare(strict_types=1);

namespace App\Actions\Analytics;

use App\Enums\SocialAccount\Platform;
use App\Models\MonthlyReport;
use Carbon\CarbonImmutable;

/**
 * Turns a stored monthly report into the title, meta and tables the report
 * page and its PDF show, translated in the current locale.
 */
class BuildMonthlyReportDocument
{
    public function __construct(private readonly BuildInsightsExport $export) {}

    /**
     * @param  bool  $compact  Fewer columns, for the PDF's page width.
     * @return array{title: string, meta: list<array{0: string, 1: string}>, sections: list<array{title: string, headers: list<string>, rows: list<list<int|float|string|null>>}>}
     */
    public function execute(MonthlyReport $report, bool $compact = false): array
    {
        $data = $report->data;

        return [
            'title' => __('analytics.reports.document_title', ['month' => $this->monthName($report)]),
            'meta' => [
                [__('analytics.reports.workspace'), (string) $report->workspace?->name],
                [__('analytics.insights.export.range'), $this->span(data_get($data, 'range'))],
                [__('analytics.insights.export.compared_to'), $this->span(data_get($data, 'previous_range'))],
                [__('analytics.insights.export.generated_at'), $report->generated_at->setTimezone($report->timezone)->format('Y-m-d H:i')],
            ],
            'sections' => [
                ...$this->export->tables($data, $compact),
                $this->topPosts((array) data_get($data, 'top_posts', []), $report->timezone, $compact),
            ],
        ];
    }

    /** The month in the current locale: "October 2026", "octubre 2026". */
    public function monthName(MonthlyReport $report): string
    {
        return $report->month->locale(app()->getLocale())->translatedFormat('F Y');
    }

    /**
     * @param  list<array<string, mixed>>  $posts
     * @return array{title: string, headers: list<string>, rows: list<list<int|float|string|null>>}
     */
    private function topPosts(array $posts, string $timezone, bool $compact): array
    {
        return [
            'title' => __('analytics.reports.top_posts'),
            'headers' => [
                __('analytics.insights.export.published_at'),
                __('analytics.dashboard.channel'),
                __('analytics.insights.export.network'),
                __('analytics.insights.export.text'),
                ...($compact ? [] : [__('analytics.insights.export.link')]),
                __('analytics.channel.metrics.reactions.label'),
                __('analytics.channel.metrics.comments.label'),
            ],
            'rows' => array_map(fn (array $post): array => [
                filled(data_get($post, 'published_at'))
                    ? CarbonImmutable::parse((string) data_get($post, 'published_at'), 'UTC')->setTimezone($timezone)->format('Y-m-d H:i')
                    : null,
                (string) (data_get($post, 'username') ?: data_get($post, 'name') ?: $this->network($post)),
                $this->network($post),
                (string) data_get($post, 'excerpt'),
                ...($compact ? [] : [data_get($post, 'permalink')]),
                data_get($post, 'reactions'),
                data_get($post, 'comments'),
            ], $posts),
        ];
    }

    /** @param array<string, mixed> $post */
    private function network(array $post): string
    {
        return Platform::tryFrom((string) data_get($post, 'platform'))?->label() ?? (string) data_get($post, 'platform');
    }

    /** @param array{start: string, end: string}|null $range */
    private function span(?array $range): string
    {
        return $range === null ? '' : data_get($range, 'start').' – '.data_get($range, 'end');
    }
}
