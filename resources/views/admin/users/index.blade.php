@extends('layouts.app')

@section('title', 'ผู้ใช้งาน | URU Coop-IS')
@section('breadcrumb', 'ผู้ใช้งาน')

@section('content')
@php($isOfficer = auth()->user()->role === 'officer')
@php($editRoute = $isOfficer ? 'officer.users.edit' : 'admin.users.edit')
@php($deleteRoute = $isOfficer ? 'officer.users.destroy' : 'admin.users.destroy')
<header class="page-heading">
    <div>
        <p class="page-eyebrow">การจัดการสิทธิ์และบัญชี</p>
        <h1>ผู้ใช้งาน</h1>
        <p class="page-subtitle">จัดการบัญชี บทบาท และสถานะการใช้งานในระบบ URU Coop-IS</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(!$isOfficer)<a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-primary"><i data-lucide="user-round-check" class="me-1" style="width:16px;height:16px"></i>คำขอสมัคร</a>@endif
        <a href="{{ $isOfficer ? route('officer.users.assign-advisor') : route('admin.users.assign-advisor') }}" class="btn btn-outline-primary"><i data-lucide="user-round-cog" class="me-1" style="width:16px;height:16px"></i>จับคู่อาจารย์</a>
        <a href="{{ $isOfficer ? route('officer.users.create') : route('admin.users.create') }}" class="btn btn-primary"><i data-lucide="user-round-plus" class="me-1" style="width:16px;height:16px"></i>เพิ่มผู้ใช้งาน</a>
    </div>
</header>

@if(session('success'))<div class="alert alert-success"><i data-lucide="circle-check" class="me-1" style="width:17px;height:17px"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<section class="card user-management-card">
    <div class="card-header bg-white border-0 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div><h2 class="h6 mb-1">บัญชีในระบบ</h2><span class="small text-muted">ทั้งหมด {{ $users->total() }} บัญชี</span></div>
            <form method="GET" class="d-flex flex-wrap gap-2 user-filters">
                <label class="visually-hidden" for="userSearch">ค้นหาผู้ใช้งาน</label>
                <input id="userSearch" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="ค้นหาชื่อหรืออีเมล">
                <label class="visually-hidden" for="roleFilter">กรองตามบทบาท</label>
                <select id="roleFilter" name="role" class="form-select"><option value="">ทุกบทบาท</option>@foreach(['student' => 'Student', 'advisor' => 'Advisor', 'supervisor' => 'Supervisor', 'mentor' => 'Mentor', 'officer' => 'Officer', 'executive' => 'Executive', 'company' => 'Company', 'admin' => 'Admin'] as $role => $label)@if(!$isOfficer || in_array($role, ['student', 'advisor', 'officer'], true))<option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $label }}</option>@endif @endforeach</select>
                <button class="btn btn-outline-primary" type="submit"><i data-lucide="filter" style="width:15px;height:15px"></i><span class="visually-hidden">กรอง</span></button>
                @if(request()->hasAny(['search', 'role']))<a class="btn btn-outline-secondary" href="{{ route($isOfficer ? 'officer.users.index' : 'admin.users.index') }}" aria-label="ล้างตัวกรอง" title="ล้างตัวกรอง"><i data-lucide="x" style="width:15px;height:15px"></i></a>@endif
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>ชื่อ</th><th>อีเมล</th><th>บทบาท</th><th>สถานประกอบการ</th><th>สถานะ</th><th>รหัสนักศึกษา</th><th>อาจารย์ที่ปรึกษา</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody>
            @forelse($users as $managedUser)
                <tr data-search-row>
                    <td><span class="avatar avatar-table">{{ mb_substr($managedUser->name, 0, 1) }}</span><strong class="ms-2">{{ $managedUser->name }}</strong></td>
                    <td>{{ $managedUser->email }}</td>
                    <td><span class="role-tag role-{{ $managedUser->role }}">{{ ucfirst($managedUser->role) }}</span></td>
                    <td>{{ $managedUser->company->company_name_th ?? ($managedUser->company->name ?? '—') }}</td>
                    <td>@if($managedUser->trashed())<span class="status-tag status-disabled"><i data-lucide="circle-pause"></i>ปิดใช้งาน</span>@elseif($managedUser->status === 'PENDING')<span class="status-tag status-pending"><i data-lucide="hourglass"></i>รออนุมัติ</span>@elseif($managedUser->status === 'REJECTED')<span class="status-tag status-disabled"><i data-lucide="circle-x"></i>ไม่อนุมัติ</span>@else<span class="status-tag status-active"><i data-lucide="circle-check"></i>ใช้งานอยู่</span>@endif</td>
                    <td>{{ $managedUser->student_number ?: '—' }}</td>
                    <td>{{ $managedUser->advisor->name ?? '—' }}</td>
                    <td class="text-end text-nowrap">
                        @if(!$managedUser->trashed())
                            <a class="icon-button table-action" href="{{ route($editRoute, $managedUser) }}" aria-label="แก้ไข {{ $managedUser->name }}" title="แก้ไข"><i data-lucide="pencil"></i></a>
                            @if(!$isOfficer)<form class="d-inline" method="POST" action="{{ route('admin.users.reset-password', $managedUser) }}" onsubmit="return confirm('รีเซ็ตรหัสผ่านเป็นค่าเริ่มต้นหรือไม่?')">@csrf<button class="icon-button table-action" aria-label="รีเซ็ตรหัสผ่าน {{ $managedUser->name }}" title="รีเซ็ตรหัสผ่าน"><i data-lucide="key-round"></i></button></form>@endif
                            <form class="d-inline" method="POST" action="{{ route($deleteRoute, $managedUser) }}" onsubmit="return confirm('ยืนยันการปิดใช้งานบัญชีนี้หรือไม่?')">@csrf @method('DELETE')<button class="icon-button table-action danger-action" aria-label="ลบ {{ $managedUser->name }}" title="ลบ"><i data-lucide="trash-2"></i></button></form>
                        @else<span class="small text-muted">ไม่สามารถจัดการได้</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="empty-state"><span class="empty-icon"><i data-lucide="users-round"></i></span><strong>ไม่พบผู้ใช้งาน</strong><span>ลองเปลี่ยนคำค้นหาหรือตัวกรอง</span></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="card-footer bg-white border-0 px-4 py-3"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><small class="text-muted">แสดง {{ $users->firstItem() }}-{{ $users->lastItem() }} จาก {{ $users->total() }} รายการ</small><div>{{ $users->links('pagination::bootstrap-4') }}</div></div></div>@endif
</section>
@endsection

@push('styles')
<style>
    .user-management-card { overflow: hidden; }
    .user-filters input { width: min(240px, 100%); }
    .user-filters select { width: 150px; }
    .status-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 999px; font-size: .78rem; font-weight: 600; white-space: nowrap; }
    .status-tag svg { width: 14px; height: 14px; }
    .status-active { color: #166534; background: #dcfce7; }
    .status-disabled { color: #64748b; background: #e2e8f0; }
    .status-pending { color: #92400e; background: #fef3c7; }
    .pagination { margin-bottom: 0; }
    @media (max-width: 700px) { .user-filters, .user-filters input, .user-filters select { width: 100%; } }
</style>
@endpush
