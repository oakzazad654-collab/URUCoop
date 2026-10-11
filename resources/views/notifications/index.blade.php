@extends('layouts.app')

@section('title', 'การแจ้งเตือน | URU Coop-IS')
@section('breadcrumb', 'การแจ้งเตือน')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">กล่องข้อความ</p><h1>การแจ้งเตือน</h1><p class="page-subtitle">ประวัติการเปลี่ยนแปลงเอกสารและการพิจารณา CWIE</p></div>@if(auth()->user()->unreadNotifications()->exists())<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-outline-primary"><i data-lucide="check-check" class="me-1" style="width:15px;height:15px"></i>ทำเครื่องหมายว่าอ่านแล้วทั้งหมด</button></form>@endif</header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="card"><div class="notification-list">
    @forelse($notifications as $notification)
        @php($data = $notification->data)
        <article class="notification-row {{ $notification->read_at ? '' : 'unread' }}"><span class="notification-symbol"><i data-lucide="{{ ($data['notification_type'] ?? '') === 'document_approved' ? 'circle-check' : ((($data['notification_type'] ?? '') === 'document_revision_requested' || ($data['notification_type'] ?? '') === 'document_rejected') ? 'rotate-ccw' : 'file-clock') }}"></i></span><div class="notification-content"><div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ $data['title'] ?? 'แจ้งเตือนเอกสาร' }}</strong><time>{{ $notification->created_at->format('d/m/Y H:i') }}</time></div><p>{{ $data['message'] ?? '' }}</p><span class="small text-muted">{{ strtoupper(str_replace('-', ' ', $data['document_type'] ?? 'CWIE')) }} · {{ $data['document_title'] ?? '' }}</span>@if(!empty($data['comment']))<blockquote>{{ $data['comment'] }}</blockquote>@endif<div class="d-flex gap-2 mt-2">@if(!empty($data['url']))<a class="btn btn-sm btn-outline-primary" href="{{ $data['url'] }}">เปิดเอกสาร</a>@endif @if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="btn btn-sm btn-link">ทำเครื่องหมายว่าอ่านแล้ว</button></form>@endif</div></div></article>
    @empty<div class="empty-state"><span class="empty-icon"><i data-lucide="bell-off"></i></span><strong>ยังไม่มีการแจ้งเตือน</strong><span>เมื่อมีความเคลื่อนไหวของเอกสาร รายการจะแสดงที่นี่</span></div>@endforelse
</div><div class="card-footer bg-white">{{ $notifications->links() }}</div></section>
@endsection
