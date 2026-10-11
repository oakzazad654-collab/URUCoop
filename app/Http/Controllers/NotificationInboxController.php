<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationInboxController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $notification)
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();
        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้วทั้งหมด');
    }
}