@extends('layouts.app')

@php($isEditing = $announcement->exists)
@section('title', ($isEditing ? 'แก้ไขข่าว' : 'เพิ่มข่าว').' | URU Coop-IS')
@section('breadcrumb', $isEditing ? 'แก้ไขข่าว' : 'เพิ่มข่าว')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">จัดการเนื้อหาสาธารณะ</p><h1>{{ $isEditing ? 'แก้ไขข่าว' : 'เพิ่มข่าว' }}</h1><p class="page-subtitle">ข่าวที่เผยแพร่แล้วจะแสดงให้ผู้ใช้ทั่วไปเห็น</p></div><a class="btn btn-outline-secondary" href="{{ route('admin.news.index') }}"><i data-lucide="arrow-left" class="me-1" style="width:15px;height:15px"></i>กลับรายการ</a></header>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<section class="card"><div class="card-body p-4">
    <form method="POST" action="{{ $isEditing ? route('admin.news.update', $announcement) : route('admin.news.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf @if($isEditing)@method('PUT')@endif
        <div class="col-12"><label class="form-label" for="title">หัวข้อข่าว</label><input class="form-control" id="title" name="title" value="{{ old('title', $announcement->title) }}" maxlength="255" required></div>
        <div class="col-12"><label class="form-label" for="excerpt">ข้อความสรุป</label><textarea class="form-control" id="excerpt" name="excerpt" rows="2" maxlength="1000">{{ old('excerpt', $announcement->excerpt) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="body">เนื้อหาข่าว</label><textarea class="form-control" id="body" name="body" rows="12" maxlength="50000" required>{{ old('body', $announcement->body) }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="image">ภาพประกอบ (JPG, PNG หรือ WebP ไม่เกิน 5 MB)</label><input class="form-control" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">@if($announcement->image_path)<div class="small text-muted mt-2">มีภาพประกอบปัจจุบันแล้ว การเลือกไฟล์ใหม่จะแทนที่ภาพเดิม</div>@endif</div>
        <div class="col-md-3"><label class="form-label" for="is_published">สถานะ</label><select class="form-select" id="is_published" name="is_published" required><option value="0" {{ (string) old('is_published', $announcement->is_published ? '1' : '0') === '0' ? 'selected' : '' }}>ฉบับร่าง</option><option value="1" {{ (string) old('is_published', $announcement->is_published ? '1' : '0') === '1' ? 'selected' : '' }}>เผยแพร่</option></select></div>
        <div class="col-md-3"><label class="form-label" for="published_at">วันเวลาเผยแพร่</label><input class="form-control" id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', optional($announcement->published_at)->format('Y-m-d\TH:i')) }}"></div>
        <div class="col-12 d-flex gap-2 pt-2"><button class="btn btn-primary" type="submit"><i data-lucide="save" class="me-1" style="width:16px;height:16px"></i>{{ $isEditing ? 'บันทึกการแก้ไข' : 'บันทึกข่าว' }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.news.index') }}">ยกเลิก</a></div>
    </form>
</div></section>
@endsection