<?php

namespace App\Notifications\Channels;

use App\Services\LineMessagingService;
use Illuminate\Notifications\Notification;

class LineChannel
{
    private $line;

    public function __construct(LineMessagingService $line)
    {
        $this->line = $line;
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->line_user_id || !method_exists($notification, 'toLine')) return;
        $this->line->push($notifiable->line_user_id, [$notification->toLine($notifiable)]);
    }
}