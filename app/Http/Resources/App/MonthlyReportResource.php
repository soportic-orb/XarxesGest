<?php

declare(strict_types=1);

namespace App\Http\Resources\App;

use App\Models\MonthlyReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MonthlyReport
 */
class MonthlyReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->monthKey(),
            'headline' => data_get($this->summary, 'headline'),
            'posts' => data_get($this->data, 'summary.posts.value'),
            'generated_at' => $this->generated_at->toIso8601String(),
        ];
    }
}
