@php
    $role = auth()->user()->role;
    $dashboardRoutes = [
        'student' => 'student.approvals.dashboard',
        'advisor' => 'advisor.approvals.dashboard',
        'officer' => 'officer.approvals.dashboard',
        'admin' => 'admin.approvals.dashboard',
    ];
    $logoutRoute = 'logout';
@endphp
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand fw-semibold" href="{{ route($dashboardRoutes[$role]) }}">URU Coop-IS</a>
        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a class="btn btn-sm btn-outline-primary" href="{{ route($dashboardRoutes[$role]) }}">{{ $role === 'student' ? 'สถานะเอกสาร' : 'รายการอนุมัติ' }}</a>
            @if($role === 'student')<a class="btn btn-sm btn-outline-secondary" href="{{ route('student.cwie.index') }}">แบบฟอร์ม สก.</a>@endif
            @if($role === 'advisor')
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('advisor.students') }}">นักศึกษาในดูแล</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('advisor.cwie06.create') }}">สร้าง CWIE 06</a>
            @endif
            @if($role === 'officer')
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('officer.companies.index') }}">สถานประกอบการ</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('officer.documents') }}">เอกสารทั้งหมด</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('officer.users.index') }}">ผู้ใช้งาน</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('officer.users.assign-advisor') }}">จับคู่อาจารย์</a>
            @endif
            @if($role === 'admin')
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.index') }}">ผู้ใช้งาน</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.assign-advisor') }}">จับคู่อาจารย์</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.audit-logs') }}">ประวัติระบบ</a>
            @endif
            <form method="POST" action="{{ route($logoutRoute) }}">@csrf<button class="btn btn-sm btn-outline-dark">ออกจากระบบ</button></form>
        </div>
    </div>
</nav>