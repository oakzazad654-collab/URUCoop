@extends('layouts.app')

@section('title', 'ภาพรวมนักศึกษา | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมนักศึกษา')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">พื้นที่นักศึกษา · URU Coop-IS</p><h1>ภาพรวมนักศึกษา</h1><p class="page-subtitle">ยื่นเอกสารสหกิจศึกษาและติดตามผลการพิจารณา</p></div>
</header>
<section class="stats-grid" aria-label="สรุปสถานะสหกิจศึกษา">
    @foreach($stats as $stat)
        <div class="stat-card"><span class="stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span><span class="stat-copy"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></span></div>
    @endforeach
</section>
<section class="dashboard-grid" aria-label="เมนูนักศึกษา">
    <a class="dashboard-card" href="{{ route('student.map') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>สถานที่ฝึกงาน</small><strong>แผนที่สถานประกอบการ</strong><span>ดูตำแหน่งสถานประกอบการและการเดินทาง</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('public.forms') }}"><span class="dashboard-card-icon"><i data-lucide="file-down"></i></span><span class="dashboard-card-copy"><small>เอกสารอ้างอิง</small><strong>คู่มือและแบบฟอร์ม</strong><span>ดาวน์โหลดแบบฟอร์ม CWIE และ สก.</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('home') }}"><span class="dashboard-card-icon"><i data-lucide="globe-2"></i></span><span class="dashboard-card-copy"><small>เว็บไซต์สาธารณะ</small><strong>หน้าแรก URU Coop-IS</strong><span>ดูข่าวสารและข้อมูลสำหรับผู้เข้าชมทั่วไป</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('student.cwie.index') }}"><span class="dashboard-card-icon"><i data-lucide="files"></i></span><span class="dashboard-card-copy"><small>เอกสารสหกิจศึกษา</small><strong>แบบฟอร์ม สก. 01–15</strong><span>กรอก แก้ไข ดูพรีวิว และดาวน์โหลด PDF</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('student.approvals.dashboard') }}"><span class="dashboard-card-icon"><i data-lucide="clipboard-check"></i></span><span class="dashboard-card-copy"><small>การพิจารณา</small><strong>ติดตามสถานะ</strong><span>ดูผลและประวัติการอนุมัติเอกสาร</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('account.settings') }}"><span class="dashboard-card-icon"><i data-lucide="settings-2"></i></span><span class="dashboard-card-copy"><small>บัญชีผู้ใช้</small><strong>การตั้งค่า</strong><span>จัดการการแจ้งเตือนและข้อมูลบัญชี</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
</section>
@endsection
