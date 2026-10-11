<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal | URU Coop-IS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: #f8fafc; color: #0f172a; }
        .topbar { background: linear-gradient(135deg, #1e293b, #334155); color: white; }
        .sidebar { background: #0f172a; color: white; }
        .nav-link { color: rgba(255,255,255,.82); }
        .nav-link.active, .nav-link:hover { color: white; background: rgba(255,255,255,.08); }
    </style>
</head>
<body>
<nav class="topbar navbar navbar-expand-lg px-4 py-3">
    <div class="container-fluid">
        <span class="navbar-brand text-white fw-semibold">URU Coop-IS Staff</span>
        <div class="ms-auto d-flex align-items-center gap-3 text-white">
            <span>{{ auth()->user()->name ?? 'Staff' }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-light">ออกจากระบบ</button></form>
        </div>
    </div>
</nav>
<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <aside class="sidebar col-lg-2 p-4">
            <div class="mb-4 fw-semibold">Staff Space</div>
            <nav class="nav flex-column gap-2">
                <a class="nav-link rounded px-3 py-2 active" href="{{ route('staff.dashboard') }}">Dashboard</a>
                <a class="nav-link rounded px-3 py-2" href="{{ route('officer.approvals.dashboard') }}">คิวตรวจและอนุมัติ</a>
                <a class="nav-link rounded px-3 py-2" href="{{ route('officer.documents') }}">เอกสาร CWIE</a>
                <a class="nav-link rounded px-3 py-2" href="{{ route('officer.companies.index') }}">สถานประกอบการ</a>
            </nav>
        </aside>
        <main class="col-lg-10 p-4">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
