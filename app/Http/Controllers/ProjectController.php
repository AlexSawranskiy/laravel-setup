<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('owner_id', $request->user()->id)
            ->orWhereHas('users', fn($q) => $q->where('users.id', $request->user()->id))
            ->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $request->user()->id,
        ]);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project, $request->user()->id);

        $project->update($request->only('name', 'description'));
        return response()->json($project);
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project, $request->user()->id);

        $project->delete();
        return response()->json(['message' => 'Project deleted']);
    }

    private function authorizeProjectOwner(Project $project, $userId)
    {
        if ($project->owner_id !== $userId) {
            abort(403, 'Forbidden');
        }
    }
}
