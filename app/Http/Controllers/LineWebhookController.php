<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request, LineMessagingService $line)
    {
        $secret = config('services.line.channel_secret');
        $signature = $request->header('X-Line-Signature');
        if (!$secret) return response()->json(['message' => 'LINE webhook is not configured.'], 503);
        if (!$signature) return response()->json(['message' => 'Invalid signature.'], 401);

        $expected = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
        if (!hash_equals($expected, $signature)) return response()->json(['message' => 'Invalid signature.'], 401);

        foreach ((array) $request->input('events', []) as $event) {
            if (data_get($event, 'type') !== 'message' || data_get($event, 'message.type') !== 'text') continue;
            $lineUserId = data_get($event, 'source.userId');
            $code = strtoupper(trim((string) data_get($event, 'message.text')));
            if (!$lineUserId || !preg_match('/^[A-F0-9]{24}$/', $code)) continue;

            $linked = $this->consumeBindingCode($lineUserId, $code);
            if ($linked) {
                try {
                    $line->push($lineUserId, [['type' => 'text', 'text' => 'เชื่อมต่อการแจ้งเตือน URU Coop-IS สำเร็จแล้ว']]);
                } catch (\Throwable $exception) {
                    Log::warning('Could not send LINE binding confirmation.', ['error' => $exception->getMessage()]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    private function consumeBindingCode($lineUserId, $code)
    {
        $hash = hash('sha256', $code);
        return DB::transaction(function () use ($lineUserId, $hash) {
            $user = User::where('line_link_code_hash', $hash)
                ->where('line_link_expires_at', '>', now())
                ->lockForUpdate()
                ->first();
            if (!$user) return false;

            $alreadyLinked = User::where('line_user_id', $lineUserId)->where('id', '!=', $user->id)->exists();
            if ($alreadyLinked) return false;

            $user->line_user_id = $lineUserId;
            $user->line_link_code_hash = null;
            $user->line_link_expires_at = null;
            $user->save();
            return true;
        });
    }
}