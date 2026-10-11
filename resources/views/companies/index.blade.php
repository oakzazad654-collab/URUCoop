@extends('layouts.app')

@section('title', 'สถานประกอบการ | URU Coop-IS')
@section('breadcrumb', 'สถานประกอบการ')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">ข้อมูลสถานประกอบการ</p><h1>สถานประกอบการ</h1><p class="page-subtitle">จัดการข้อมูลสถานประกอบการสหกิจศึกษา</p></div><a href="{{ route('officer.companies.create') }}" class="btn btn-primary"><i data-lucide="plus" class="me-1" style="width:16px;height:16px"></i>เพิ่มสถานประกอบการ</a></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="company-map-layout">
    <section class="card map-card"><div class="card-header bg-white"><h2 class="h6 mb-0">แผนที่สถานประกอบการ</h2><span class="small text-muted">{{ $companies->count() }} แห่ง</span></div><div class="map-frame"><div id="mainMap" aria-label="แผนที่สถานประกอบการ"></div></div></section>
    <section class="card company-list-card"><div class="card-header bg-white"><h2 class="h6 mb-0">รายการสถานประกอบการ</h2></div><div class="company-list">
        @forelse($companies as $company)
            <article class="company-list-item searchable-item" data-search="{{ $company->name }} {{ $company->address }} {{ $company->contact_person }}"><div class="d-flex justify-content-between gap-2"><a href="{{ route('officer.companies.show', $company) }}" class="company-name">{{ $company->name }}</a><span class="badge {{ $company->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $company->status }}</span></div><p class="small text-muted mt-1 mb-2">{{ $company->address ?: 'ไม่ระบุที่อยู่' }}</p><div class="d-flex flex-wrap gap-2"><a href="{{ route('officer.companies.show', $company) }}" class="btn btn-sm btn-outline-primary">รายละเอียด</a><a href="{{ route('officer.companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary">แก้ไข</a><form action="{{ route('officer.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลหรือไม่?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">ลบ</button></form></div></article>
        @empty<div class="empty-state"><span class="empty-icon"><i data-lucide="building-2"></i></span><strong>ยังไม่มีสถานประกอบการ</strong></div>@endforelse
    </div></section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>.company-map-layout{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(290px,.9fr);gap:16px}.map-card{min-width:0}.map-frame{height:min(68vh,690px);min-height:420px;padding:10px}.map-frame #mainMap{width:100%;height:100%;border-radius:12px}.company-list-card{min-width:0;max-height:min(68vh,690px);overflow:hidden}.company-list{overflow:auto}.company-list-item{padding:15px 17px;border-bottom:1px solid var(--slate-100)}.company-name{color:var(--slate-800);font-size:13px;font-weight:600;text-decoration:none}.company-name:hover{color:var(--green-700)}@media(max-width:1000px){.company-map-layout{grid-template-columns:1fr}.company-list-card{max-height:none}.company-list{max-height:450px}}@media(max-width:600px){.map-frame{height:54vh;min-height:320px;padding:7px}}</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@php
    $companyMapData = $companies->map(function ($company) {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'address' => $company->address,
            'phone' => $company->phone,
            'latitude' => $company->latitude !== null ? (float) $company->latitude : null,
            'longitude' => $company->longitude !== null ? (float) $company->longitude : null,
        ];
    })->values()->toArray();
@endphp
<script>
    const companyData = @json($companyMapData);
    const mainMap = L.map('mainMap').setView([15.8700, 100.9925], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(mainMap);
    const markers = [];
    companyData.forEach(function (company) {
        if (company.latitude === null || company.longitude === null) return;
        const marker = L.marker([company.latitude, company.longitude]).addTo(mainMap);
        marker.bindPopup('<strong>' + String(company.name).replace(/[&<>"']/g, '') + '</strong><br>' + String(company.address || '').replace(/[&<>"']/g, '') + '<br><a href="/officer/companies/' + company.id + '">รายละเอียด</a>');
        markers.push(marker);
    });
    if (markers.length) mainMap.fitBounds(new L.featureGroup(markers).getBounds().pad(.18));
</script>
@endpush
