@extends('layouts.public')

@section('title', 'คู่มือและแบบฟอร์ม | URU Coop-IS')

@section('content')
<header class="public-info-heading">
    <p class="public-home-eyebrow">ดาวน์โหลดเอกสาร</p>
    <h1>คู่มือและแบบฟอร์มสหกิจศึกษา</h1>
    <p>เลือกดาวน์โหลดแบบฟอร์มและเอกสารอ้างอิงที่ใช้ประกอบการดำเนินงาน</p>
</header>
<div class="alert alert-warning"><strong>หมายเหตุ:</strong> เอกสารที่ระบุว่าเป็นฉบับเก่าอาจไม่ใช่แบบฟอร์มปัจจุบัน โปรดตรวจสอบกับศูนย์สหกิจศึกษาก่อนนำไปใช้</div>
@foreach($forms as $group)
    <section class="public-form-group">
        <div class="public-info-section-heading"><div><h2>{{ $group['title'] }}</h2><p>{{ $group['description'] }}</p></div></div>
        <div class="public-form-list">
            @foreach($group['files'] as $file)
                <a class="public-form-link" href="{{ $file['url'] ?? asset('forms/'.$file['file']) }}" target="_blank" rel="noopener"><span class="public-form-icon"><i data-lucide="file-down"></i></span><span><strong>{{ $file['name'] }}</strong><small>{{ strtoupper(pathinfo($file['file'], PATHINFO_EXTENSION)) }}</small></span><i data-lucide="arrow-down-to-line"></i></a>
            @endforeach
        </div>
    </section>
@endforeach
@endsection

@push('styles')
<style>
    .public-info-heading{max-width:850px;margin:0 auto 24px}.public-info-heading h1{margin:0;color:#183528;font-size:28px;font-weight:600}.public-info-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px;line-height:1.8}.public-form-group{margin-top:25px}.public-info-section-heading{margin-bottom:12px}.public-info-section-heading h2{margin:0;color:#20392a;font-size:18px}.public-info-section-heading p{margin:4px 0 0;color:#718176;font-size:12px}.public-form-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.public-form-link{display:flex;align-items:center;gap:11px;min-width:0;padding:13px 15px;background:#fff;border:1px solid #dfe9e1;border-radius:9px;color:#274333;text-decoration:none}.public-form-link:hover{border-color:#9fc8a8;background:#f9fcf9}.public-form-icon{width:34px;height:34px;display:grid;place-items:center;flex:0 0 auto;border-radius:8px;background:#eaf4ec;color:#27824f}.public-form-icon svg,.public-form-link>svg{width:16px;height:16px}.public-form-link>span:nth-child(2){flex:1;min-width:0}.public-form-link strong,.public-form-link small{display:block}.public-form-link strong{font-size:12px}.public-form-link small{margin-top:3px;color:#819087;font-size:9px}.public-form-link>svg:last-child{color:#548161}@media(max-width:600px){.public-form-list{grid-template-columns:1fr}.public-info-heading h1{font-size:24px}}
</style>
@endpush
