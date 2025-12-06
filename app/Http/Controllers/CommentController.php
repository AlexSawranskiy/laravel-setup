<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Task;

class CommentController extends Controller
{
    public function index($taskId)
    {
        $comments = Comment::where('task_id', $taskId)->get();
        return response()->json($comments);
    }

    public function store(Request $request, $taskId)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $comment = Comment::create([
            'body' => $request->body,
            'task_id' => $taskId,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'Forbidden');
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }
}
