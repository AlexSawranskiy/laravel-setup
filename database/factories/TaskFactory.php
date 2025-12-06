<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'author_id' => User::factory(),
            'assignee_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'status' => fake()->randomElement(['new', 'in_progress', 'review', 'done']),
            'priority' => fake()->numberBetween(1, 5),
            'due_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ];
    }
}
