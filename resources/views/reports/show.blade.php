@extends('layouts.app')

@section('title', $report['title'].' | URU Coop-IS')
@section('breadcrumb', $report['title'])

@section('content')
<header class="page-heading">
    <div>
        <p class="page-eyebrow">URU Coop-IS · รายงาน</p>
        <h1>{{ $report['title'] }}</h1>
        <p class="page-subtitle">แสดงข้อมูลตามขอบเขตสิทธิ์ของบัญชีที่เข้าสู่ระบบ</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">กลับไปรายงานทั้งหมด</a>
        <a class="btn btn-outline-primary" href="{{ route('reports.show', ['reportKey' => $reportKey, 'format' => 'csv']) }}">ดาวน์โหลด CSV</a>
        <a class="btn btn-primary" href="{{ route('reports.show', ['reportKey' => $reportKey, 'format' => 'pdf']) }}">ดาวน์โหลด PDF</a>
    </div>
</header>

<section class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">{{ $report['title'] }}</h2>
        <span class="badge text-bg-light">{{ count($report['data']) }} รายการ</span>
    </div>
    <div class="card-body">
        @if($report['data'])
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            @foreach(array_keys((array) $report['data'][0]) as $column)
                                <th scope="col">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data'] as $row)
                            <tr>
                                @foreach((array) $row as $value)
                                    <td>{{ is_scalar($value) ? $value : '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0" role="status">ยังไม่มีข้อมูลสำหรับรายงานนี้</p>
        @endif
    </div>
</section>
@endsection
