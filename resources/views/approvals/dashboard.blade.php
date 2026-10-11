@extends('layouts.app')

@section('title', auth()->user()->role === 'student' ? 'สถานะเอกสาร | URU Coop-IS' : 'รายการอนุมัติ | URU Coop-IS')
@section('breadcrumb', auth()->user()->role === 'student' ? 'สถานะเอกสาร' : 'รายการอนุมัติ')

@section('content')
@php($role = auth()->user()->role)
<header class="page-heading">
    <div><p class="page-eyebrow">{{ ucfirst(['student' => 'นักศึกษา', 'advisor' => 'อาจารย์ที่ปรึกษา', 'officer' => 'เจ้าหน้าที่ศูนย์', 'admin' => 'ผู้ดูแลระบบ'][$role]) }}</p><h1>{{ $role === 'student' ? 'สถานะเอกสารของฉัน' : ($role === 'advisor' ? 'เอกสารในความดูแล' : 'รายการรอพิจารณา') }}</h1><p class="page-subtitle">ติดตามเอกสารและดำเนินการตามขั้นตอนสหกิจศึกษา</p></div>
    @if($role === 'student')<a href="{{ route('student.cwie.index') }}" class="btn btn-primary"><i data-lucide="plus" class="me-1" style="width:16px;height:16px"></i>กรอกแบบฟอร์ม สก.</a>@endif
</header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="stats-grid" aria-label="สรุปสถานะเอกสาร">
    <article class="stat-card"><span class="stat-icon"><i data-lucide="files"></i></span><div class="stat-copy"><span>เอกสารทั้งหมด</span><strong>{{ $stats['total'] }}</strong></div></article>
    <article class="stat-card"><span class="stat-icon" style="color:#a16207;background:#fef3c7"><i data-lucide="clock-3"></i></span><div class="stat-copy"><span>{{ $role === 'student' ? 'รอพิจารณา' : 'รอดำเนินการ' }}</span><strong>{{ $stats['pending'] }}</strong></div></article>
    <article class="stat-card"><span class="stat-icon" style="color:#15803d;background:#dcfce7"><i data-lucide="circle-check"></i></span><div class="stat-copy"><span>อนุมัติแล้ว</span><strong>{{ $stats['approved'] }}</strong></div></article>
    <article class="stat-card"><span class="stat-icon" style="color:#b91c1c;background:#fee2e2"><i data-lucide="rotate-ccw"></i></span><div class="stat-copy"><span>ส่งกลับ/ไม่อนุมัติ</span><strong>{{ $stats['revision'] }}</strong></div></article>
    @if($stats['companies'] !== null)<article class="stat-card"><span class="stat-icon" style="color:#475569;background:#e2e8f0"><i data-lucide="building-2"></i></span><div class="stat-copy"><span>สถานประกอบการ</span><strong>{{ $stats['companies'] }}</strong></div></article>@endif
</section>
<section class="card">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2"><div><h2 class="h6 mb-0">{{ $role === 'student' ? 'เอกสารของฉัน' : 'เอกสารในคิว' }}</h2><span class="small text-muted">{{ $documents->count() }} รายการ</span></div><span class="small text-muted">อัปเดตล่าสุด</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>เอกสาร</th><th>เจ้าของเอกสาร</th><th>รหัสนักศึกษา</th><th>สถานะ</th><th>วันที่อัปเดต</th><th class="text-end">รายการ</th></tr></thead>
        <tbody>
        @forelse($documents as $document)
            @php($reviewRoute = $role.'.approvals.review')
            <tr data-search-row>
                <td><span class="fw-semibold">{{ strtoupper(str_replace('-', ' ', $document->type)) }}</span><div class="small text-muted">{{ $document->title }}</div></td>
                <td>{{ $document->owner->name ?? ($document->data['student_name'] ?? '—') }}</td>
                <td>{{ $document->student_id ?: '—' }}</td>
                <td>@include('cwie._status_badge', ['status' => $document->status])</td>
                <td class="text-nowrap">{{ $document->updated_at->format('d/m/Y H:i') }}</td>
                <td class="text-end"><a href="{{ route($reviewRoute, [$document->type, $document]) }}" class="btn btn-sm btn-outline-primary" aria-label="เปิดเอกสาร {{ $document->type }}"><i data-lucide="arrow-up-right" style="width:15px;height:15px"></i><span class="visually-hidden">เปิดเอกสาร</span></a></td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="empty-state"><span class="empty-icon"><i data-lucide="inbox"></i></span><strong>ยังไม่มีเอกสารในรายการ</strong><span>{{ $role === 'student' ? 'แบบฟอร์ม สก. ที่บันทึกจะแสดงในหน้ารายการแบบฟอร์ม' : 'รายการใหม่จะแสดงที่นี่เมื่อเข้าสู่ขั้นตอนพิจารณา' }}</span>@if($role === 'student')<a href="{{ route('student.cwie.index') }}" class="btn btn-sm btn-primary mt-2">เลือกแบบฟอร์ม</a>@endif</div></td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
