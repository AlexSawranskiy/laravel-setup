<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Jobs\SendTaskCreatedNotification;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskNotificationListener
{
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;
    
        $message = "<b>New Task Created!</b>\n\n" .
                   "<b>ID:</b> {$task->id}\n" .
                   "<b>Title:</b> {$task->title}\n" .
                   "<b>Priority:</b> {$task->priority}\n" .
                   "<b>Status:</b> {$task->status}";

        SendTaskCreatedNotification::dispatch($task);
        SendTelegramMessageJob::dispatch($message);
    }
}