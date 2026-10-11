<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function home()
    {
        $latestAnnouncements = $this->publishedAnnouncements()->latest('published_at')->limit(3)->get();
        $backgroundSetting = WebsiteSetting::where('key', 'homepage_background_image')->first();
        $backgroundImage = $backgroundSetting && is_string($backgroundSetting->value) && $backgroundSetting->value !== ''
            ? route('homepage.background', ['v' => hash('sha256', $backgroundSetting->value)])
            : null;
        $youtubeVideoId = WebsiteSetting::where('key', 'homepage_youtube_video_id')->value('value');
        if (!is_string($youtubeVideoId) || !preg_match('/\A[A-Za-z0-9_-]{11}\z/', $youtubeVideoId)) {
            $youtubeVideoId = null;
        }

        return view('landing.home', compact('latestAnnouncements', 'backgroundImage', 'youtubeVideoId'));
    }

    public function index()
    {
        $announcements = $this->publishedAnnouncements()->latest('published_at')->paginate(9);

        return view('landing.news.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        abort_unless($announcement->is_published && $announcement->published_at && $announcement->published_at->lte(now()), 404);

        return view('landing.news.show', compact('announcement'));
    }

    public function image(Announcement $announcement)
    {
        abort_unless($announcement->is_published && $announcement->published_at && $announcement->published_at->lte(now()), 404);
        abort_unless($announcement->image_path && Storage::disk('public')->exists($announcement->image_path), 404);

        $mimeType = Storage::disk('public')->mimeType($announcement->image_path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return response()->file(Storage::disk('public')->path($announcement->image_path), [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function publishedAnnouncements()
    {
        return Announcement::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}