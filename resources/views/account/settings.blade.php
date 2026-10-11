@extends('layouts.app')

@section('title', 'การตั้งค่าบัญชี | URU Coop-IS')
@section('breadcrumb', 'การตั้งค่าบัญชี')

@section('content')
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<header class="page-heading"><div><p class="page-eyebrow">บัญชีและการแจ้งเตือน</p><h1>การตั้งค่าบัญชี</h1><p class="page-subtitle">จัดการช่องทางรับการแจ้งเตือนเอกสาร CWIE</p></div></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="card settings-card"><div class="card-header bg-white"><div class="settings-heading"><span class="settings-icon"><i data-lucide="message-circle"></i></span><div><h2 class="h6 mb-1">เชื่อมต่อ LINE Notifications</h2><p class="small text-muted mb-0">รับการแจ้งเตือนสถานะเอกสารผ่าน LINE Messaging API</p></div></div></div><div class="card-body">
    @if($user->line_user_id)
        <div class="connection-status connected"><span class="connection-dot"></span><div><strong>เชื่อมต่อแล้ว</strong><span>บัญชี LINE นี้จะได้รับการแจ้งเตือนเอกสาร</span></div></div>
        <form method="POST" action="{{ route('account.line.disconnect') }}" onsubmit="return confirm('ยกเลิกการเชื่อมต่อ LINE หรือไม่?')" class="mt-3">@csrf @method('DELETE')<button class="btn btn-outline-danger">ยกเลิกการเชื่อมต่อ</button></form>
    @else
        <div class="connection-status disconnected"><span class="connection-dot"></span><div><strong>ยังไม่ได้เชื่อมต่อ</strong><span>เชื่อมต่อบัญชี LINE เพื่อรับข้อความเมื่อเอกสารมีความเคลื่อนไหว</span></div></div>
        @if(!$lineAddUrl || !config('services.line.channel_access_token') || !config('services.line.channel_secret'))
            <div class="alert alert-warning mt-3 mb-0">ผู้ดูแลระบบยังตั้งค่า LINE Messaging API ไม่ครบ</div>
        @elseif(session('line_link_code'))
            <div class="line-bind-panel mt-4">
                <div class="line-qr"><img src="{{ $qrCodeUrl }}" alt="QR Code เพิ่มเพื่อน LINE Official Account" width="180" height="180"><span>สแกนเพื่อเปิด LINE Official Account</span></div>
                <div class="line-bind-instructions"><p class="page-eyebrow">รหัสเชื่อมต่อ · หมดอายุใน 10 นาที</p><div class="binding-code" id="bindingCode">{{ session('line_link_code') }}</div><button type="button" class="btn btn-outline-primary btn-sm mb-3" id="copyBindingCode"><i data-lucide="copy" class="me-1" style="width:14px;height:14px"></i>คัดลอกรหัส</button><ol><li>เพิ่มหรือเปิด LINE Official Account</li><li>ส่งรหัสนี้เป็นข้อความหา Official Account</li><li>ระบบจะผูกบัญชีโดยอัตโนมัติเมื่อได้รับ webhook</li></ol><a class="btn btn-success" href="{{ $lineAddUrl }}" target="_blank" rel="noopener"><i data-lucide="external-link" class="me-1" style="width:15px;height:15px"></i>เปิด LINE</a></div>
            </div>
        @else
            <form method="POST" action="{{ route('account.line.bind-code') }}" class="mt-3">@csrf<button class="btn btn-primary"><i data-lucide="link" class="me-1" style="width:15px;height:15px"></i>สร้างรหัสเชื่อมต่อ LINE</button></form>
        @endif
    @endif
