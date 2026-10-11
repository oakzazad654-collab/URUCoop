<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->latest()->paginate(15);

        return view('admin.news.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.news.form', ['announcement' => new Announcement()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAnnouncement($request);
        $validated['author_id'] = $request->user()->id;
        $validated['is_published'] = (bool) $validated['is_published'];
        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        }

        Announcement::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'เผยแพร่/บันทึกข่าวเรียบร้อยแล้ว');
    }

    public function edit(Announcement $news)
    {
        return view('admin.news.form', ['announcement' => $news]);
    }

    public function update(Request $request, Announcement $news)
    {
        $validated = $this->validateAnnouncement($request);
        $validated['is_published'] = (bool) $validated['is_published'];
        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = $news->published_at ?: now();
        }
        if ($request->hasFile('image')) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'อัปเดตข่าวเรียบร้อยแล้ว');
    }

    public function destroy(Announcement $news)
    {
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'ลบข่าวเรียบร้อยแล้ว');
    }

    private function validateAnnouncement(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'required|string|max:50000',
            'is_published' => 'required|boolean',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
    }
}