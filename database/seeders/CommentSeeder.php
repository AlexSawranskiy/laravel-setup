<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = Task::all();
        $users = User::all();

        if ($tasks->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No tasks or users found. Please run seeders in the correct order.');
            return;
        }

        foreach ($tasks as $task) {
            Comment::factory(rand(1, 4))->create([
                'task_id' => $task->id,
                'author_id' => $users->random()->id,
            ]);
        }
    }
}
