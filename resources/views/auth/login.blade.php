<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>เข้าสู่ระบบ | URU Coop-IS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #edf7ef, #f8faf8 58%, #e4f2e7); }
        .single-login-screen { display: grid; min-height: 100vh; place-items: center; padding: 24px; }
        .single-login-card { width: min(100%, 440px); padding: 32px; background: #fff; border: 1px solid #dce8df; border-radius: 12px; box-shadow: 0 18px 50px rgba(20, 83, 45, .12); }
        .single-login-brand { display: grid; justify-items: center; gap: 10px; margin-bottom: 24px; text-align: center; }
        .single-login-mark { display: grid; width: 48px; height: 48px; place-items: center; color: #fff; background: #166534; border-radius: 12px; }
        .single-login-mark svg { width: 23px; height: 23px; }
        .single-login-card h1 { margin: 0; color: #183528; font-size: 22px; font-weight: 600; line-height: 1.45; text-align: center; }
        .single-login-card .subtitle { margin: 8px 0 24px; color: #748677; font-size: 12px; text-align: center; }
        .single-login-card .form-label { color: #3c4e42; }
        .single-login-card .form-control { min-height: 46px; }
        .single-login-card .btn-primary { min-height: 46px; font-size: 14px; }
        @media (max-width: 480px) { .single-login-screen { padding: 16px; } .single-login-card { padding: 26px 20px; } .single-login-card h1 { font-size: 20px; } }
    </style>
</head>
<body>
<main class="single-login-screen">
    <section class="single-login-card" aria-labelledby="loginHeading">
        <div class="single-login-brand"><span class="single-login-mark"><i data-lucide="landmark"></i></span><p class="page-eyebrow mb-0">URU Coop-IS</p></div>
        <h1 id="loginHeading">ระบบสารสนเทศสหกิจศึกษา URU Coop-IS</h1>
        <p class="subtitle">เข้าสู่ระบบเพื่อดำเนินงานสหกิจศึกษา</p>
        @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3"><label for="email" class="form-label">อีเมล</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@uru.ac.th"></div>
            <div class="mb-4"><label for="password" class="form-label">รหัสผ่าน</label><input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="รหัสผ่านของคุณ"></div>
            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </form>
        <div class="text-center mt-3"><a href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a></div>
        <div class="text-center mt-2 small">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></div>
    </section>
</main>
<script src="https://unpkg.com/lucide@latest"></script><script>lucide.createIcons();</script>
</body>
</html>
