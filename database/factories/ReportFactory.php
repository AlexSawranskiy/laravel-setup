<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReportFactory extends Factory
{
    protected $model = \App\Models\Report::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = Carbon::parse($startDate)->addDays(rand(1, 30));

        return [
            'name' => $this->faker->sentence(3),
            'period_start' => $startDate,
            'period_end' => $endDate,
            'statistics' => [
                'total_tasks' => $this->faker->numberBetween(10, 100),
                'completed_tasks' => $this->faker->numberBetween(0, 50),
                'pending_tasks' => $this->faker->numberBetween(0, 50),
            ],
            'file_path' => $this->faker->boolean(70) ? 'reports/' . $this->faker->uuid() . '.pdf' : null,
        ];
    }
}
