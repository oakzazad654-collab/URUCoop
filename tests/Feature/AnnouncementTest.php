<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_and_news_pages_show_published_announcements_only()
    {
        $published = Announcement::create([
            'title' => 'กำหนดการสหกิจศึกษา',
            'excerpt' => 'ประกาศกำหนดการประจำภาคเรียน',
            'body' => 'รายละเอียดกำหนดการสหกิจศึกษา',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        $draft = Announcement::create([
            'title' => 'ข่าวฉบับร่าง',
            'body' => 'ยังไม่เผยแพร่',
            'is_published' => false,
        ]);

        $this->get('/')->assertOk()->assertSee('URU Coop-IS')->assertSee('กำหนดการสหกิจศึกษา');
        $this->get('/news')->assertOk()
            ->assertSee('กำหนดการสหกิจศึกษา')
            ->assertDontSee('ข่าวฉบับร่าง');
        $this->get('/guide')->assertOk()->assertSee('ขั้นตอนการดำเนินงานสหกิจศึกษา');
        $this->get('/news/'.$published->id)->assertOk()->assertSee('รายละเอียดกำหนดการสหกิจศึกษา');
        $this->get('/news/'.$draft->id)->assertNotFound();
    }

    public function test_admin_can_publish_news_with_an_image()
    {
        $this->seed(UserSeeder::class);
        Storage::fake('public');
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();

        $this->actingAs($admin)->get('/admin/news/create')->assertOk();
        $this->actingAs($admin)->post('/admin/news', [
            'title' => 'ประกาศรับสมัครนักศึกษา',
            'excerpt' => 'เปิดรับสมัครแล้ว',
            'body' => 'รายละเอียดการรับสมัครและกำหนดการ',
            'is_published' => '1',
            'published_at' => now()->format('Y-m-d\TH:i'),
            'image' => UploadedFile::fake()->image('announcement.jpg', 800, 500),
        ])->assertRedirect('/admin/news');

        $announcement = Announcement::where('title', 'ประกาศรับสมัครนักศึกษา')->firstOrFail();
        $this->assertTrue($announcement->is_published);
        $this->assertEquals($admin->id, $announcement->author_id);
        Storage::disk('public')->assertExists($announcement->image_path);
        $this->get('/news/'.$announcement->id)->assertOk()->assertSee('ประกาศรับสมัครนักศึกษา');

        $this->actingAs($admin)->put('/admin/news/'.$announcement->id, [
            'title' => 'ประกาศรับสมัครฉบับแก้ไข',
            'excerpt' => 'ปรับปรุงกำหนดการ',
            'body' => 'รายละเอียดฉบับแก้ไข',
            'is_published' => '0',
        ])->assertRedirect('/admin/news');
        $this->get('/news')->assertDontSee('ประกาศรับสมัครฉบับแก้ไข');
        Storage::disk('public')->assertExists($announcement->image_path);

        $this->actingAs($admin)->delete('/admin/news/'.$announcement->id)->assertRedirect('/admin/news');
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
        Storage::disk('public')->assertMissing($announcement->image_path);
    }

    public function test_non_admin_cannot_manage_announcements()
    {
        $student = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'news-student@example.test',
            'password' => bcrypt('secret'),
            'role' => 'student',
        ]);

        $this->actingAs($student)->get('/admin/news')->assertForbidden();
    }
}