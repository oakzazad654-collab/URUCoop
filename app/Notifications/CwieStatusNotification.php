<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CwieStatusNotification extends Notification
{
    use Queueable;

    private $title;
    private $message;
    private $relatedType;
    private $relatedId;

    public function __construct($title, $message, $relatedType, $relatedId)
    {
        $this->title = $title;
        $this->message = $message;
        $this->relatedType = $relatedType;
        $this->relatedId = $relatedId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'related_type' => $this->relatedType,
            'related_id' => $this->relatedId,
        ];
    }
}
