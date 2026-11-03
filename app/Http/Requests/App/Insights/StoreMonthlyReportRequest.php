<?php

declare(strict_types=1);

namespace App\Http\Requests\App\Insights;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A finished month, written as `YYYY-MM`; last month when absent.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $currentMonth = now($this->user()->timezone ?? 'UTC')->format('Y-m');

        return [
            'month' => ['nullable', 'date_format:Y-m', "before:{$currentMonth}"],
        ];
    }
}
