<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteAppearanceController extends Controller
{
    private const HOMEPAGE_BACKGROUND_KEY = 'homepage_background_image';
    private const HOMEPAGE_YOUTUBE_KEY = 'homepage_youtube_video_id';

    public function edit()
    {
        $backgroundSetting = WebsiteSetting::where('key', self::HOMEPAGE_BACKGROUND_KEY)->first();
        $backgroundImage = $backgroundSetting && is_string($backgroundSetting->value) && $backgroundSetting->value !== ''
            ? route('homepage.background', ['v' => hash('sha256', $backgroundSetting->value)])
            : null;
        $youtubeVideoId = WebsiteSetting::where('key', self::HOMEPAGE_YOUTUBE_KEY)->value('value');

        return view('admin.appearance.edit', compact('backgroundImage', 'youtubeVideoId'));
    }

    public function updateBackground(Request $request)
    {
        $validated = $request->validate([
            'background_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $previousPath = WebsiteSetting::where('key', self::HOMEPAGE_BACKGROUND_KEY)->value('value');
        $newPath = $validated['background_image']->store('site', 'public');
        abort_unless($newPath, 500, 'ไม่สามารถบันทึกภาพพื้นหลังได้');

        WebsiteSetting::updateOrCreate(
            ['key' => self::HOMEPAGE_BACKGROUND_KEY],
            ['value' => $newPath]
        );

        if ($previousPath) {
            Storage::disk('public')->delete($previousPath);
        }

        return redirect()->route('admin.appearance.edit')->with('success', 'เปลี่ยนภาพพื้นหลังหน้าแรกเรียบร้อยแล้ว');
    }

    public function removeBackground()
    {
        $setting = WebsiteSetting::where('key', self::HOMEPAGE_BACKGROUND_KEY)->first();
        if ($setting) {
            Storage::disk('public')->delete($setting->value);
            $setting->delete();
        }

        return redirect()->route('admin.appearance.edit')->with('success', 'นำภาพพื้นหลังออกแล้ว');
    }

    public function updateYoutubeVideo(Request $request)
    {
        $validated = $request->validate([
            'youtube_url' => [
                'nullable',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '' && $this->youtubeVideoId($value) === null) {
                        $fail('กรุณาระบุ URL วิดีโอ YouTube ที่ถูกต้อง');
                    }
                },
            ],
        ]);

        $videoId = isset($validated['youtube_url']) && $validated['youtube_url'] !== ''
            ? $this->youtubeVideoId($validated['youtube_url'])
            : null;

        if ($videoId === null) {
            WebsiteSetting::where('key', self::HOMEPAGE_YOUTUBE_KEY)->delete();
        } else {
            WebsiteSetting::updateOrCreate(
                ['key' => self::HOMEPAGE_YOUTUBE_KEY],
                ['value' => $videoId]
            );
        }

        return redirect()->route('admin.appearance.edit')->with(
            'success',
            $videoId === null ? 'นำวิดีโอ YouTube ออกจากหน้าแรกแล้ว' : 'บันทึกวิดีโอ YouTube หน้าแรกเรียบร้อยแล้ว'
        );
    }

    public function homepageBackground()
    {
        $path = WebsiteSetting::where('key', self::HOMEPAGE_BACKGROUND_KEY)->value('value');
        abort_unless(
            is_string($path) && preg_match('/\Asite\/[A-Za-z0-9._-]+\.(?:jpg|jpeg|png|webp)\z/i', $path),
            404
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);
        $mimeType = $disk->mimeType($path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function youtubeVideoId($url)
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? null;
            } else {
                $segments = explode('/', $path);
                if (count($segments) === 2 && in_array($segments[0], ['embed', 'shorts', 'live'], true)) {
                    $videoId = $segments[1];
                }
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $segments = explode('/', $path);
            $videoId = count($segments) === 1 ? $segments[0] : null;
        }

        return is_string($videoId) && preg_match('/\A[A-Za-z0-9_-]{11}\z/', $videoId)
            ? $videoId
            : null;
    }
}