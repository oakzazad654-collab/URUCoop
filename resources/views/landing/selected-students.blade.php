@extends('layouts.public')

@section('title', 'นักศึกษาผ่านการคัดเลือก | URU Coop-IS')

@section('content')
<header class="public-info-heading"><p class="public-home-eyebrow">ผลการคัดเลือก</p><h1>นักศึกษาที่ผ่านการคัดเลือกเข้าร่วมสหกิจศึกษา</h1><p>รายชื่อแสดงเฉพาะนักศึกษาที่ได้รับอนุมัติและสถานประกอบการตอบรับแล้ว</p></header>
<section class="card overflow-hidden">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>นักศึกษา</th><th>หลักสูตร/คณะ</th><th>สถานประกอบการ</th><th>ภาคการศึกษา</th><th>ช่วงปฏิบัติงาน</th></tr></thead><tbody>
        @forelse($placements as $placement)
            <tr><td><strong>{{ optional(optional($placement->student)->user)->name ?: 'นักศึกษา' }}</strong></td><td>{{ optional(optional($placement->student)->program)->program_name ?: '—' }}<div class="small text-muted">{{ optional(optional(optional($placement->student)->program)->faculty)->name }}</div></td><td>{{ optional($placement->company)->company_name_th ?: optional($placement->company)->name }}</td><td>{{ optional($placement->application->term)->semester_no ? 'ภาค '.$placement->application->term->semester_no.'/'.$placement->application->term->academic_year : '—' }}</td><td>{{ optional($placement->work_start_date)->format('d/m/Y') }} – {{ optional($placement->work_end_date)->format('d/m/Y') }}</td></tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="users-round"></i></span><strong>ยังไม่มีรายชื่อที่ประกาศ</strong><span>รายชื่อจะแสดงเมื่อผ่านการอนุมัติและสถานประกอบการตอบรับแล้ว</span></div></td></tr>
        @endforelse
    </tbody></table></div>
    @if($placements->hasPages())<div class="card-footer bg-white">{{ $placements->links('pagination::bootstrap-4') }}</div>@endif
</section>
@endsection

@push('styles')
<style>.public-info-heading{max-width:900px;margin:0 auto 24px}.public-info-heading h1{margin:0;color:#183528;font-size:27px;font-weight:600}.public-info-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px}.table thead th{font-size:11px;color:#617568;background:#f7faf7}.table tbody td{font-size:12px;color:#354d3e}@media(max-width:600px){.public-info-heading h1{font-size:23px}}</style>
@endpush
