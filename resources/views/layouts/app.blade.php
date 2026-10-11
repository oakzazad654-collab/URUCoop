<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'URU Coop-IS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="app-body">
@php
    $user = auth()->user();
    $role = $user->role;
    $logoutRoute = 'logout';
    $unreadNotificationCount = $user->unreadNotifications()->count();
    $recentNotifications = $user->notifications()->latest()->limit(5)->get();
    $roleLabels = ['student' => 'นักศึกษา', 'advisor' => 'อาจารย์ที่ปรึกษา', 'officer' => 'เจ้าหน้าที่ศูนย์', 'supervisor' => 'อาจารย์นิเทศ', 'mentor' => 'ผู้นิเทศงาน', 'executive' => 'ผู้บริหาร', 'company' => 'สถานประกอบการ', 'admin' => 'ผู้ดูแลระบบ'];
    $dashboardRoutes = ['student' => 'student.dashboard', 'advisor' => 'staff.dashboard', 'officer' => 'staff.dashboard', 'supervisor' => 'supervisor.dashboard', 'mentor' => 'mentor.dashboard', 'executive' => 'executive.dashboard', 'company' => 'company.dashboard', 'admin' => 'admin.dashboard'];
    $menuItems = [
        'student' => [
            ['label' => 'ภาพรวม', 'route' => 'student.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'แผนที่สถานประกอบการ', 'route' => 'student.map', 'icon' => 'map-pinned'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'สถานะเอกสาร', 'route' => 'student.approvals.dashboard', 'icon' => 'clipboard-check'],
            ['label' => 'แบบฟอร์ม สก.', 'route' => 'student.cwie.index', 'icon' => 'files'],
            ['label' => 'รายงานส่วนตัว', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
        ],
        'advisor' => [
            ['label' => 'ภาพรวม', 'route' => 'staff.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'คิวพิจารณา', 'route' => 'advisor.approvals.dashboard', 'icon' => 'clipboard-check'],
            ['label' => 'แผนที่ฝึกงาน', 'route' => 'staff.map', 'icon' => 'map-pinned'],
            ['label' => 'นักศึกษาในดูแล', 'route' => 'advisor.students', 'icon' => 'users-round'],
            ['label' => 'สถานประกอบการในสาขา', 'route' => 'advisor.companies', 'icon' => 'building-2'],
            ['label' => 'คำขอนิเทศ CWIE 06', 'route' => 'advisor.cwie06.create', 'icon' => 'file-plus-2'],
            ['label' => 'รายงานและสถิติ', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
        ],
        'officer' => [
            ['label' => 'ภาพรวม', 'route' => 'staff.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'คิวอนุมัติ', 'route' => 'officer.approvals.dashboard', 'icon' => 'clipboard-check'],
            ['label' => 'แผนที่ฝึกงาน', 'route' => 'staff.map', 'icon' => 'map-pinned'],
            ['label' => 'เอกสารทั้งหมด', 'route' => 'officer.documents', 'icon' => 'files'],
            ['label' => 'สถานประกอบการ', 'route' => 'officer.companies.index', 'icon' => 'building-2'],
            ['label' => 'ผู้ใช้งาน', 'route' => 'officer.users.index', 'icon' => 'users-round'],
            ['label' => 'จับคู่อาจารย์', 'route' => 'officer.users.assign-advisor', 'icon' => 'user-round-cog'],
            ['label' => 'รายงานและสถิติ', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
        ],
        'supervisor' => [
            ['label' => 'ภาพรวม', 'route' => 'supervisor.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'แผนที่สถานที่ฝึกงาน', 'route' => 'supervisor.map', 'icon' => 'map-pinned'],
            ['label' => 'สถานประกอบการในสาขา', 'route' => 'supervisor.companies', 'icon' => 'building-2'],
            ['label' => 'รายงานและสถิติ', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
            ['label' => 'หน้าแรกเว็บไซต์', 'route' => 'home', 'icon' => 'globe-2'],
        ],
        'mentor' => [
            ['label' => 'ภาพรวม', 'route' => 'mentor.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'Workflow และคิวอนุมัติ', 'route' => 'workflow.index', 'icon' => 'clipboard-check'],
            ['label' => 'หน้าแรกเว็บไซต์', 'route' => 'home', 'icon' => 'globe-2'],
        ],
        'executive' => [
            ['label' => 'ภาพรวม', 'route' => 'executive.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'ทำเนียบสหกิจศึกษา', 'route' => 'executive.directory', 'icon' => 'contact-round'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'รายงานและสถิติ', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
            ['label' => 'หน้าแรกเว็บไซต์', 'route' => 'home', 'icon' => 'globe-2'],
        ],
        'company' => [
            ['label' => 'ภาพรวมสถานประกอบการ', 'route' => 'company.dashboard', 'icon' => 'building-2'],
            ['label' => 'หน้าแรกเว็บไซต์', 'route' => 'home', 'icon' => 'globe-2'],
        ],
        'admin' => [
            ['label' => 'ภาพรวม', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
            ['label' => 'ทำเนียบสหกิจศึกษา', 'route' => 'admin.directory', 'icon' => 'contact-round'],
            ['label' => 'Workflow สหกิจ', 'route' => 'workflow.index', 'icon' => 'route'],
            ['label' => 'ข่าวสาร', 'route' => 'admin.news.index', 'icon' => 'newspaper'],
            ['label' => 'ปรับแต่งหน้าแรก', 'route' => 'admin.appearance.edit', 'icon' => 'image'],
            ['label' => 'ตำแหน่งแบบฟอร์ม สก.', 'route' => 'admin.sk-positions.index', 'icon' => 'move'],
            ['label' => 'คิวอนุมัติ', 'route' => 'admin.approvals.dashboard', 'icon' => 'clipboard-check'],
            ['label' => 'แผนที่ฝึกงาน', 'route' => 'admin.map', 'icon' => 'map-pinned'],
            ['label' => 'ผู้ใช้งานและสิทธิ์', 'route' => 'admin.users.index', 'icon' => 'users-round'],
            ['label' => 'คำขอสมัครบัญชี', 'route' => 'admin.registrations.index', 'icon' => 'user-round-check'],
            ['label' => 'ภาคการศึกษา', 'route' => 'admin.academic-terms.index', 'icon' => 'calendar-days'],
            ['label' => 'จับคู่อาจารย์', 'route' => 'admin.users.assign-advisor', 'icon' => 'user-round-cog'],
            ['label' => 'ประวัติระบบ', 'route' => 'admin.audit-logs', 'icon' => 'history'],
            ['label' => 'รายงานและสถิติ', 'route' => 'reports.index', 'icon' => 'chart-no-axes-combined'],
        ],
    ];
    $routeLabels = [
        'student.dashboard' => 'ภาพรวมนักศึกษา', 'staff.dashboard' => 'ภาพรวมบุคลากร',
        'workflow.index' => 'Workflow สหกิจ',
        'reports.index' => 'รายงานและสถิติ',
        'reports.show' => 'รายงานและสถิติ',
        'supervisor.dashboard' => 'ภาพรวมอาจารย์นิเทศ', 'executive.dashboard' => 'ภาพรวมผู้บริหาร',
        'mentor.dashboard' => 'ภาพรวมผู้นิเทศงาน', 'admin.registrations.index' => 'คำขอสมัครบัญชี',
        'admin.directory' => 'ทำเนียบสหกิจศึกษา', 'executive.directory' => 'ทำเนียบสหกิจศึกษา',
        'advisor.companies' => 'สถานประกอบการในสาขา', 'supervisor.companies' => 'สถานประกอบการในสาขา',
        'student.map' => 'แผนที่สถานประกอบการ', 'supervisor.map' => 'แผนที่สถานที่ฝึกงาน',
        'company.dashboard' => 'ภาพรวมสถานประกอบการ',
        'admin.news.index' => 'จัดการข่าวสาร', 'admin.news.create' => 'เพิ่มข่าว',
        'admin.news.edit' => 'แก้ไขข่าว',
        'admin.appearance.edit' => 'ปรับแต่งหน้าแรก',
        'student.approvals.dashboard' => 'สถานะเอกสาร', 'student.cwie.index' => 'แบบฟอร์ม สก.',
        'advisor.approvals.dashboard' => 'คิวพิจารณา', 'officer.approvals.dashboard' => 'คิวอนุมัติ',
        'staff.map' => 'แผนที่สถานที่ฝึกงาน', 'admin.map' => 'แผนที่สถานที่ฝึกงาน',
        'admin.dashboard' => 'ภาพรวมระบบ', 'admin.approvals.dashboard' => 'คิวอนุมัติ', 'advisor.students' => 'นักศึกษาในดูแล',
        'advisor.cwie06.create' => 'คำขอนิเทศ CWIE 06', 'officer.documents' => 'เอกสารทั้งหมด',
        'officer.companies.index' => 'สถานประกอบการ', 'officer.users.index' => 'ผู้ใช้งาน',
        'admin.users.index' => 'ผู้ใช้งานและสิทธิ์', 'officer.users.assign-advisor' => 'จับคู่อาจารย์',
        'admin.users.assign-advisor' => 'จับคู่อาจารย์', 'admin.audit-logs' => 'ประวัติระบบ',
        'admin.academic-terms.index' => 'ภาคการศึกษา',
        'admin.academic-terms.create' => 'เพิ่มภาคการศึกษา',
        'admin.academic-terms.edit' => 'แก้ไขภาคการศึกษา',
    ];
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
    $pageLabel = $routeLabels[$currentRoute] ?? trim(ucwords(str_replace(['.', '-', '_'], ' ', $currentRoute ?: 'URU Coop-IS')));
@endphp
<div class="app-shell" id="appShell">
    <aside class="app-sidebar" id="appSidebar" aria-label="เมนูหลัก">
        <a class="brand-lockup" href="{{ route($dashboardRoutes[$role]) }}">
            <span class="brand-mark"><i data-lucide="landmark"></i></span>
            <span class="brand-copy"><strong>URU Coop-IS</strong><small>สหกิจศึกษาและ CWIE</small></span>
        </a>
        <div class="sidebar-caption">เมนูหลัก</div>
        <nav class="sidebar-nav">
            @foreach($menuItems[$role] ?? [] as $item)
                @php($active = $currentRoute === $item['route'] || strpos($currentRoute ?? '', $item['route'].'.') === 0 || ($item['route'] === 'reports.index' && strpos($currentRoute ?? '', 'reports.') === 0))
                <a class="sidebar-link {{ $active ? 'active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}">
                    <i data-lucide="{{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-user"><span class="avatar">{{ mb_substr($user->name, 0, 1) }}</span><span class="sidebar-user-copy"><strong>{{ $user->name }}</strong><small>{{ $roleLabels[$role] ?? $role }}</small></span></div>
            <form method="POST" action="{{ route($logoutRoute) }}">@csrf<button class="sidebar-link logout-link" type="submit" title="ออกจากระบบ"><i data-lucide="log-out"></i><span>ออกจากระบบ</span></button></form>
        </div>
    </aside>
    <button class="sidebar-scrim" id="sidebarScrim" aria-label="ปิดเมนู"></button>
    <div class="app-main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-button sidebar-toggle" id="sidebarToggle" type="button" aria-label="เปิดหรือย่อเมนู" title="เมนู"><i data-lucide="panel-left"></i></button>
                <div class="breadcrumb-line"><span>URU Coop-IS</span><i data-lucide="chevron-right"></i><strong>@yield('breadcrumb', $pageLabel)</strong></div>
            </div>
            <div class="topbar-actions">
                <label class="quick-search"><i data-lucide="search"></i><input id="quickSearch" type="search" placeholder="ค้นหาในหน้านี้" aria-label="ค้นหาในหน้านี้"><kbd>⌘ K</kbd></label>
                <div class="dropdown">
                    <button class="icon-button notification-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="การแจ้งเตือน {{ $unreadNotificationCount }} รายการที่ยังไม่อ่าน" title="การแจ้งเตือน"><i data-lucide="bell"></i>@if($unreadNotificationCount)<span class="notification-count">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>@endif</button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu">
                        <div class="dropdown-heading"><span>การแจ้งเตือน</span>@if($unreadNotificationCount)<form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm">อ่านทั้งหมด</button></form>@endif</div>
                        <div class="notification-dropdown-list">
                            @forelse($recentNotifications as $notification)
                                @php($notificationData = $notification->data)
                                <div class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}"><a href="{{ $notificationData['url'] ?? route($dashboardRoutes[$role]) }}"><strong>{{ $notificationData['title'] ?? 'แจ้งเตือนเอกสาร' }}</strong><span>{{ $notificationData['message'] ?? '' }}</span><small>{{ $notification->created_at->diffForHumans() }}</small></a>@if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf<button title="ทำเครื่องหมายว่าอ่านแล้ว" aria-label="ทำเครื่องหมายว่าอ่านแล้ว"><i data-lucide="check"></i></button></form>@endif</div>
                            @empty<div class="notification-empty">ยังไม่มีการแจ้งเตือน</div>@endforelse
                        </div>
                        <a class="notification-all-link" href="{{ route('notifications.index') }}">ดูการแจ้งเตือนทั้งหมด</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="profile-button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-small">{{ mb_substr($user->name, 0, 1) }}</span><span class="profile-copy"><strong>{{ $user->name }}</strong><small>{{ $roleLabels[$role] ?? $role }}</small></span><i data-lucide="chevron-down"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end profile-menu"><div class="profile-menu-header"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small><span class="role-pill">{{ $roleLabels[$role] ?? $role }}</span></div><a class="dropdown-item" href="{{ route('account.settings') }}"><i data-lucide="settings-2"></i>การตั้งค่าการแจ้งเตือน</a><form method="POST" action="{{ route($logoutRoute) }}">@csrf<button class="dropdown-item text-danger" type="submit"><i data-lucide="log-out"></i>ออกจากระบบ</button></form></div>
                </div>
            </div>
        </header>
        <main class="main-content">
            @yield('content')
        </main>
        <footer class="app-footer">มหาวิทยาลัยราชภัฏอุตรดิตถ์ <span>·</span> URU Coop-IS</footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
    (function () {
        const shell = document.getElementById('appShell');
        const toggle = document.getElementById('sidebarToggle');
        const scrim = document.getElementById('sidebarScrim');
        const search = document.getElementById('quickSearch');
        const mobile = window.matchMedia('(max-width: 900px)');
        function setSidebar(open) { shell.classList.toggle('sidebar-open', open); }
        toggle.addEventListener('click', function () {
            if (mobile.matches) setSidebar(!shell.classList.contains('sidebar-open'));
            else shell.classList.toggle('sidebar-collapsed');
        });
        scrim.addEventListener('click', function () { setSidebar(false); });
        document.addEventListener('keydown', function (event) {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); search.focus(); }
            if (event.key === 'Escape') setSidebar(false);
        });
        search.addEventListener('input', function () {
            const query = search.value.trim().toLocaleLowerCase();
            document.querySelectorAll('[data-search-row], .table tbody tr, .searchable-item').forEach(function (item) {
                const searchable = (item.dataset.search || item.textContent).toLocaleLowerCase();
                item.hidden = !!query && !searchable.includes(query);
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
