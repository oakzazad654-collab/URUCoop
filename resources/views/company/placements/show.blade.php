@extends('layouts.app')

@section('title', 'รายละเอียดนักศึกษา | URU Coop-IS')
@section('breadcrumb', 'รายละเอียดนักศึกษา')

@section('content')
@php
    $student = $placement->student;
    $application = $placement->application;
    $displayName = optional($student->user)->name ?: trim(($student->title_name ? $student->title_name.' ' : '').($student->first_name_en ?: '').' '.($student->last_name_en ?: ''));
    $statusClasses = ['ACCEPTED' => 'text-bg-success', 'REJECTED' => 'text-bg-danger', 'PENDING' => 'text-bg-warning'];
@endphp

<header class="page-heading d-flex justify-content-between align-items-start gap-3 flex-wrap">
    <div>
        <p class="page-eyebrow">พื้นที่สถานประกอบการ · URU Coop-IS</p>
        <h1>รายละเอียดนักศึกษา</h1>
        <p class="page-subtitle">ข้อมูลการปฏิบัติงานของนักศึกษาที่ได้รับการประสานงานกับสถานประกอบการ</p>
    </div>
    <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary">กลับหน้าภาพรวม</a>
</header>

<section class="card mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <p class="text-muted small mb-1">นักศึกษา</p>
                <h2 class="h4 mb-1">{{ $displayName ?: 'ไม่ระบุชื่อ' }}</h2>
                <div class="text-muted">รหัสนักศึกษา {{ $student->student_code ?: '—' }}</div>
            </div>
            <span class="badge {{ $statusClasses[$placement->acceptance_status] ?? 'text-bg-secondary' }} fs-6">{{ $placement->acceptance_status ?: 'ไม่ระบุสถานะ' }}</span>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-6"><span class="text-muted small d-block">หลักสูตร</span><strong>{{ optional($student->program)->program_name ?: '—' }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">คณะ</span><strong>{{ optional(optional($student->program)->faculty)->faculty_name ?: optional(optional($student->program)->faculty)->name ?: '—' }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">ภาคการศึกษา</span><strong>{{ $application && $application->term ? $application->term->semester_no.'/'.$application->term->academic_year : '—' }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">ตำแหน่งปฏิบัติงาน</span><strong>{{ $placement->job_position ?: '—' }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">วันที่เริ่มปฏิบัติงาน</span><strong>{{ optional($placement->work_start_date)->format('d/m/Y') ?: '—' }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">วันที่สิ้นสุด</span><strong>{{ optional($placement->work_end_date)->format('d/m/Y') ?: '—' }}</strong></div>
        </div>
    </div>
</section>

<section class="card mb-4">
    <div class="card-header bg-white"><h2 class="h6 mb-0">รายละเอียดงาน</h2></div>
    <div class="card-body"><p class="mb-0">{!! nl2br(e($placement->job_description ?: 'ยังไม่มีรายละเอียดงาน')) !!}</p></div>
</section>

<div class="row g-4">
    <div class="col-lg-6">
        <section class="card h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">แผนปฏิบัติงาน</h2></div>
            <div class="card-body">
                @if($placement->workPlan)
                    <h3 class="h6">{{ $placement->workPlan->plan_title }}</h3>
                    @if($placement->workPlan->objective)<p class="text-muted">{{ $placement->workPlan->objective }}</p>@endif
                    @if($placement->workPlan->items->isNotEmpty())
                        <ol class="mb-0">@foreach($placement->workPlan->items->sortBy('sequence_no') as $item)<li class="mb-2"><strong>{{ $item->activity_name }}</strong>@if($item->activity_description)<div class="small text-muted">{{ $item->activity_description }}</div>@endif</li>@endforeach</ol>
                    @else<p class="text-muted mb-0">ยังไม่มีรายการกิจกรรม</p>@endif
                @else
                    <p class="text-muted mb-0">ยังไม่มีแผนปฏิบัติงาน</p>
                @endif
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="card h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">หัวข้อรายงาน</h2></div>
            <div class="card-body">
                @if($placement->reportOutline)
                    <h3 class="h6">{{ $placement->reportOutline->report_title_th }}</h3>
                    @if($placement->reportOutline->report_title_en)<p class="text-muted">{{ $placement->reportOutline->report_title_en }}</p>@endif
                    @if($placement->reportOutline->objectives)<div class="small text-muted">วัตถุประสงค์</div><p class="mb-0">{{ $placement->reportOutline->objectives }}</p>@endif
                @else
                    <p class="text-muted mb-0">ยังไม่มีหัวข้อรายงาน</p>
                @endif
            </div>
        </section>
    </div>
</div>

@if($placement->acceptance_status === 'PENDING')
    <div class="d-flex gap-2 justify-content-end mt-4">
        <form method="POST" action="{{ route('company.placements.acceptance', $placement->placement_id) }}">@csrf<input type="hidden" name="acceptance_status" value="ACCEPTED"><button class="btn btn-success" type="submit">ตอบรับนักศึกษา</button></form>
        <form method="POST" action="{{ route('company.placements.acceptance', $placement->placement_id) }}" onsubmit="return confirm('ยืนยันปฏิเสธการรับนักศึกษารายนี้หรือไม่?')">@csrf<input type="hidden" name="acceptance_status" value="REJECTED"><button class="btn btn-outline-danger" type="submit">ปฏิเสธ</button></form>
    </div>
@endif
@endsection
