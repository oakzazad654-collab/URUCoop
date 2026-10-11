@extends('layouts.app')

@section('title', 'ภาพรวมผู้บริหาร | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมผู้บริหาร')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">ผู้บริหาร · URU Coop-IS</p><h1>ภาพรวมผู้บริหาร</h1><p class="page-subtitle">ติดตามภาพรวมสหกิจศึกษาและรายการอนุมัติที่ได้รับมอบหมาย</p></div>
</header>
<section class="stats-grid" aria-label="สรุปภาพรวมผู้บริหาร">
    @foreach($stats as $stat)
        <div class="stat-card"><span class="stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span><span class="stat-copy"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></span></div>
    @endforeach
</section>
<section class="dashboard-grid" aria-label="เมนูผู้บริหาร">
    <a class="dashboard-card" href="{{ route('workflow.index') }}"><span class="dashboard-card-icon"><i data-lucide="clipboard-check"></i></span><span class="dashboard-card-copy"><small>การดำเนินงาน</small><strong>รายการรอพิจารณา</strong><span>ดูและดำเนินการเฉพาะรายการอนุมัติที่มอบหมายให้คุณ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('executive.directory') }}"><span class="dashboard-card-icon"><i data-lucide="contact-round"></i></span><span class="dashboard-card-copy"><small>ข้อมูลทั้งระบบ</small><strong>ทำเนียบสหกิจศึกษา</strong><span>ดูรายชื่อนักศึกษา สถานประกอบการ และบุคลากร</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('home') }}"><span class="dashboard-card-icon"><i data-lucide="globe-2"></i></span><span class="dashboard-card-copy"><small>เว็บไซต์สาธารณะ</small><strong>หน้าแรก URU Coop-IS</strong><span>ดูข่าวสารและข้อมูลสำหรับผู้เข้าชมทั่วไป</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
</section>
@endsection