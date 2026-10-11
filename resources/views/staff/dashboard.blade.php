@extends('layouts.app')

@section('title', 'ภาพรวมบุคลากร | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมบุคลากร')

@section('content')
@php($role = auth()->user()->role)
<header class="page-heading">
    <div><p class="page-eyebrow">{{ $role === 'advisor' ? 'อาจารย์ที่ปรึกษา' : 'เจ้าหน้าที่ศูนย์สหกิจศึกษา' }} · URU Coop-IS</p><h1>ภาพรวมบุคลากร</h1><p class="page-subtitle">{{ $role === 'advisor' ? 'พิจารณาเอกสารและดูแลนักศึกษาในความรับผิดชอบ' : 'ตรวจสอบเอกสารและจัดการข้อมูลสหกิจศึกษา' }}</p></div>
</header>
<section class="stats-grid" aria-label="สรุปงานสหกิจศึกษา">
    @foreach($stats as $stat)
        <div class="stat-card"><span class="stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span><span class="stat-copy"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></span></div>
    @endforeach
</section>
<section class="dashboard-grid" aria-label="เมนูบุคลากร">
    <a class="dashboard-card" href="{{ route('home') }}"><span class="dashboard-card-icon"><i data-lucide="globe-2"></i></span><span class="dashboard-card-copy"><small>เว็บไซต์สาธารณะ</small><strong>หน้าแรก URU Coop-IS</strong><span>ดูข่าวสารและข้อมูลสำหรับผู้เข้าชมทั่วไป</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    @if($role === 'advisor')
        <a class="dashboard-card" href="{{ route('advisor.approvals.dashboard') }}"><span class="dashboard-card-icon"><i data-lucide="clipboard-check"></i></span><span class="dashboard-card-copy"><small>เอกสาร CWIE</small><strong>คิวพิจารณา</strong><span>ตรวจเอกสารที่รอความเห็นจากอาจารย์</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('staff.map') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>สถานที่ฝึกงาน</small><strong>แผนที่นิเทศ</strong><span>ดูตำแหน่งบริษัทและนักศึกษาที่อนุมัติแล้ว</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('advisor.students') }}"><span class="dashboard-card-icon"><i data-lucide="users-round"></i></span><span class="dashboard-card-copy"><small>นักศึกษา</small><strong>นักศึกษาในดูแล</strong><span>ดูรายชื่อและข้อมูลนักศึกษาที่รับผิดชอบ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('advisor.companies') }}"><span class="dashboard-card-icon"><i data-lucide="building-2"></i></span><span class="dashboard-card-copy"><small>สถานประกอบการ</small><strong>สถานประกอบการในสาขา</strong><span>ดูสถานประกอบการที่นักศึกษาในสาขาสมัคร</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('advisor.cwie06.create') }}"><span class="dashboard-card-icon"><i data-lucide="file-plus-2"></i></span><span class="dashboard-card-copy"><small>การนิเทศงาน</small><strong>สร้างคำขอ CWIE 06</strong><span>ส่งคำขออนุญาตออกนิเทศงาน</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    @else
        <a class="dashboard-card" href="{{ route('officer.approvals.dashboard') }}"><span class="dashboard-card-icon"><i data-lucide="clipboard-check"></i></span><span class="dashboard-card-copy"><small>เอกสาร CWIE</small><strong>คิวอนุมัติ</strong><span>ตรวจเอกสารที่ผ่านการพิจารณาจากอาจารย์</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('staff.map') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>สถานที่ฝึกงาน</small><strong>แผนที่นิเทศ</strong><span>ดูตำแหน่งบริษัทและนักศึกษาที่อนุมัติแล้ว</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('officer.documents') }}"><span class="dashboard-card-icon"><i data-lucide="files"></i></span><span class="dashboard-card-copy"><small>เอกสาร CWIE</small><strong>เอกสารทั้งหมด</strong><span>ค้นหาและติดตามเอกสารทุกสถานะ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('officer.companies.index') }}"><span class="dashboard-card-icon"><i data-lucide="building-2"></i></span><span class="dashboard-card-copy"><small>ฐานข้อมูล</small><strong>สถานประกอบการ</strong><span>จัดการข้อมูลสถานที่ปฏิบัติงาน</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
        <a class="dashboard-card" href="{{ route('officer.users.index') }}"><span class="dashboard-card-icon"><i data-lucide="users-round"></i></span><span class="dashboard-card-copy"><small>บัญชีผู้ใช้</small><strong>ผู้ใช้งาน</strong><span>ดูแลบัญชีในระบบสหกิจศึกษา</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    @endif
</section>
@endsection
