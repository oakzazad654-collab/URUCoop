@extends('layouts.app')

@section('title', 'คำขอสมัครบัญชี | URU Coop-IS')
@section('breadcrumb', 'คำขอสมัครบัญชี')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">ตรวจสอบบัญชีใหม่</p><h1>คำขอสมัครที่รออนุมัติ</h1><p class="page-subtitle">ตรวจสอบข้อมูลบทบาทและสถานประกอบการก่อนเปิดใช้งาน</p></div><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">ผู้ใช้งานทั้งหมด</a></header>
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
<section class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ชื่อ</th><th>อีเมล/โทรศัพท์</th><th>บทบาท</th><th>สังกัด/สถานประกอบการ</th><th>สมัครเมื่อ</th><th>พิจารณา</th></tr></thead><tbody>
    @forelse($users as $user)
        <tr><td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}<div class="small text-muted">{{ $user->phone ?: 'ไม่ระบุโทรศัพท์' }}</div></td><td>{{ ['advisor' => 'อาจารย์ที่ปรึกษา', 'supervisor' => 'อาจารย์นิเทศ', 'mentor' => 'ผู้นิเทศงาน', 'company' => 'สถานประกอบการ'][$user->role] ?? $user->role }}</td><td>{{ $user->company->company_name_th ?? ($user->company->name ?? ($user->faculty->name ?? '—')) }}@if($user->department)<div class="small text-muted">{{ $user->department->name }}</div>@endif</td><td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td><td class="text-nowrap"><form class="d-inline" method="POST" action="{{ route('admin.registrations.approve', $user) }}">@csrf<button class="btn btn-sm btn-success">อนุมัติ</button></form><form class="d-inline ms-1" method="POST" action="{{ route('admin.registrations.reject', $user) }}" onsubmit="return confirm('ยืนยันปฏิเสธคำขอนี้หรือไม่?')">@csrf<button class="btn btn-sm btn-outline-danger">ปฏิเสธ</button></form></td></tr>
    @empty
        <tr><td colspan="6"><div class="empty-state"><span class="empty-icon"><i data-lucide="user-check"></i></span><strong>ไม่มีคำขอสมัครที่รออนุมัติ</strong><span>บัญชีบุคลากรและสถานประกอบการใหม่จะแสดงในรายการนี้</span></div></td></tr>
    @endforelse
</tbody></table></div>@if($users->hasPages())<div class="card-footer bg-white">{{ $users->links('pagination::bootstrap-4') }}</div>@endif</section>
@endsection
