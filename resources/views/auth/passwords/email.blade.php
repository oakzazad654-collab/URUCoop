<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ลืมรหัสผ่าน | URU Coop-IS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>body{min-height:100vh;background:linear-gradient(135deg,#edf7ef,#f8faf8 58%,#e4f2e7)}.screen{display:grid;min-height:100vh;place-items:center;padding:24px}.card{width:min(100%,440px);padding:32px;border:1px solid #dce8df;box-shadow:0 18px 50px rgba(20,83,45,.12)}@media(max-width:480px){.card{padding:24px}}</style>
</head>
<body><main class="screen"><section class="card"><p class="text-success fw-semibold mb-2">URU Coop-IS</p><h1 class="h4">ลืมรหัสผ่าน</h1><p class="text-muted">กรอกอีเมลที่ใช้เข้าสู่ระบบ หากอีเมลลงทะเบียนไว้ เราจะส่งลิงก์ตั้งรหัสผ่านใหม่ให้</p>
    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <div class="mb-3"><label class="form-label" for="email">อีเมล</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
        <button class="btn btn-primary w-100" type="submit">ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
    </form>
    <div class="text-center mt-3"><a href="{{ route('login') }}">กลับไปหน้าเข้าสู่ระบบ</a></div>
</section></main></body></html>
