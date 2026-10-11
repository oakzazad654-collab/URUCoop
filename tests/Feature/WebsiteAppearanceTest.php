<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_replace_and_remove_the_public_homepage_background()
    {
        $this->seed(UserSeeder::class);
        Storage::fake('public');
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();

        $this->actingAs($admin)->get('/admin/appearance')->assertOk()->assertSee('ภาพพื้นหลังหน้าแรก');

        $this->actingAs($admin)->put('/admin/appearance/background', [
            'background_image' => UploadedFile::fake()->image('hero-first.jpg', 1600, 600),
        ])->assertRedirect('/admin/appearance');

        $firstPath = \Illuminate\Support\Facades\DB::table('website_settings')
            ->where('key', 'homepage_background_image')->value('value');
        $this->assertNotEmpty($firstPath);
        Storage::disk('public')->assertExists($firstPath);
        auth()->logout();
        $this->get('/')->assertOk()->assertSee('homepage-background?v=', false);
        $this->get('/homepage-background')->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $this->actingAs($admin)->put('/admin/appearance/background', [
            'background_image' => UploadedFile::fake()->image('hero-second.png', 1600, 600),
        ])->assertRedirect('/admin/appearance');

        $secondPath = \Illuminate\Support\Facades\DB::table('website_settings')
            ->where('key', 'homepage_background_image')->value('value');
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->actingAs($admin)->delete('/admin/appearance/background')->assertRedirect('/admin/appearance');
        $this->assertDatabaseMissing('website_settings', ['key' => 'homepage_background_image']);
        Storage::disk('public')->assertMissing($secondPath);
        auth()->logout();
        $this->get('/')->assertOk()->assertDontSee('homepage-background?v=', false);
        $this->get('/homepage-background')->assertNotFound();
    }

    public function test_admin_can_add_and_remove_a_youtube_video_on_the_public_homepage()
    {
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();
        $videoId = 'dQw4w9WgXcQ';

        $this->actingAs($admin)->put('/admin/appearance/youtube', [
            'youtube_url' => 'https://www.youtube.com/watch?v='.$videoId,
        ])->assertRedirect('/admin/appearance');

        $this->assertDatabaseHas('website_settings', [
            'key' => 'homepage_youtube_video_id',
            'value' => $videoId,
        ]);
        $this->actingAs($admin)->get('/admin/appearance')
            ->assertOk()
            ->assertSee($videoId)
            ->assertSee('youtube-nocookie.com/embed/'.$videoId, false);
        $this->get('/')->assertOk()
            ->assertSee('youtube-nocookie.com/embed/'.$videoId, false)
            ->assertSee('public-home-video', false);

        $this->actingAs($admin)->put('/admin/appearance/youtube', [
            'youtube_url' => '',
        ])->assertRedirect('/admin/appearance');

        $this->assertDatabaseMissing('website_settings', ['key' => 'homepage_youtube_video_id']);
        $this->get('/')->assertOk()->assertDontSee('youtube-nocookie.com/embed/', false);
    }

    public function test_admin_can_save_supported_youtube_short_links_but_not_external_embed_urls()
    {
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();

        $this->actingAs($admin)->put('/admin/appearance/youtube', [
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertRedirect('/admin/appearance');
        $this->assertDatabaseHas('website_settings', [
            'key' => 'homepage_youtube_video_id',
            'value' => 'dQw4w9WgXcQ',
        ]);

        $this->actingAs($admin)->from('/admin/appearance')->put('/admin/appearance/youtube', [
            'youtube_url' => 'https://example.com/embed/dQw4w9WgXcQ',
        ])->assertRedirect('/admin/appearance')
            ->assertSessionHasErrors('youtube_url');
    }

    public function test_non_admin_cannot_change_homepage_background()
    {
        $student = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'appearance-student@example.test',
            'password' => bcrypt('secret'),
            'role' => 'student',
        ]);

        $this->actingAs($student)->get('/admin/appearance')->assertForbidden();
        $this->actingAs($student)->put('/admin/appearance/background', [
            'background_image' => UploadedFile::fake()->image('hero.jpg'),
        ])->assertForbidden();
        $this->actingAs($student)->put('/admin/appearance/youtube', [
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertForbidden();
    }
}