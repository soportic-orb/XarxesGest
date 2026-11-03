<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MonthlyReport;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlyReport>
 */
class MonthlyReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $month = now('UTC')->subMonthNoOverflow()->startOfMonth();

        return [
            'workspace_id' => Workspace::factory(),
            'month' => $month->toDateString(),
            'timezone' => 'UTC',
            'data' => [
                'range' => ['start' => $month->toDateString(), 'end' => $month->endOfMonth()->toDateString()],
                'previous_range' => ['start' => $month->subMonthNoOverflow()->toDateString(), 'end' => $month->subDay()->toDateString()],
                'summary' => [
                    'posts' => ['value' => 12, 'previous' => 9, 'change' => 33.3],
                    'reactions' => ['value' => 340, 'previous' => 280, 'change' => 21.4],
                ],
                'performance' => [],
                'followers' => ['accounts' => []],
                'top_posts' => [],
            ],
            'summary' => [
                'headline' => fake()->sentence(),
                'highlights' => [fake()->sentence(), fake()->sentence()],
                'recommendations' => [fake()->sentence()],
            ],
            'generated_at' => now(),
        ];
    }

    public function withoutSummary(): static
    {
        return $this->state(fn (): array => ['summary' => null]);
    }
}
