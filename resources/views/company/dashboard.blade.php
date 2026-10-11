@extends('layouts.app')

@section('title', 'ภาพรวมสถานประกอบการ | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมสถานประกอบการ')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">พื้นที่สถานประกอบการ · URU Coop-IS</p><h1>{{ auth()->user()->company->company_name_th ?: auth()->user()->company->name }}</h1><p class="page-subtitle">ตรวจสอบนักศึกษาที่ได้รับการประสานงานและบันทึกผลตอบรับ</p></div>
</header>
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

<section class="stats-grid mb-4" aria-label="สรุปนักศึกษาที่สถานประกอบการ">
    <div class="stat-card"><span class="stat-icon"><i data-lucide="users-round"></i></span><span class="stat-copy"><span>Placement ทั้งหมด</span><strong>{{ $placements->count() }}</strong></span></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="hourglass"></i></span><span class="stat-copy"><span>รอตอบรับ</span><strong>{{ $placements->where('acceptance_status', 'PENDING')->count() }}</strong></span></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="circle-check"></i></span><span class="stat-copy"><span>ตอบรับแล้ว</span><strong>{{ $placements->where('acceptance_status', 'ACCEPTED')->count() }}</strong></span></div>
</section>

<section class="card overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="h6 mb-1">นักศึกษาที่ประสานงาน</h2><span class="small text-muted">แสดงเฉพาะรายการของสถานประกอบการนี้</span></div><span class="badge text-bg-light">{{ $placements->count() }} รายการ</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>นักศึกษา</th><th>ตำแหน่ง</th><th>ช่วงปฏิบัติงาน</th><th>สถานะตอบรับ</th><th>ดำเนินการ</th></tr></thead><tbody>
        @forelse($placements as $placement)
            <tr>
                <td><a href="{{ route('company.placements.show', $placement->placement_id) }}" class="text-decoration-none"><strong>{{ optional(optional($placement->student)->user)->name ?: optional($placement->student)->student_code }}</strong></a><div class="small text-muted">{{ optional($placement->student)->student_code }}</div></td>
                <td>{{ $placement->job_position }}</td>
                <td>{{ optional($placement->work_start_date)->format('d/m/Y') }} – {{ optional($placement->work_end_date)->format('d/m/Y') }}</td>
                <td><span class="badge {{ $placement->acceptance_status === 'ACCEPTED' ? 'text-bg-success' : ($placement->acceptance_status === 'REJECTED' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $placement->acceptance_status }}</span></td>
                <td>
                    @if($placement->acceptance_status === 'PENDING')
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('company.placements.acceptance', $placement->placement_id) }}">@csrf<input type="hidden" name="acceptance_status" value="ACCEPTED"><button class="btn btn-sm btn-success" type="submit">ตอบรับ</button></form>
                            <form method="POST" action="{{ route('company.placements.acceptance', $placement->placement_id) }}" onsubmit="return confirm('ยืนยันปฏิเสธการรับนักศึกษารายนี้หรือไม่?')">@csrf<input type="hidden" name="acceptance_status" value="REJECTED"><button class="btn btn-sm btn-outline-danger" type="submit">ปฏิเสธ</button></form>
                        </div>
                    @else<span class="small text-muted">บันทึกผลแล้ว</span>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="building-2"></i></span><strong>ยังไม่มีรายการนักศึกษาที่รอตอบรับ</strong><span>รายการที่เจ้าหน้าที่ประสานงานกับบริษัทจะแสดงที่นี่</span></div></td></tr>
        @endforelse
    </tbody></table></div>
</section>

<section class="card mt-4">
    <div class="card-header bg-white"><h2 class="h6 mb-1">ผู้นิเทศงานของสถานประกอบการ</h2><span class="small text-muted">ผู้ดูแลระบบที่ผ่านการอนุมัติและอยู่ในบัญชีบริษัทนี้</span></div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($mentors as $mentor)
                <div class="col-md-6"><div class="border rounded-3 p-3"><strong>{{ trim($mentor->first_name.' '.$mentor->last_name) }}</strong><div class="small text-muted">{{ $mentor->position_name ?: 'ผู้นิเทศงาน' }}</div><div class="small text-muted">{{ $mentor->email ?: '—' }} · {{ $mentor->phone ?: '—' }}</div></div></div>
            @empty
                <div class="col-12 text-muted small">ยังไม่มีผู้นิเทศงานที่เปิดใช้งาน</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
