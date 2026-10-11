<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ตั้งรหัสผ่านใหม่ | URU Coop-IS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>body{min-height:100vh;background:linear-gradient(135deg,#edf7ef,#f8faf8 58%,#e4f2e7)}.screen{display:grid;min-height:100vh;place-items:center;padding:24px}.card{width:min(100%,440px);padding:32px;border:1px solid #dce8df;box-shadow:0 18px 50px rgba(20,83,45,.12)}@media(max-width:480px){.card{padding:24px}}</style>
</head>
<body><main class="screen"><section class="card"><p class="text-success fw-semibold mb-2">URU Coop-IS</p><h1 class="h4">ตั้งรหัสผ่านใหม่</h1><p class="text-muted">ตั้งรหัสผ่านใหม่อย่างน้อย 10 ตัวอักษร</p>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3"><label class="form-label" for="email">อีเมล</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required></div>
        <div class="mb-3"><label class="form-label" for="password">รหัสผ่านใหม่</label><input class="form-control" id="password" name="password" type="password" minlength="10" autocomplete="new-password" required></div>
        <div class="mb-3"><label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="10" autocomplete="new-password" required></div>
        <button class="btn btn-primary w-100" type="submit">บันทึกรหัสผ่านใหม่</button>
    </form>
</section></main></body></html>