</div></section>
<section class="card settings-card mt-3"><div class="card-header bg-white"><h2 class="h6 mb-0">ข้อมูลส่วนตัวและสังกัด</h2></div><div class="card-body"><dl class="row detail-list mb-0">
    <dt class="col-md-3">ชื่อ-นามสกุล</dt><dd class="col-md-9">{{ $user->name }}</dd>
    <dt class="col-md-3">อีเมล</dt><dd class="col-md-9">{{ $user->email }}</dd>
    <dt class="col-md-3">โทรศัพท์</dt><dd class="col-md-9">{{ $user->phone ?: '—' }}</dd>
    <dt class="col-md-3">บทบาท</dt><dd class="col-md-9"><span class="role-tag role-{{ $user->role }}">{{ ['student' => 'นักศึกษา', 'advisor' => 'อาจารย์ที่ปรึกษา', 'supervisor' => 'อาจารย์นิเทศ', 'mentor' => 'ผู้นิเทศงาน', 'company' => 'สถานประกอบการ', 'officer' => 'เจ้าหน้าที่', 'executive' => 'ผู้บริหาร', 'admin' => 'ผู้ดูแลระบบ'][$user->role] ?? $user->role }}</span></dd>
    @if($user->role === 'student')
        <dt class="col-md-3">รหัสนักศึกษา</dt><dd class="col-md-9">{{ optional($user->studentProfile)->student_code ?: $user->student_number ?: '—' }}</dd>
        <dt class="col-md-3">หลักสูตร/คณะ</dt><dd class="col-md-9">{{ optional(optional($user->studentProfile)->program)->program_name ?: '—' }} · {{ optional(optional(optional($user->studentProfile)->program)->faculty)->name ?: optional($user->faculty)->name }}</dd>
        <dt class="col-md-3">อาจารย์ที่ปรึกษา</dt><dd class="col-md-9">{{ optional($user->advisor)->name ?: 'ยังไม่กำหนด' }}</dd>
    @elseif(in_array($user->role, ['advisor', 'supervisor', 'officer'], true))
        <dt class="col-md-3">รหัสบุคลากร</dt><dd class="col-md-9">{{ optional($user->staffProfile)->staff_code ?: '—' }}</dd>
        <dt class="col-md-3">คณะ/หลักสูตร</dt><dd class="col-md-9">{{ optional(optional($user->staffProfile)->faculty)->name ?: optional($user->faculty)->name }} · {{ optional(optional($user->staffProfile)->program)->program_name ?: optional($user->department)->name }}</dd>
        <dt class="col-md-3">ตำแหน่ง</dt><dd class="col-md-9">{{ optional($user->staffProfile)->position_name ?: '—' }}</dd>
    @elseif(in_array($user->role, ['company', 'mentor'], true))
        <dt class="col-md-3">สถานประกอบการ</dt><dd class="col-md-9">{{ optional($user->company)->company_name_th ?: optional($user->company)->name ?: optional(optional($user->companyContact)->company)->name ?: '—' }}</dd>
        @if($user->role === 'company')
            <dt class="col-md-3">ที่อยู่</dt><dd class="col-md-9">{{ optional($user->company)->address ?: '—' }}</dd>
            <dt class="col-md-3">พิกัด</dt><dd class="col-md-9">{{ optional($user->company)->latitude && optional($user->company)->longitude ? $user->company->latitude.', '.$user->company->longitude : '—' }}</dd>
        @else
            <dt class="col-md-3">ตำแหน่ง</dt><dd class="col-md-9">{{ optional($user->companyContact)->position_name ?: 'ผู้นิเทศงาน' }}</dd>
        @endif
    @endif
</dl></div></section>
<section class="card settings-card mt-3"><div class="card-header bg-white"><h2 class="h6 mb-0">เปลี่ยนรหัสผ่าน</h2></div><div class="card-body">
    <form method="POST" action="{{ route('account.password.update') }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-md-4"><label class="form-label" for="current_password">รหัสผ่านปัจจุบัน</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
        <div class="col-md-4"><label class="form-label" for="password">รหัสผ่านใหม่</label><input class="form-control" id="password" name="password" type="password" minlength="10" autocomplete="new-password" required><div class="form-text">ต้องมีอย่างน้อย 10 ตัวอักษร</div></div>
        <div class="col-md-4"><label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="10" autocomplete="new-password" required></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกรหัสผ่านใหม่</button></div>
    </form>
</div></section>
@endsection

@push('scripts')
<script>
    const copyButton = document.getElementById('copyBindingCode');
    if (copyButton) copyButton.addEventListener('click', function () { navigator.clipboard.writeText(document.getElementById('bindingCode').textContent.trim()).then(function () { copyButton.textContent = 'คัดลอกแล้ว'; }); });
</script>
@endpush
