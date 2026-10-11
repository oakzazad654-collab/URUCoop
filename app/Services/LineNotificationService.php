<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LineNotificationService
{
    public function push($lineUserId, array $messages)
    {
        $token = config('services.line.channel_token') ?: config('services.line.channel_access_token');
        if (!$token) {
            throw new RuntimeException('LINE Messaging API is not configured.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(8)
            ->post(config('services.line.push_endpoint', 'https://api.line.me/v2/bot/message/push'), [
                'to' => $lineUserId,
                'messages' => $messages,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('LINE push failed with HTTP '.$response->status().': '.$response->body());
        }

        return $response;
    }

    public function sendTestNotification($lineUserId, $userName)
    {
        return $this->push($lineUserId, [[
            'type' => 'text',
            'text' => "ทดสอบระบบแจ้งเตือน URU Coop-IS\nสถานะ: ระบบแจ้งเตือนพร้อมใช้งาน\nผู้รับ: ".$userName,
        ]]);
    }
}