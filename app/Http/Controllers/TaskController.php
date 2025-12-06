<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;

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

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string'
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $projectId,
            'assignee_id' => $request->assignee_id,
            'status' => $request->status ?? 'new'
        ]);

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
