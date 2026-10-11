@extends('layouts.app')

@section('title', 'แผนที่สถานที่ฝึกงาน | URU Coop-IS')
@section('breadcrumb', 'แผนที่สถานที่ฝึกงาน')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">การนิเทศสหกิจศึกษา · แผนที่</p><h1>สถานที่ฝึกงาน</h1><p class="page-subtitle">ตำแหน่งสถานประกอบการและนักศึกษาที่ผ่านการอนุมัติ CWIE 01</p></div>
</header>
<div class="map-tracker-toolbar">
    <label class="quick-search map-search"><i data-lucide="search"></i><input id="locationFilter" type="search" placeholder="ค้นหาสถานประกอบการ จังหวัด หรือนักศึกษา" aria-label="ค้นหาสถานประกอบการบนแผนที่"></label>
    <span id="mapDataStatus" class="map-data-status" aria-live="polite">กำลังโหลดข้อมูลตำแหน่ง...</span>
</div>
<div class="internship-map-layout">
    <section class="card map-panel" aria-label="แผนที่สถานที่ฝึกงาน">
        <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2"><h2 class="h6 mb-0">แผนที่รวม</h2><span class="small text-muted">OpenStreetMap</span></div>
        <div class="map-frame"><div id="internshipMap" role="application" aria-label="แผนที่สถานประกอบการ"></div></div>
        <div class="map-legend" aria-label="คำอธิบายสีหมุด">
            <span><i class="legend-dot legend-dot--low"></i>0–1 คน</span>
            <span><i class="legend-dot legend-dot--medium"></i>2–4 คน</span>
            <span><i class="legend-dot legend-dot--high"></i>5 คนขึ้นไป</span>
        </div>
    </section>
    <section class="card map-list-panel" aria-label="สถานประกอบการตามตำแหน่ง">
        <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2"><div><h2 class="h6 mb-0">สถานประกอบการ</h2><span id="companyCount" class="small text-muted">0 แห่ง</span></div><i data-lucide="map-pinned" class="map-list-heading-icon"></i></div>
        <div id="locationList" class="location-list"><div class="empty-state"><strong>กำลังโหลดตำแหน่ง</strong></div></div>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<style>
    .map-tracker-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:-4px 0 14px}
    .map-search{width:min(100%,520px);background:#fff}
    .map-data-status{color:var(--slate-500);font-size:11px}
    .internship-map-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.85fr);align-items:start;gap:14px}
    .map-panel,.map-list-panel{min-width:0;overflow:hidden}
    .map-frame{height:min(68vh,720px);min-height:440px;padding:9px}
    #internshipMap{width:100%;height:100%;border-radius:10px;background:#eef3ee}
    .map-legend{display:flex;flex-wrap:wrap;gap:14px;padding:0 16px 14px;color:var(--slate-600);font-size:10px}
    .map-legend span{display:inline-flex;align-items:center;gap:6px}
    .legend-dot{width:9px;height:9px;border-radius:50%}
    .legend-dot--low{background:#15803d}.legend-dot--medium{background:#ca8a04}.legend-dot--high{background:#dc2626}
    .map-list-panel{max-height:min(68vh,720px)}
    .map-list-heading-icon{width:17px;height:17px;color:var(--green-700)}
    .location-list{max-height:calc(min(68vh,720px) - 57px);overflow:auto}
    .location-list-item{display:grid;width:100%;grid-template-columns:38px minmax(0,1fr) auto;align-items:start;gap:10px;padding:13px 14px;color:var(--slate-800);background:#fff;border:0;border-bottom:1px solid var(--slate-100);text-align:left}
    .location-list-item:hover,.location-list-item:focus-visible{background:var(--green-50)}
    .location-list-mark{display:grid;width:34px;height:34px;place-items:center;color:var(--green-800);background:var(--green-50);border:1px solid #dcfce7;border-radius:10px}
    .location-list-mark svg{width:17px;height:17px}
    .location-list-copy{display:grid;min-width:0;gap:3px}
    .location-list-copy strong{color:var(--slate-950);font-size:12px;line-height:1.4}
    .location-list-copy small{color:var(--slate-500);font-size:10px;line-height:1.45}
    .student-count{display:inline-grid;min-width:25px;height:25px;padding:0 6px;place-items:center;color:#166534;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:999px;font-size:10px;font-weight:600}
    .student-count--medium{color:#92400e;background:#fffbeb;border-color:#fde68a}.student-count--high{color:#991b1b;background:#fef2f2;border-color:#fecaca}
    .location-marker{background:transparent;border:0}
    .location-dot{display:grid;width:28px;height:28px;place-items:center;color:#fff;background:#15803d;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(19,37,26,.35);font-size:10px;font-weight:700}
    .location-marker--medium .location-dot{background:#ca8a04}.location-marker--high .location-dot{background:#dc2626}
    .map-cluster{display:grid;place-items:center;background:rgba(21,128,61,.18);border-radius:50%}
    .map-cluster span{display:grid;width:30px;height:30px;place-items:center;color:#fff;background:#15803d;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 5px rgba(19,37,26,.28);font-size:10px;font-weight:700}
    .map-cluster--medium{background:rgba(202,138,4,.18)}.map-cluster--medium span{background:#ca8a04}
    .map-cluster--high{background:rgba(220,38,38,.18)}.map-cluster--high span{background:#dc2626}
    .map-popup{min-width:205px;max-width:270px;font-family:'Prompt',sans-serif;color:#26372c}
    .map-popup strong{display:block;margin-bottom:4px;color:#13251a;font-size:13px}
    .map-popup-address{margin:0 0 8px;color:#586b5d;font-size:10px;line-height:1.5}
    .map-popup-students{margin:5px 0 9px;padding-left:17px;font-size:10px;line-height:1.6}
    .map-popup-link{display:inline-block;padding:6px 9px;color:#fff;background:#15803d;border-radius:6px;font-size:10px;text-decoration:none}
    .map-popup-link:hover{color:#fff;background:#166534}
    @media(max-width:1050px){.internship-map-layout{grid-template-columns:1fr}.map-list-panel{max-height:none}.location-list{max-height:360px}}
    @media(max-width:640px){.map-tracker-toolbar{align-items:stretch;flex-direction:column}.map-search{width:100%}.map-frame{height:55vh;min-height:330px;padding:7px}.location-list-item{padding:12px}.map-legend{gap:9px;padding-left:11px}}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    (function () {
        const map = L.map('internshipMap').setView([15.8700, 100.9925], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const clusterGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 48,
            iconCreateFunction: function (cluster) {
                const count = cluster.getChildCount();
                const level = count >= 10 ? 'high' : (count >= 5 ? 'medium' : 'low');
                return L.divIcon({ className: 'map-cluster map-cluster--' + level, html: '<span>' + count + '</span>', iconSize: L.point(42, 42) });
            }
        }).addTo(map);
        const locationFilter = document.getElementById('locationFilter');
        const locationList = document.getElementById('locationList');
        const mapDataStatus = document.getElementById('mapDataStatus');
        const companyCount = document.getElementById('companyCount');
        let locations = [];
        const markersById = {};

        function markerLevel(count) { return count >= 5 ? 'high' : (count >= 2 ? 'medium' : 'low'); }

        function createPopup(company) {
            const popup = document.createElement('div');
            popup.className = 'map-popup';
            const title = document.createElement('strong');
            title.textContent = company.name;
            popup.appendChild(title);
            const address = document.createElement('p');
            address.className = 'map-popup-address';
            address.textContent = company.address || 'ไม่ระบุที่อยู่';
            popup.appendChild(address);
            const studentHeading = document.createElement('strong');
            studentHeading.textContent = 'นักศึกษาที่ฝึกงาน (' + company.student_count + ')';
            popup.appendChild(studentHeading);
            if (company.students.length) {
                const roster = document.createElement('ul');
                roster.className = 'map-popup-students';
                company.students.forEach(function (student) {
                    const item = document.createElement('li');
                    const label = student.student_id ? student.name + ' (' + student.student_id + ')' : student.name;
                    item.textContent = label + (student.major ? ' · ' + student.major : '');
                    roster.appendChild(item);
                });
                popup.appendChild(roster);
            } else {
                const empty = document.createElement('p');
                empty.className = 'map-popup-address';
                empty.textContent = 'ยังไม่มีนักศึกษาที่อนุมัติ CWIE 01 ณ สถานประกอบการนี้';
                popup.appendChild(empty);
            }
            const directions = document.createElement('a');
            const destination = encodeURIComponent(company.latitude + ',' + company.longitude);
            directions.href = company.directions_url || ('https://www.google.com/maps/dir/?api=1&destination=' + destination);
            directions.target = '_blank';
            directions.rel = 'noopener noreferrer';
            directions.className = 'map-popup-link';
            directions.textContent = 'วางแผนการเดินทาง / ขอเส้นทาง';
            popup.appendChild(directions);
            return popup;
        }

        function renderList() {
            const query = locationFilter.value.trim().toLocaleLowerCase();
            const visible = locations.filter(function (company) {
                const roster = company.students.map(function (student) { return student.name + ' ' + student.student_id + ' ' + student.major; }).join(' ');
                return (company.name + ' ' + company.address + ' ' + roster).toLocaleLowerCase().includes(query);
            });
            clusterGroup.clearLayers();
            companyCount.textContent = visible.length + ' แห่ง';
            locationList.replaceChildren();
            if (!visible.length) {
                const empty = document.createElement('div');
                empty.className = 'empty-state';
                const message = document.createElement('strong');
                message.textContent = locations.length ? 'ไม่พบสถานประกอบการที่ค้นหา' : 'ยังไม่มีสถานประกอบการที่บันทึกพิกัด';
                empty.appendChild(message);
                locationList.appendChild(empty);
                return;
            }
            visible.forEach(function (company) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'location-list-item';
                const icon = document.createElement('span');
                icon.className = 'location-list-mark';
                icon.innerHTML = '<i data-lucide="building-2"></i>';
                const copy = document.createElement('span');
                copy.className = 'location-list-copy';
                const name = document.createElement('strong');
                name.textContent = company.name;
                const address = document.createElement('small');
                address.textContent = company.address || 'ไม่ระบุที่อยู่';
                const roster = document.createElement('small');
                roster.textContent = company.student_count + ' คนที่ผ่านอนุมัติ CWIE 01';
                copy.append(name, address, roster);
                const count = document.createElement('span');
                count.className = 'student-count student-count--' + markerLevel(company.student_count);
                count.textContent = company.student_count;
                button.append(icon, copy, count);
                button.addEventListener('click', function () {
                    map.flyTo([company.latitude, company.longitude], 15, { duration: .5 });
                    markersById[company.id].openPopup();
                });
                locationList.appendChild(button);
                clusterGroup.addLayer(markersById[company.id]);
            });
            if (window.lucide) lucide.createIcons();
        }

        fetch('{{ route('api.companies.locations') }}', { headers: { 'Accept': 'application/json' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Unable to load company locations');
                return response.json();
            })
            .then(function (data) {
                locations = data;
                locations.forEach(function (company) {
                    const level = markerLevel(company.student_count);
                    const icon = L.divIcon({
                        className: 'location-marker location-marker--' + level,
                        html: '<span class="location-dot">' + company.student_count + '</span>',
                        iconSize: [28, 32],
                        iconAnchor: [14, 30],
                        popupAnchor: [0, -28]
                    });
                    const marker = L.marker([company.latitude, company.longitude], { icon: icon });
                    marker.bindPopup(createPopup(company));
                    markersById[company.id] = marker;
                    clusterGroup.addLayer(marker);
                });
                mapDataStatus.textContent = locations.length + ' แห่ง · แสดงนักศึกษาจาก CWIE 01 ที่อนุมัติแล้ว';
                renderList();
                if (locations.length === 1) map.setView([locations[0].latitude, locations[0].longitude], 13);
                if (locations.length > 1) map.fitBounds(clusterGroup.getBounds().pad(.15));
            })
            .catch(function () {
                mapDataStatus.textContent = 'โหลดข้อมูลแผนที่ไม่สำเร็จ';
                locationList.innerHTML = '<div class="empty-state"><strong>ไม่สามารถโหลดข้อมูลตำแหน่งได้</strong><span>โปรดลองรีเฟรชหน้าอีกครั้ง</span></div>';
            });

        locationFilter.addEventListener('input', renderList);
    })();
</script>
@endpush
