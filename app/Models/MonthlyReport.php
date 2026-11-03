<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\MonthlyReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A workspace's analytics for one calendar month, frozen when it was generated
 * so it reads the same later, with the summary Claude wrote for it.
 *
 * `data` holds raw values (the Insights summary, performance and followers
 * blocks, and the month's best posts); labels are translated when the report
 * is shown, so every member reads it in their own language.
 *
 * @property CarbonImmutable $month
 * @property array{range: array{start: string, end: string}, previous_range: array{start: string, end: string}, summary: array<string, mixed>, performance: list<array<string, mixed>>, followers: array{accounts: list<array<string, mixed>>}, top_posts: list<array<string, mixed>>} $data
 * @property array{headline: string, highlights: list<string>, recommendations: list<string>}|null $summary
 */
class MonthlyReport extends Model
{
    /** @use HasFactory<MonthlyReportFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'month',
        'timezone',
        'data',
        'summary',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'immutable_date',
            'data' => 'array',
            'summary' => 'array',
            'generated_at' => 'immutable_datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** The month as `YYYY-MM`, the key the pages and the PDF use. */
    public function monthKey(): string
    {
        return $this->month->format('Y-m');
    }
}
