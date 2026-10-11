<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LineMessagingService
{
    private $notifications;

    public function __construct(LineNotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function push($lineUserId, array $messages)
    {
        return $this->notifications->push($lineUserId, $messages);
    }
}