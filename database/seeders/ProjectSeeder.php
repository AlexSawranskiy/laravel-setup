<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        Project::factory(rand(5, 8))->create([
            'owner_id' => $users->random()->id,
        ])->each(function ($project) use ($users) {

            $projectUsers = $users->random(rand(2, 5));
            foreach ($projectUsers as $user) {
                $project->users()->attach($user->id, ['role' => fake()->randomElement(['member', 'manager'])]);
            }

            if (!$project->users->contains($project->owner_id)) {
                $project->users()->attach($project->owner_id, ['role' => 'owner']);
            }
        });
    }
}
