<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LineNotificationService;
use Illuminate\Console\Command;
use Throwable;

class TestLineNotificationCommand extends Command
{
    protected $signature = 'test:line-notify {email}';

    protected $description = 'Send a real LINE test notification to a linked URU Coop-IS user';

    public function handle(LineNotificationService $line)
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('ไม่พบผู้ใช้ที่ใช้อีเมล '.$email);
            return 1;
        }

        if (!$user->line_user_id) {
            $this->warn('ผู้ใช้นี้ยังไม่ได้เชื่อมต่อ LINE');
            $this->line('กรุณาผูกบัญชีผ่านหน้าการตั้งค่าบัญชี หรือบันทึก LINE User ID ที่ขึ้นต้นด้วย U ในคอลัมน์ users.line_user_id');
            return 1;
        }

        try {
            $line->sendTestNotification($user->line_user_id, $user->name);
        } catch (Throwable $exception) {
            $this->error('ส่งข้อความทดสอบไม่สำเร็จ: '.$exception->getMessage());
            return 1;
        }

        $this->info('ส่งข้อความทดสอบ LINE ให้ '.$user->email.' สำเร็จ');
        return 0;
    }
}