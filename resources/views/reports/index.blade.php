@extends('layouts.app')

@section('title', 'รายงานและสถิติ | URU Coop-IS')
@section('breadcrumb', 'รายงานและสถิติ')

@section('content')
<header class="page-heading">
    <div>
        <p class="page-eyebrow">URU Coop-IS · รายงาน</p>
        <h1>รายงานและสถิติ</h1>
        <p class="page-subtitle">ข้อมูลรายงานจะแสดงเฉพาะรายการที่คุณมีสิทธิ์เข้าถึง</p>
    </div>
</header>

<section class="dashboard-grid" aria-label="รายงานที่ใช้งานได้">
    @foreach($reports as $reportKey => $report)
        <a class="dashboard-card" href="{{ route('reports.show', $reportKey) }}">
            <span class="dashboard-card-icon"><i data-lucide="chart-no-axes-combined"></i></span>
            <span class="dashboard-card-copy">
                <small>รายงานสหกิจศึกษา</small>
                <strong>{{ $report['title'] }}</strong>
                <span>{{ $report['description'] }}</span>
            </span>
            <i class="dashboard-card-arrow" data-lucide="arrow-up-right"></i>
        </a>
    @endforeach
</section>
@endsection
