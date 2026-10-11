@extends('layouts.app')

@section('title', 'จับคู่อาจารย์ที่ปรึกษา | URU Coop-IS')
@section('breadcrumb', 'จับคู่อาจารย์ที่ปรึกษา')

@section('content')
@php($isOfficer = auth()->user()->role === 'officer')
<header class="page-heading"><div><p class="page-eyebrow">การกำหนดผู้ดูแลนักศึกษา</p><h1>จับคู่อาจารย์ที่ปรึกษา</h1><p class="page-subtitle">เลือกนักศึกษาหนึ่งคนหรือหลายคนเพื่อกำหนดอาจารย์ที่ปรึกษา</p></div><a href="{{ $isOfficer ? route('officer.users.index') : route('admin.users.index') }}" class="btn btn-outline-secondary"><i data-lucide="users-round" class="me-1" style="width:15px;height:15px"></i>ผู้ใช้งาน</a></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ $isOfficer ? route('officer.users.save-advisor') : route('admin.users.save-advisor') }}">
    @csrf
    <section class="card mb-3"><div class="card-body"><div class="row align-items-end g-3">
        <div class="col-lg-5"><label class="form-label" for="advisor_id">อาจารย์ที่ปรึกษา</label><select id="advisor_id" name="advisor_id" class="form-select"><option value="">ยกเลิกการจับคู่</option>@foreach($advisors as $advisor)<option value="{{ $advisor->id }}">{{ $advisor->name }} · {{ $advisor->faculty->name ?? 'ไม่ระบุคณะ' }}</option>@endforeach</select></div>
        <div class="col-lg-4"><label class="form-label" for="faculty_filter">กรองตามคณะ</label><select id="faculty_filter" class="form-select"><option value="">ทุกคณะ</option>@foreach($faculties as $faculty)<option value="{{ $faculty->name }}">{{ $faculty->name }}</option>@endforeach</select></div>
        <div class="col-lg-3"><button class="btn btn-primary w-100"><i data-lucide="user-round-check" class="me-1" style="width:16px;height:16px"></i>บันทึกการจับคู่</button></div>
    </div></div></section>
    <section class="card"><div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="h6 mb-0">นักศึกษา</h2><span class="small text-muted">{{ $students->count() }} รายการ</span></div><label class="form-check mb-0"><input type="checkbox" id="select_all" class="form-check-input"><span class="form-check-label small">เลือกทั้งหมด</span></label></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th></th><th>นักศึกษา</th><th>รหัส</th><th>คณะ/สาขา</th><th>อาจารย์ปัจจุบัน</th></tr></thead>
    <tbody>@forelse($students as $student)<tr data-faculty="{{ $student->faculty->name ?? '' }}" data-search-row><td><input type="checkbox" class="form-check-input student-check" name="student_ids[]" value="{{ $student->id }}" aria-label="เลือก {{ $student->name }}"></td><td><strong>{{ $student->name }}</strong></td><td>{{ $student->student_number ?: '—' }}</td><td>{{ $student->faculty->name ?? '—' }}<div class="small text-muted">{{ $student->department->name ?? '' }}</div></td><td>{{ $student->advisor->name ?? 'ยังไม่กำหนด' }}</td></tr>@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="users-round"></i></span><strong>ไม่มีบัญชีนักศึกษา</strong></div></td></tr>@endforelse</tbody>
    </table></div></section>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('select_all').addEventListener('change', function (event) { document.querySelectorAll('.student-check').forEach(function (checkbox) { if (!checkbox.closest('tr').hidden) checkbox.checked = event.target.checked; }); });
    document.getElementById('faculty_filter').addEventListener('change', function (event) { document.querySelectorAll('tbody tr[data-faculty]').forEach(function (row) { row.hidden = !!event.target.value && row.dataset.faculty !== event.target.value; }); });
</script>
@endpush
