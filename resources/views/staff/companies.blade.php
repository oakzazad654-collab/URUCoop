@extends('layouts.app')

@section('title', 'สถานประกอบการในสาขาที่ดูแล | URU Coop-IS')
@section('breadcrumb', 'สถานประกอบการ')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">สถานประกอบการที่เกี่ยวข้อง</p><h1>สถานประกอบการในสาขาที่รับผิดชอบ</h1><p class="page-subtitle">แสดงสถานประกอบการที่มีใบสมัครจากนักศึกษาในขอบเขตคณะ/หลักสูตรที่คุณดูแล</p></div></header>
<section class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>สถานประกอบการ</th><th>ประเภทธุรกิจ</th><th>ที่อยู่</th><th>พิกัด</th><th>แผนที่</th></tr></thead><tbody>
@forelse($companies as $company)<tr><td><strong>{{ $company->company_name_th ?: $company->name }}</strong></td><td>{{ $company->business_type ?: '—' }}</td><td>{{ collect([$company->address, $company->district, $company->province])->filter()->implode(', ') ?: '—' }}</td><td>{{ $company->latitude && $company->longitude ? $company->latitude.', '.$company->longitude : '—' }}</td><td>@if($company->latitude && $company->longitude)<a href="https://www.google.com/maps/search/?api=1&query={{ $company->latitude }},{{ $company->longitude }}" target="_blank" rel="noopener">เปิดแผนที่</a>@else—@endif</td></tr>@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="building-2"></i></span><strong>ยังไม่มีสถานประกอบการที่เกี่ยวข้อง</strong><span>สถานประกอบการจะปรากฏเมื่อมีนักศึกษาในขอบเขตส่งใบสมัคร</span></div></td></tr>@endforelse
</tbody></table></div>@if($companies->hasPages())<div class="card-footer bg-white">{{ $companies->links('pagination::bootstrap-4') }}</div>@endif</section>
@endsection
