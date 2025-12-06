<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Middleware\CheckProjectAccess;

// User routes
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [UserController::class, 'logout']);

// Project routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->middleware(CheckProjectAccess::class);
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->middleware(CheckProjectAccess::class);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->middleware(CheckProjectAccess::class);

    // Tasks within projects
    Route::get('/projects/{id}/tasks', [TaskController::class, 'index'])->middleware(CheckProjectAccess::class);
    Route::post('/projects/{id}/tasks', [TaskController::class, 'store'])->middleware(CheckProjectAccess::class);
});

// Task routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Comments for tasks
    Route::get('/tasks/{id}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{id}/comments', [CommentController::class, 'store']);
});

// Delete comment
Route::middleware('auth:sanctum')->delete('/comments/{id}', [CommentController::class, 'destroy']);

