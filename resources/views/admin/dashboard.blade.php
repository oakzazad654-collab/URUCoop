@extends('layouts.app')

@section('title', 'ภาพรวมระบบ | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมระบบ')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">ผู้ดูแลระบบ · URU Coop-IS</p><h1>ภาพรวมระบบ</h1><p class="page-subtitle">จัดการสิทธิ์ผู้ใช้ ตรวจสอบคิวอนุมัติ และติดตามประวัติระบบ</p></div>
</header>
<section class="stats-grid" aria-label="สรุปภาพรวมระบบ">
    @foreach($stats as $stat)
        <div class="stat-card"><span class="stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span><span class="stat-copy"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></span></div>
    @endforeach
</section>
<section class="dashboard-grid" aria-label="เมนูผู้ดูแลระบบ">
    <a class="dashboard-card" href="{{ route('home') }}"><span class="dashboard-card-icon"><i data-lucide="globe-2"></i></span><span class="dashboard-card-copy"><small>เว็บไซต์สาธารณะ</small><strong>หน้าแรก URU Coop-IS</strong><span>ดูข่าวสารและข้อมูลสำหรับผู้เข้าชมทั่วไป</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.approvals.dashboard') }}"><span class="dashboard-card-icon"><i data-lucide="clipboard-check"></i></span><span class="dashboard-card-copy"><small>เอกสาร CWIE</small><strong>คิวอนุมัติ</strong><span>ตรวจสอบเอกสารที่รอการพิจารณา</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.map') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>สถานที่ฝึกงาน</small><strong>แผนที่นิเทศ</strong><span>ดูตำแหน่งบริษัทและนักศึกษาที่อนุมัติแล้ว</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.users.index') }}"><span class="dashboard-card-icon"><i data-lucide="users-round"></i></span><span class="dashboard-card-copy"><small>บัญชีและสิทธิ์</small><strong>ผู้ใช้งาน</strong><span>จัดการบัญชีและบทบาทในระบบ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.registrations.index') }}"><span class="dashboard-card-icon"><i data-lucide="user-round-check"></i></span><span class="dashboard-card-copy"><small>คำขอสมัคร</small><strong>อนุมัติบัญชีใหม่</strong><span>ตรวจสอบคำขอจากอาจารย์ ผู้นิเทศ และสถานประกอบการ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.directory') }}"><span class="dashboard-card-icon"><i data-lucide="contact-round"></i></span><span class="dashboard-card-copy"><small>ข้อมูลทั้งระบบ</small><strong>ทำเนียบสหกิจศึกษา</strong><span>ดูรายชื่อนักศึกษา สถานประกอบการ และบุคลากร</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.users.assign-advisor') }}"><span class="dashboard-card-icon"><i data-lucide="user-round-cog"></i></span><span class="dashboard-card-copy"><small>การดูแลนักศึกษา</small><strong>จับคู่อาจารย์ที่ปรึกษา</strong><span>กำหนดอาจารย์ให้กับนักศึกษา</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('admin.audit-logs') }}"><span class="dashboard-card-icon"><i data-lucide="history"></i></span><span class="dashboard-card-copy"><small>บันทึกกิจกรรม</small><strong>ประวัติระบบ</strong><span>ตรวจสอบการเปลี่ยนแปลงและการดำเนินการ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
</section>
@endsection
