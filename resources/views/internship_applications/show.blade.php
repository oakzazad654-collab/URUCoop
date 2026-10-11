<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสมัครสหกิจศึกษา</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>รายละเอียดใบสมัครสหกิจศึกษา</h2>
        <div>
            <a href="{{ route('internship-applications.pdf', ['internship_application' => $application]) }}" class="btn btn-success" target="_blank">ดาวน์โหลด PDF</a>
            <a href="{{ route('internship-applications.index') }}" class="btn btn-outline-secondary">กลับ</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5>ข้อมูลสถานประกอบการ</h5>
            <p><strong>ชื่อบริษัท:</strong> {{ $application->company_name }}</p>
            <p><strong>ที่อยู่:</strong> {{ $application->company_address }}</p>
            <p><strong>เบอร์โทร:</strong> {{ $application->company_phone }}</p>
            <p><strong>อีเมล:</strong> {{ $application->company_email }}</p>

            <h5 class="mt-4">ข้อมูลนักศึกษา</h5>
            <p><strong>ชื่อ-สกุล:</strong> {{ $application->student_title_th }} {{ $application->student_first_name_th }} {{ $application->student_last_name_th }}</p>
            <p><strong>รหัสนักศึกษา:</strong> {{ $application->student_id }}</p>
            <p><strong>คณะ/สาขา:</strong> {{ $application->faculty }} / {{ $application->major }}</p>
            <p><strong>GPA/GPAX:</strong> {{ $application->gpa }} / {{ $application->gpax }}</p>

            <h5 class="mt-4">ประวัติการศึกษา</h5>
            @foreach((array) ($application->education_history ?? []) as $item)
                <p>- {{ $item }}</p>
            @endforeach

            <h5 class="mt-4">ความสามารถทางภาษา</h5>
            @foreach((array) ($application->language_skills ?? []) as $item)
                <p>- {{ is_array($item) ? ($item['language'] ?? '') . ': ' . ($item['level'] ?? '') : $item }}</p>
            @endforeach
        </div>
    </div>
</div>
</body>
</html>
