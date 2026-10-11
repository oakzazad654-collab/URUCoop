<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $payload['title'] }}</title></head>
<body style="margin:0;padding:24px;background:#f8fafc;color:#1e293b;font-family:Arial,sans-serif">
<table role="presentation" style="width:100%;max-width:600px;margin:0 auto;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:12px"><tr><td style="padding:28px">
    <p style="margin:0 0 8px;color:#1d4ed8;font-size:12px;font-weight:bold">URU Coop-IS · CWIE</p>
    <h1 style="margin:0 0 16px;color:#0f172a;font-size:20px">{{ $payload['title'] }}</h1>
    <p style="font-size:14px;line-height:1.7">{{ $payload['message'] }}</p>
    <p style="font-size:13px;line-height:1.7"><strong>แบบฟอร์ม:</strong> {{ $payload['document_type'] }}<br><strong>เอกสาร:</strong> {{ $payload['document_title'] }}<br><strong>นักศึกษา:</strong> {{ $payload['student_name'] ?: '—' }}<br><strong>รหัสนักศึกษา:</strong> {{ $payload['student_id'] ?: '—' }}</p>
    @if($payload['comment'])<div style="padding:12px;background:#f1f5f9;border-radius:8px;font-size:13px"><strong>ความเห็น:</strong> {{ $payload['comment'] }}</div>@endif
    <p style="margin:24px 0"><a href="{{ $payload['url'] }}" style="display:inline-block;padding:11px 18px;background:#1d4ed8;color:#fff;border-radius:8px;text-decoration:none">เปิดเอกสาร</a></p>
    <p style="margin:0;color:#64748b;font-size:11px">ข้อความนี้ส่งโดยระบบ URU Coop-IS</p>
</td></tr></table>
</body>
</html>