<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $tasks = Task::where('project_id', $projectId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assignee_id, fn($q) => $q->where('assignee_id', $request->assignee_id))
            ->get();

        return response()->json($tasks);
    }

     public function store(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'assignee_id' => 'required|exists:users,id',
            'status' => 'required|string',
            'priority' => 'required|integer|min:1|max:5',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$project->users->contains($request->assignee_id)) {
            return response()->json(['message' => 'Assignee is not a member of this project'], 422);
        }

        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'author_id' => $request->user()->id,
            'assignee_id' => $request->assignee_id,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        TaskCreated::dispatch($task);

        return response()->json($task, 201);
    }


    public function show(Task $task)
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        $userId = $request->user()->id;

        // Автор задачі або власник проекту
        $canEdit = $task->assignee_id === $userId || $task->project->owner_id === $userId;

        if (!$canEdit) {
            abort(403, 'Forbidden');
        }

        $task->update($request->only('title', 'description', 'assignee_id', 'status'));

        TaskUpdated::dispatch($task);

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        $userId = $request->user()->id;

        $canDelete = $task->assignee_id === $userId || $task->project->owner_id === $userId;

        if (!$canDelete) {
            abort(403, 'Forbidden');
        }

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
