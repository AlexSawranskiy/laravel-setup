<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project_User>
 */
class ProjectUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::all()->random()->id ?? Project::factory(),
            'user_id'    => User::all()->random()->id ?? User::factory(),
            'role'       => $this->faker->randomElement(['admin', 'editor', 'viewer']),
        ];
    }
}
