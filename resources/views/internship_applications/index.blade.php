<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สหกิจศึกษา | รายการใบสมัคร</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">รายการใบสมัครงานสหกิจศึกษา (สก.03)</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('student.cwie.index') }}" class="btn btn-outline-primary">แบบฟอร์ม สก.</a>
            <a href="{{ route('internship-applications.create') }}" class="btn btn-primary">+ เพิ่มใบสมัคร</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>ชื่อ-สกุล</th>
            <th>รหัสนักศึกษา</th>
            <th>คณะ/สาขา</th>
            <th>สถานประกอบการ</th>
            <th>จัดการ</th>
        </tr>
        </thead>
        <tbody>
        @forelse($applications as $application)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $application->student_title_th }} {{ $application->student_first_name_th }} {{ $application->student_last_name_th }}</td>
                <td>{{ $application->student_id }}</td>
                <td>{{ $application->faculty }} / {{ $application->major }}</td>
                <td>{{ $application->company_name }}</td>
                <td>
                    <a href="{{ route('internship-applications.show', $application) }}" class="btn btn-sm btn-info">ดู</a>
                    <a href="{{ route('internship-applications.edit', $application) }}" class="btn btn-sm btn-warning">แก้ไข</a>
                    <a href="{{ route('internship-applications.pdf', $application) }}" class="btn btn-sm btn-success" target="_blank">PDF</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">ยังไม่มีข้อมูลใบสมัคร</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
