@extends('layouts.app')

@php($editing = isset($term))

@section('title', ($editing ? 'แก้ไข' : 'เพิ่ม').'ภาคการศึกษา | URU Coop-IS')
@section('breadcrumb', $editing ? 'แก้ไขภาคการศึกษา' : 'เพิ่มภาคการศึกษา')

@section('content')
<header class="page-heading">
    <div>
        <p class="page-eyebrow">ข้อมูลพื้นฐาน · ผู้ดูแลระบบ</p>
        <h1>{{ $editing ? 'แก้ไขภาคการศึกษา' : 'เพิ่มภาคการศึกษา' }}</h1>
        <p class="page-subtitle">รายการภาคการศึกษาที่มีใบสมัครอ้างอิงอยู่จะไม่สามารถลบออกจากระบบได้</p>
    </div>
</header>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>บันทึกข้อมูลไม่สำเร็จ</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<section class="card">
    <div class="card-body">
        <form method="POST" action="{{ $editing ? route('admin.academic-terms.update', $term) : route('admin.academic-terms.store') }}" class="row g-3">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="col-md-6">
                <label class="form-label" for="semester_no">ภาคการศึกษาที่</label>
                <select class="form-select" id="semester_no" name="semester_no" required>
                    <option value="">เลือกภาคการศึกษา</option>
                    @foreach([1, 2, 3] as $semester)
                        <option value="{{ $semester }}" @selected((string) old('semester_no', $term->semester_no ?? '') === (string) $semester)>{{ $semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="academic_year">ปีการศึกษา (ค.ศ.)</label>
                <input class="form-control" id="academic_year" name="academic_year" type="number" min="1900" max="2200" value="{{ old('academic_year', $term->academic_year ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="start_date">วันเริ่มภาคการศึกษา</label>
                <input class="form-control" id="start_date" name="start_date" type="date" value="{{ old('start_date', isset($term) && $term->start_date ? $term->start_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="end_date">วันสิ้นสุดภาคการศึกษา</label>
                <input class="form-control" id="end_date" name="end_date" type="date" value="{{ old('end_date', isset($term) && $term->end_date ? $term->end_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="registration_start_date">วันเริ่มรับสมัคร</label>
                <input class="form-control" id="registration_start_date" name="registration_start_date" type="date" value="{{ old('registration_start_date', isset($term) && $term->registration_start_date ? $term->registration_start_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="registration_end_date">วันสิ้นสุดรับสมัคร</label>
                <input class="form-control" id="registration_end_date" name="registration_end_date" type="date" value="{{ old('registration_end_date', isset($term) && $term->registration_end_date ? $term->registration_end_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="status">สถานะ</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['PLANNED' => 'วางแผน', 'OPEN' => 'เปิดรับสมัคร', 'CLOSED' => 'ปิดรับสมัคร'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $term->status ?? 'PLANNED') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">บันทึกภาคการศึกษา</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.academic-terms.index') }}">ยกเลิก</a>
            </div>
        </form>
    </div>
</section>
@endsection
