<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ข่าวสาร | URU Coop-IS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .public-news-body{min-height:100vh;margin:0;background:#f4f7f4;color:#1f3529;font-family:Prompt,sans-serif}
        .public-news-header{background:#fff;border-bottom:1px solid #dfe9e1}
        .public-news-header-inner{max-width:1120px;min-height:76px;margin:auto;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px}
        .public-news-brand{display:flex;align-items:center;gap:12px;color:#183528;text-decoration:none}
        .public-news-brand strong,.public-news-brand small{display:block}.public-news-brand strong{font-size:15px}.public-news-brand small{margin-top:2px;color:#738579;font-size:11px}
        .public-news-mark{width:40px;height:40px;display:grid;place-items:center;border-radius:8px;background:#166534;color:#fff}.public-news-mark svg{width:20px;height:20px}
        .public-news-actions{display:flex;align-items:center;flex-wrap:wrap;justify-content:flex-end;gap:10px 15px}.public-news-nav-link{color:#385447;text-decoration:none;font-size:12px}.public-news-nav-link:hover{color:#166534}
        .public-news-user{max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#385447;font-size:12px}
        .public-news-main{max-width:1120px;min-height:calc(100vh - 140px);margin:auto;padding:44px 24px 56px}
        .public-news-footer{padding:18px 24px;text-align:center;color:#75847a;border-top:1px solid #dfe9e1;background:#fff;font-size:12px}.public-news-footer span{padding:0 5px}
        @media(max-width:700px){.public-news-header-inner{padding:12px 16px}.public-news-main{padding:30px 16px 40px}.public-news-actions{gap:12px}}
        @media(max-width:520px){.public-news-header-inner{align-items:flex-start;flex-direction:column;gap:10px}.public-news-brand strong{white-space:nowrap}.public-news-actions{width:100%;justify-content:space-between;gap:8px}.public-news-nav-link{white-space:nowrap}.public-news-actions .btn{white-space:nowrap}}
    </style>
    @stack('styles')
</head>
<body class="public-news-body">
@php
    $publicDashboardRoutes = [
        'student' => 'student.dashboard',
        'advisor' => 'staff.dashboard',
        'officer' => 'staff.dashboard',
        'supervisor' => 'supervisor.dashboard',
        'mentor' => 'mentor.dashboard',
        'executive' => 'executive.dashboard',
        'company' => 'company.dashboard',
        'admin' => 'admin.dashboard',
    ];
    $publicDashboardRoute = auth()->check() ? ($publicDashboardRoutes[auth()->user()->role] ?? null) : null;
@endphp
<header class="public-news-header">
    <div class="public-news-header-inner">
        <a class="public-news-brand" href="{{ route('home') }}">
            <span class="public-news-mark"><i data-lucide="landmark"></i></span>
            <span><strong>URU Coop-IS</strong><small>มหาวิทยาลัยราชภัฏอุตรดิตถ์</small></span>
        </a>
        <nav class="public-news-actions" aria-label="เมนูเว็บไซต์">
            <a href="{{ route('home') }}" class="public-news-nav-link">หน้าแรก</a>
            <a href="{{ route('public.about') }}" class="public-news-nav-link">ข้อมูลสหกิจ</a>
            <a href="{{ route('public.guide') }}" class="public-news-nav-link">ขั้นตอนสหกิจ</a>
            <a href="{{ route('public.forms') }}" class="public-news-nav-link">คู่มือ/แบบฟอร์ม</a>
            <a href="{{ route('news.index') }}" class="public-news-nav-link">ข่าวสาร</a>
            <a href="{{ route('public.selected-students') }}" class="public-news-nav-link">รายชื่อผู้ผ่านคัดเลือก</a>
            <a href="{{ route('public.activities') }}" class="public-news-nav-link">กิจกรรม</a>
            <a href="{{ route('public.board') }}" class="public-news-nav-link">กระดานข่าว</a>
            @if($publicDashboardRoute)
                <a href="{{ route($publicDashboardRoute) }}" class="btn btn-primary btn-sm"><i data-lucide="layout-dashboard" class="me-1" style="width:15px;height:15px"></i>กลับสู่ระบบ</a>
            @elseif(auth()->guest())
                <span class="d-flex gap-2"><a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">เข้าสู่ระบบ</a><a href="{{ route('register') }}" class="btn btn-primary btn-sm">สมัครสมาชิก</a></span>
            @else
                <span class="public-news-user" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
            @endif
        </nav>
    </div>
</header>
<main class="public-news-main">
    @yield('content')
</main>
<footer class="public-news-footer">มหาวิทยาลัยราชภัฏอุตรดิตถ์ <span>·</span> URU Coop-IS</footer>
<script src="https://unpkg.com/lucide@latest"></script><script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>