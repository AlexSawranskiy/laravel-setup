<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Project;

class CheckProjectAccess
{
    /**
     * Перевіряє, чи має користувач доступ до проекту.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Отримуємо проект за id з маршруту
        $project = Project::find($request->route('id'));

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $userId = $request->user()->id;

        // Перевірка доступу: власник або учасник проекту
        $hasAccess = $project->owner_id === $userId || $project->users()->where('users.id', $userId)->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
