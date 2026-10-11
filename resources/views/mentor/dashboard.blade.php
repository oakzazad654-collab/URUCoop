@extends('layouts.app')

@section('title', 'ภาพรวมผู้นิเทศงาน | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมผู้นิเทศงาน')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">ผู้นิเทศงานสถานประกอบการ · URU Coop-IS</p><h1>{{ trim($contact->first_name.' '.$contact->last_name) }}</h1><p class="page-subtitle">{{ optional($contact->company)->company_name_th ?: optional($contact->company)->name }} · {{ $contact->position_name ?: 'ผู้นิเทศงาน' }}</p></div></header>
<section class="stats-grid mb-4" aria-label="สรุปงานผู้นิเทศ"><div class="stat-card"><span class="stat-icon"><i data-lucide="users-round"></i></span><span class="stat-copy"><span>นักศึกษาที่รับผิดชอบ</span><strong>{{ $placements->count() }}</strong></span></div><div class="stat-card"><span class="stat-icon"><i data-lucide="clipboard-check"></i></span><span class="stat-copy"><span>แผนงานรออนุมัติ</span><strong>{{ $approvals->count() }}</strong></span></div></section>
<section class="card mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">รายการรอพิจารณา</h2></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>นักศึกษา</th><th>สถานประกอบการ</th><th>หัวข้อแผนงาน</th><th>กิจกรรม</th><th>สถานะ</th></tr></thead><tbody>
@forelse($approvals as $approval)@php($plan=$workPlans->get($approval->document_id))
<tr><td>{{ optional(optional(optional(optional($plan)->placement)->student)->user)->name ?: '—' }}</td><td>{{ optional(optional(optional($plan)->placement)->company)->company_name_th ?: optional(optional(optional($plan)->placement)->company)->name }}</td><td>{{ optional($plan)->plan_title }}</td><td>{{ optional(optional($plan)->items)->count() ?: 0 }}</td><td><span class="badge text-bg-warning">รอตรวจใน Workflow</span></td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="clipboard-check"></i></span><strong>ไม่มีแผนงานรอพิจารณา</strong><span>แผนที่นักศึกษาส่งให้คุณจะปรากฏที่นี่</span></div></td></tr>@endforelse
</tbody></table></div></section>
<section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">นักศึกษาที่รับผิดชอบ</h2><span class="small text-muted">แสดงเฉพาะ placement ที่ระบุคุณเป็นผู้นิเทศงาน</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>นักศึกษา</th><th>รหัส</th><th>ตำแหน่งงาน</th><th>ช่วงปฏิบัติงาน</th><th>แผนงาน/รายงาน</th></tr></thead><tbody>
@forelse($placements as $placement)<tr><td><strong>{{ optional(optional($placement->student)->user)->name ?: optional($placement->student)->student_code }}</strong></td><td>{{ optional($placement->student)->student_code }}</td><td>{{ $placement->job_position }}</td><td>{{ optional($placement->work_start_date)->format('d/m/Y') }} – {{ optional($placement->work_end_date)->format('d/m/Y') }}</td><td>{{ optional($placement->workPlan)->plan_title ?: 'ยังไม่มีแผนงาน' }}<div class="small text-muted">{{ optional($placement->reportOutline)->report_title_th ?: 'ยังไม่มีโครงร่างรายงาน' }}</div></td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="users-round"></i></span><strong>ยังไม่มีนักศึกษาที่ได้รับมอบหมาย</strong><span>รายการจะแสดงเมื่อ placement ระบุคุณเป็นผู้นิเทศงาน</span></div></td></tr>@endforelse
</tbody></table></div></section>
<div class="mt-3"><a class="btn btn-primary" href="{{ route('workflow.index') }}">เปิดรายการอนุมัติและรายละเอียดงาน</a></div>
@endsection
