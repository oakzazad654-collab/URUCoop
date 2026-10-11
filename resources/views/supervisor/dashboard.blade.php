@extends('layouts.app')

@section('title', 'ภาพรวมอาจารย์นิเทศ | URU Coop-IS')
@section('breadcrumb', 'ภาพรวมอาจารย์นิเทศ')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">อาจารย์นิเทศ · URU Coop-IS</p><h1>ภาพรวมอาจารย์นิเทศ</h1><p class="page-subtitle">ติดตามนักศึกษาและกำหนดการนิเทศในคณะที่รับผิดชอบ</p></div>
</header>
<section class="stats-grid" aria-label="สรุปงานอาจารย์นิเทศ">
    @foreach($stats as $stat)
        <div class="stat-card"><span class="stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span><span class="stat-copy"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></span></div>
    @endforeach
</section>
<section class="dashboard-grid" aria-label="เมนูอาจารย์นิเทศ">
    <a class="dashboard-card" href="{{ route('workflow.index') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>การนิเทศสหกิจศึกษา</small><strong>กำหนดการและผลนิเทศ</strong><span>จัดการคำขอนิเทศและบันทึกผลในคณะที่รับผิดชอบ</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('supervisor.map') }}"><span class="dashboard-card-icon"><i data-lucide="map-pinned"></i></span><span class="dashboard-card-copy"><small>สถานที่ฝึกงาน</small><strong>แผนที่นิเทศ</strong><span>ดูพิกัดสถานประกอบการและนักศึกษาในขอบเขต</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('supervisor.companies') }}"><span class="dashboard-card-icon"><i data-lucide="building-2"></i></span><span class="dashboard-card-copy"><small>สถานประกอบการ</small><strong>สถานประกอบการในสาขา</strong><span>ค้นหาสถานประกอบการที่เกี่ยวข้องกับนักศึกษา</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
    <a class="dashboard-card" href="{{ route('home') }}"><span class="dashboard-card-icon"><i data-lucide="globe-2"></i></span><span class="dashboard-card-copy"><small>เว็บไซต์สาธารณะ</small><strong>หน้าแรก URU Coop-IS</strong><span>ดูข่าวสารและข้อมูลสำหรับผู้เข้าชมทั่วไป</span></span><i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i></a>
</section>
@endsection