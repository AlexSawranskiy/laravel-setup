<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommentNotificationListener
{
    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $comment->load(['task', 'user']); 
        
        $task = $comment->task;
        $author = $comment->user;

        $message = "<b>Новий коментар!</b>\n\n" .
                   "<b>Задача:</b> #{$task->id} {$task->title}\n" .
                   "<b>Автор:</b> {$author->name}\n" .
                   "<b>Текст:</b> <i>{$comment->body}</i>";

        SendTelegramMessageJob::dispatch($message);
    }
}