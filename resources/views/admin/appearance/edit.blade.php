@extends('layouts.app')

@section('title', 'ปรับแต่งหน้าแรก | URU Coop-IS')
@section('breadcrumb', 'ปรับแต่งหน้าแรก')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">เว็บไซต์สาธารณะ · ผู้ดูแลระบบ</p><h1>ภาพพื้นหลังหน้าแรกและวิดีโอ</h1><p class="page-subtitle">จัดการภาพพื้นหลังและวิดีโอ YouTube ที่แสดงบนหน้าแรก</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('home') }}" target="_blank" rel="noopener"><i data-lucide="external-link" class="me-1" style="width:15px;height:15px"></i>ดูหน้าเว็บไซต์</a>
</header>

@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

<section class="card appearance-card">
    <div class="card-header bg-white"><h2 class="h6 mb-1">ตัวอย่างภาพปัจจุบัน</h2><span class="small text-muted">ระบบจะวาง overlay สีเขียวทับภาพเพื่อให้อ่านหัวข้อได้ชัด</span></div>
    <div class="card-body">
        @if($backgroundImage)
            <div class="appearance-preview" style="background-image:linear-gradient(90deg,rgba(13,50,35,.9),rgba(13,50,35,.54)),url('{{ $backgroundImage }}')"><span>URU Coop-IS</span><small>ตัวอย่างส่วนหัวหน้าเว็บไซต์</small></div>
        @else
            <div class="appearance-preview appearance-preview-empty"><span>URU Coop-IS</span><small>กำลังใช้พื้นหลังสีเขียวเดิม</small></div>
        @endif

        <form method="POST" action="{{ route('admin.appearance.background.update') }}" enctype="multipart/form-data" class="appearance-upload-form">
            @csrf
            @method('PUT')
            <div class="appearance-upload-copy"><label class="form-label" for="background_image">เลือกรูปพื้นหลังใหม่</label><p class="small text-muted mb-0">รองรับ JPG, PNG, WebP ขนาดไม่เกิน 8 MB แนะนำภาพแนวนอนอย่างน้อย 1600 × 600 px</p></div>
            <div class="appearance-upload-controls"><input class="form-control" id="background_image" name="background_image" type="file" accept=".jpg,.jpeg,.png,.webp" required><button class="btn btn-primary" type="submit"><i data-lucide="image-up" class="me-1" style="width:16px;height:16px"></i>บันทึกภาพพื้นหลัง</button></div>
        </form>

        @if($backgroundImage)
            <form method="POST" action="{{ route('admin.appearance.background.destroy') }}" class="appearance-remove-form" onsubmit="return confirm('นำภาพพื้นหลังออกและกลับไปใช้พื้นหลังสีเขียวเดิมหรือไม่?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit"><i data-lucide="trash-2" class="me-1" style="width:15px;height:15px"></i>นำภาพออก</button>
            </form>
        @endif
    </div>
</section>

<section class="card appearance-card mt-4">
    <div class="card-header bg-white">
        <h2 class="h6 mb-1">วิดีโอ YouTube บนหน้าแรก</h2>
        <span class="small text-muted">วางลิงก์วิดีโอ YouTube เพื่อแสดงคลิปบนหน้าเว็บไซต์สาธารณะ เฉพาะผู้ดูแลระบบเท่านั้นที่แก้ไขได้</span>
    </div>
    <div class="card-body">
        @if($youtubeVideoId)
            <div class="appearance-video-preview mb-3">
                <iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeVideoId }}" title="ตัวอย่างวิดีโอ YouTube บนหน้าแรก" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        @else
            <p class="text-muted small mb-3">ยังไม่ได้กำหนดวิดีโอ YouTube</p>
        @endif

        <form method="POST" action="{{ route('admin.appearance.youtube.update') }}" class="row g-3 align-items-end">
            @csrf
            @method('PUT')
            <div class="col-md-9">
                <label class="form-label" for="youtube_url">ลิงก์วิดีโอ YouTube</label>
                <input class="form-control" id="youtube_url" name="youtube_url" type="url" maxlength="2048" value="{{ old('youtube_url', $youtubeVideoId ? 'https://www.youtube.com/watch?v='.$youtubeVideoId : '') }}" placeholder="https://www.youtube.com/watch?v=…" aria-describedby="youtube-url-help">
                <div class="form-text" id="youtube-url-help">รองรับลิงก์ youtube.com/watch, youtu.be, shorts และ embed; เว้นว่างแล้วบันทึกเพื่อนำคลิปออก</div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">บันทึกวิดีโอ</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
    .appearance-card{overflow:hidden}.appearance-preview{min-height:260px;aspect-ratio:16/6;display:flex;flex-direction:column;justify-content:center;gap:8px;padding:32px 40px;background-color:#123d2c;background-position:center;background-size:cover;border-radius:6px;color:#fff}.appearance-preview span{font-size:30px;font-weight:600}.appearance-preview small{color:#d8e8dc;font-size:12px}.appearance-preview-empty{background-image:radial-gradient(circle at 90% 20%,rgba(255,255,255,.11),transparent 42%);background-color:#123d2c}
    .appearance-upload-form{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,1.1fr);gap:20px;align-items:end;margin-top:22px}.appearance-upload-copy .form-label{font-weight:600}.appearance-upload-controls{display:flex;gap:10px;align-items:center}.appearance-upload-controls .form-control{min-width:0}.appearance-upload-controls .btn{white-space:nowrap}.appearance-remove-form{margin-top:16px;padding-top:16px;border-top:1px solid #e2e8e4}
    .appearance-video-preview{max-width:760px;aspect-ratio:16/9;background:#10251a;border-radius:8px;overflow:hidden}.appearance-video-preview iframe{width:100%;height:100%;border:0}
    @media(max-width:700px){.appearance-preview{min-height:200px;padding:24px;aspect-ratio:4/3}.appearance-preview span{font-size:24px}.appearance-upload-form{grid-template-columns:1fr;gap:12px}.appearance-upload-controls{align-items:stretch;flex-direction:column}}
</style>
@endpush