<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeThisMonth();
        return [
            'period_start' => $start,
            'period_end' => fake()->dateTimeBetween($start, '+1 month'),
            'payload' => json_encode(['tasks_completed' => rand(10, 50), 'new_users' => rand(1, 5)]),
            'path' => '/reports/report-' . fake()->uuid() . '.pdf',
        ];
    }
}
