@extends('layouts.app')

@section('title', 'จัดการข่าวสาร | URU Coop-IS')
@section('breadcrumb', 'จัดการข่าวสาร')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">เนื้อหาสาธารณะ</p><h1>ข่าวสาร</h1><p class="page-subtitle">จัดการข่าวที่แสดงบนหน้าสาธารณะ</p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('news.index') }}" target="_blank" rel="noopener"><i data-lucide="external-link" class="me-1" style="width:15px;height:15px"></i>เปิดหน้าสาธารณะ</a><a class="btn btn-primary" href="{{ route('admin.news.create') }}"><i data-lucide="plus" class="me-1" style="width:16px;height:16px"></i>เพิ่มข่าว</a></div>
</header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="card overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h2 class="h6 mb-0">รายการข่าว</h2><span class="small text-muted">{{ $announcements->total() }} รายการ</span></div>
    <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>ข่าว</th><th>ผู้เขียน</th><th>วันที่เผยแพร่</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
        @forelse($announcements as $announcement)
            <tr><td><strong>{{ $announcement->title }}</strong>@if($announcement->excerpt)<div class="small text-muted">{{ \Illuminate\Support\Str::limit($announcement->excerpt, 100) }}</div>@endif</td><td>{{ optional($announcement->author)->name ?: '—' }}</td><td>{{ optional($announcement->published_at)->format('d/m/Y H:i') ?: 'ยังไม่กำหนด' }}</td><td>@if($announcement->is_published && $announcement->published_at && $announcement->published_at->lte(now()))<span class="badge text-bg-success">เผยแพร่</span>@elseif($announcement->is_published)<span class="badge text-bg-warning">ตั้งเวลา</span>@else<span class="badge text-bg-secondary">ฉบับร่าง</span>@endif</td><td class="text-end text-nowrap"><a class="icon-button table-action" href="{{ route('admin.news.edit', $announcement) }}" title="แก้ไข" aria-label="แก้ไขข่าว {{ $announcement->title }}"><i data-lucide="pencil"></i></a>@if($announcement->is_published && $announcement->published_at && $announcement->published_at->lte(now()))<a class="icon-button table-action" href="{{ route('news.show', $announcement) }}" target="_blank" rel="noopener" title="ดูข่าว" aria-label="ดูข่าว {{ $announcement->title }}"><i data-lucide="external-link"></i></a>@endif<form class="d-inline" method="POST" action="{{ route('admin.news.destroy', $announcement) }}" onsubmit="return confirm('ยืนยันลบข่าวนี้หรือไม่?')">@csrf @method('DELETE')<button class="icon-button table-action danger-action" title="ลบ" aria-label="ลบข่าว {{ $announcement->title }}"><i data-lucide="trash-2"></i></button></form></td></tr>
        @empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="newspaper"></i></span><strong>ยังไม่มีข่าว</strong></div></td></tr>@endforelse
    </tbody></table></div>
    @if($announcements->hasPages())<div class="card-footer bg-white">{{ $announcements->links() }}</div>@endif
</section>
@endsection