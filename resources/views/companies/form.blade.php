@extends('layouts.app')

@section('title', (isset($company) ? 'แก้ไขสถานประกอบการ' : 'เพิ่มสถานประกอบการ').' | URU Coop-IS')
@section('breadcrumb', isset($company) ? 'แก้ไขสถานประกอบการ' : 'เพิ่มสถานประกอบการ')

@section('content')
@php($isEditing = isset($company))
<header class="page-heading"><div><p class="page-eyebrow">ข้อมูลสถานประกอบการ</p><h1>{{ $isEditing ? 'แก้ไขสถานประกอบการ' : 'เพิ่มสถานประกอบการ' }}</h1><p class="page-subtitle">ระบุรายละเอียดและตำแหน่งสถานที่ปฏิบัติงาน</p></div><a href="{{ route('officer.companies.index') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="me-1" style="width:15px;height:15px"></i>กลับรายการ</a></header>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $isEditing ? route('officer.companies.update', $company) : route('officer.companies.store') }}">
    @csrf @if($isEditing)@method('PUT')@endif
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">ข้อมูลทั่วไป</h2><div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name">ชื่อสถานประกอบการ</label><input id="name" name="name" class="form-control" value="{{ old('name', $company->name ?? '') }}" required></div>
        <div class="col-md-6"><label class="form-label" for="status">สถานะ</label><select id="status" name="status" class="form-select"><option value="active" {{ old('status', $company->status ?? 'active') === 'active' ? 'selected' : '' }}>active</option><option value="pending" {{ old('status', $company->status ?? '') === 'pending' ? 'selected' : '' }}>pending</option></select></div>
        <div class="col-12"><label class="form-label" for="address">ที่อยู่</label><textarea id="address" name="address" rows="2" class="form-control">{{ old('address', $company->address ?? '') }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="subdistrict">ตำบล/แขวง</label><input id="subdistrict" name="subdistrict" class="form-control" value="{{ old('subdistrict', $company->subdistrict ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label" for="district">อำเภอ/เขต</label><input id="district" name="district" class="form-control" value="{{ old('district', $company->district ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label" for="province">จังหวัด</label><input id="province" name="province" class="form-control" value="{{ old('province', $company->province ?? '') }}"></div>
        <div class="col-md-6"><label class="form-label" for="zipcode">รหัสไปรษณีย์</label><input id="zipcode" name="zipcode" class="form-control" value="{{ old('zipcode', $company->zipcode ?? '') }}"></div>
        <div class="col-md-4"><label class="form-label" for="phone">เบอร์โทร</label><input id="phone" name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}"></div>
        <div class="col-md-4"><label class="form-label" for="email">อีเมล</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}"></div>
        <div class="col-md-4"><label class="form-label" for="contact_person">ผู้ติดต่อ</label><input id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person', $company->contact_person ?? '') }}"></div>
        <div class="col-12"><label class="form-label" for="google_maps_url">ลิงก์ Google Maps</label><input id="google_maps_url" type="url" name="google_maps_url" class="form-control" value="{{ old('google_maps_url', $company->google_maps_url ?? '') }}" placeholder="สร้างให้อัตโนมัติเมื่อเลือกตำแหน่ง"></div>
    </div></div></section>
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">ตำแหน่งบนแผนที่</h2><div class="d-flex flex-wrap gap-2 mb-2"><input type="search" id="locationSearch" class="form-control flex-grow-1" placeholder="ค้นหาชื่อสถานที่หรือที่อยู่"><button type="button" id="searchLocationBtn" class="btn btn-outline-primary"><i data-lucide="search" class="me-1" style="width:15px;height:15px"></i>ค้นหา</button></div><p id="locationSearchStatus" class="small text-muted mb-2" aria-live="polite">เลือกผลการค้นหา หรือคลิก/ลากหมุดบนแผนที่</p><div id="locationResults" class="location-results mb-3" role="listbox" aria-label="ผลการค้นหาสถานที่"></div><div id="miniMap" class="company-edit-map mb-2" aria-label="แผนที่เลือกตำแหน่งสถานประกอบการ"></div><p id="selectedCoordinates" class="small text-muted mb-2">ยังไม่ได้เลือกพิกัด</p><input id="latitude" type="hidden" name="latitude" value="{{ old('latitude', $company->latitude ?? '') }}"><input id="longitude" type="hidden" name="longitude" value="{{ old('longitude', $company->longitude ?? '') }}"></div></section>
    <div class="form-actions"><a href="{{ route('officer.companies.index') }}" class="btn btn-outline-secondary">ยกเลิก</a><button type="submit" class="btn btn-primary"><i data-lucide="save" class="me-1" style="width:16px;height:16px"></i>{{ $isEditing ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}</button></div>
</form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>.company-edit-map{height:clamp(270px,42vh,460px);border:1px solid var(--slate-200);border-radius:12px;z-index:0}.location-results{display:grid;gap:6px}.location-result{padding:9px 12px;color:var(--slate-700);background:#fff;border:1px solid var(--slate-200);border-radius:8px;text-align:left;font-size:12px}.location-result:hover,.location-result:focus{color:var(--forest-800);background:var(--green-50);border-color:#86efac}</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const latitude = Number(latInput.value);
    const longitude = Number(lngInput.value);
    const hasCoordinates = Number.isFinite(latitude) && Number.isFinite(longitude) && (latInput.value !== '' && lngInput.value !== '');
    const startPoint = hasCoordinates ? [latitude, longitude] : [15.8700, 100.9925];
    const miniMap = L.map('miniMap').setView(startPoint, hasCoordinates ? 13 : 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(miniMap);
    let marker = null;
    const status = document.getElementById('locationSearchStatus');
    const resultsContainer = document.getElementById('locationResults');
    const coordinateLabel = document.getElementById('selectedCoordinates');
    const markerIcon = L.divIcon({ className: 'location-marker', html: '<span></span>', iconSize: [24, 32], iconAnchor: [12, 30] });

    function updateAddress(result) {
        const address = result.address || {};
        const street = [address.house_number, address.road || address.pedestrian || address.footway].filter(Boolean).join(' ');
        document.getElementById('address').value = street || result.display_name || document.getElementById('address').value;
        document.getElementById('subdistrict').value = address.suburb || address.neighbourhood || address.village || address.town || '';
        document.getElementById('district').value = address.city_district || address.district || address.county || '';
        document.getElementById('province').value = address.state || address.province || '';
        document.getElementById('zipcode').value = address.postcode || '';
    }

    function updateCoordinates(lat, lng, result, updateMapsUrl) {
        latInput.value = Number(lat).toFixed(8);
        lngInput.value = Number(lng).toFixed(8);
        coordinateLabel.textContent = 'พิกัดที่เลือก: ' + latInput.value + ', ' + lngInput.value;
        if (updateMapsUrl !== false) document.getElementById('google_maps_url').value = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(latInput.value + ',' + lngInput.value);
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true, icon: markerIcon }).addTo(miniMap);
            marker.on('dragend', function (event) { const point = event.target.getLatLng(); updateCoordinates(point.lat, point.lng); });
        } else {
            marker.setLatLng([lat, lng]);
        }
        if (result) updateAddress(result);
    }

    if (hasCoordinates) updateCoordinates(latitude, longitude, null, false);
    miniMap.on('click', function (event) { updateCoordinates(event.latlng.lat, event.latlng.lng); });

    async function searchLocation() {
        const query = document.getElementById('locationSearch').value.trim();
        if (!query) return;
        status.textContent = 'กำลังค้นหาสถานที่...';
        resultsContainer.replaceChildren();
        try {
            const url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=5&q=' + encodeURIComponent(query);
            const response = await fetch(url, { headers: { 'Accept-Language': 'th,en' } });
            if (!response.ok) throw new Error('Geocoding request failed');
            const results = await response.json();
            if (!results.length) { status.textContent = 'ไม่พบสถานที่ ลองค้นหาด้วยชื่อถนนหรือจังหวัด'; return; }
            status.textContent = 'เลือกผลลัพธ์เพื่อปักหมุด';
            results.forEach(function (result) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'location-result';
                button.setAttribute('role', 'option');
                button.textContent = result.display_name;
                button.addEventListener('click', function () {
                    const lat = Number(result.lat);
                    const lng = Number(result.lon);
                    miniMap.setView([lat, lng], 16);
                    updateCoordinates(lat, lng, result);
                    status.textContent = 'ปักหมุดแล้ว สามารถลากหมุดเพื่อปรับตำแหน่งได้';
                    resultsContainer.replaceChildren();
                });
                resultsContainer.appendChild(button);
            });
        } catch (error) {
            status.textContent = 'ค้นหาสถานที่ไม่สำเร็จ กรุณาลองอีกครั้ง';
        }
    }

    document.getElementById('searchLocationBtn').addEventListener('click', searchLocation);
    document.getElementById('locationSearch').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') { event.preventDefault(); searchLocation(); }
    });
</script>
@endpush
