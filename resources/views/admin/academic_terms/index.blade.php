@extends('layouts.app')

@section('title', 'ภาคการศึกษา | URU Coop-IS')
@section('breadcrumb', 'ภาคการศึกษา')

@section('content')
<header class="page-heading">
    <div>
        <p class="page-eyebrow">ข้อมูลพื้นฐาน · ผู้ดูแลระบบ</p>
        <h1>ภาคการศึกษา</h1>
        <p class="page-subtitle">กำหนดปีการศึกษา ช่วงรับสมัคร และสถานะที่ใช้ในใบสมัครสหกิจศึกษา</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.academic-terms.create') }}"><i data-lucide="plus" class="me-1" style="width:16px;height:16px"></i>เพิ่มภาคการศึกษา</a>
</header>

@if(session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

<section class="card overflow-hidden">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">รายการภาคการศึกษา</h2>
        <span class="small text-muted">{{ $terms->total() }} รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>ภาค/ปีการศึกษา</th><th>ช่วงการศึกษา</th><th>ช่วงรับสมัคร</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr>
            </thead>
            <tbody>
                @forelse($terms as $term)
                    <tr>
                        <td><strong>{{ $term->semester_no }}/{{ $term->academic_year }}</strong></td>
                        <td>{{ $term->start_date ? $term->start_date->format('d/m/Y') : '—' }} – {{ $term->end_date ? $term->end_date->format('d/m/Y') : '—' }}</td>
                        <td>{{ $term->registration_start_date ? $term->registration_start_date->format('d/m/Y') : '—' }} – {{ $term->registration_end_date ? $term->registration_end_date->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($term->status === 'OPEN')
                                <span class="badge text-bg-success">เปิดรับสมัคร</span>
                            @elseif($term->status === 'CLOSED')
                                <span class="badge text-bg-secondary">ปิดรับสมัคร</span>
                            @else
                                <span class="badge text-bg-warning">วางแผน</span>
                            @endif
                        </td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.academic-terms.edit', $term) }}">แก้ไข</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="calendar-days"></i></span><strong>ยังไม่มีภาคการศึกษา</strong></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($terms->hasPages())
        <div class="card-footer bg-white">{{ $terms->links() }}</div>
    @endif
</section>
@endsection
