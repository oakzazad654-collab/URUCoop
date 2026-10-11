<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มใบสมัครสหกิจศึกษา</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        .section-title { border-left: 3px solid #2563eb; padding-left: 10px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">แบบฟอร์มใบสมัครงานสหกิจศึกษา (สก.03)</h2>
        <a href="{{ route('internship-applications.index') }}" class="btn btn-outline-secondary">กลับหน้ารายการ</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ isset($application->id) ? route('internship-applications.update', $application) : route('internship-applications.store') }}">
        @csrf
        @if(isset($application->id))
            @method('PUT')
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title mb-3">ข้อมูลสถานประกอบการ</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ชื่อสถานประกอบการ</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $application->company_name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ระยะเวลาปฏิบัติงาน (เดือน)</label>
                        <input type="number" name="work_duration_months" class="form-control" value="{{ old('work_duration_months', $application->work_duration_months) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เบอร์โทรบริษัท</label>
                        <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $application->company_phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">ที่อยู่บริษัท</label>
                        <textarea name="company_address" class="form-control" rows="3">{{ old('company_address', $application->company_address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมลบริษัท</label>
                        <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $application->company_email) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title mb-3">ข้อมูลนักศึกษา</div>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">คำนำหน้า</label>
                        <input type="text" name="student_title_th" class="form-control" value="{{ old('student_title_th', $application->student_title_th) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">ชื่อ (ไทย)</label>
                        <input type="text" name="student_first_name_th" class="form-control" value="{{ old('student_first_name_th', $application->student_first_name_th) }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">นามสกุล (ไทย)</label>
                        <input type="text" name="student_last_name_th" class="form-control" value="{{ old('student_last_name_th', $application->student_last_name_th) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ชื่อ (อังกฤษ)</label>
                        <input type="text" name="student_first_name_en" class="form-control" value="{{ old('student_first_name_en', $application->student_first_name_en) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">นามสกุล (อังกฤษ)</label>
                        <input type="text" name="student_last_name_en" class="form-control" value="{{ old('student_last_name_en', $application->student_last_name_en) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">รหัสนักศึกษา</label>
                        <input type="text" name="student_id" class="form-control" value="{{ old('student_id', $application->student_id) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">คณะ</label>
                        <input type="text" name="faculty" class="form-control" value="{{ old('faculty', $application->faculty) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">สาขา</label>
                        <input type="text" name="major" class="form-control" value="{{ old('major', $application->major) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ชั้นปี</label>
                        <input type="text" name="year_level" class="form-control" value="{{ old('year_level', $application->year_level) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GPA</label>
                        <input type="number" step="0.01" name="gpa" class="form-control" value="{{ old('gpa', $application->gpa) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GPAX</label>
                        <input type="number" step="0.01" name="gpax" class="form-control" value="{{ old('gpax', $application->gpax) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">วันเกิด</label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $application->birth_date ? $application->birth_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">สถานที่เกิด</label>
                        <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $application->birth_place) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ที่อยู่ที่พักอาศัย</label>
                        <textarea name="home_address" class="form-control" rows="3">{{ old('home_address', $application->home_address) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เบอร์โทร</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $application->phone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $application->email) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ส่วนสูง</label>
                        <input type="text" name="height" class="form-control" value="{{ old('height', $application->height) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">น้ำหนัก</label>
                        <input type="text" name="weight" class="form-control" value="{{ old('weight', $application->weight) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">กรุ๊ปเลือด</label>
                        <input type="text" name="blood_type" class="form-control" value="{{ old('blood_type', $application->blood_type) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">ศาสนา</label>
                        <input type="text" name="religion" class="form-control" value="{{ old('religion', $application->religion) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title mb-3">บุคคลติดต่อฉุกเฉิน</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ชื่อ-สกุล</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $application->emergency_contact_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ความสัมพันธ์</label>
                        <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation', $application->emergency_contact_relation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เบอร์โทร</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $application->emergency_contact_phone) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title mb-3">ข้อมูลครอบครัว</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ชื่อบิดา</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $application->father_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">อาชีพบิดา</label>
                        <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation', $application->father_occupation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เบอร์โทรบิดา</label>
                        <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone', $application->father_phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ชื่อมารดา</label>
                        <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $application->mother_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">อาชีพมารดา</label>
                        <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation', $application->mother_occupation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เบอร์โทรมารดา</label>
                        <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone', $application->mother_phone) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title mb-3">ประวัติการศึกษา / ความสามารถทางภาษา</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ประวัติการศึกษา (แต่ละรายการคั่นด้วยเครื่องหมาย ;)</label>
                        <textarea name="education_history" class="form-control" rows="4">{{ old('education_history', is_array($application->education_history) ? implode('; ', $application->education_history) : '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ความสามารถทางภาษา (เช่น ภาษาไทย:ดีมาก; ภาษาอังกฤษ:ดี)</label>
                        <textarea name="language_skills" class="form-control" rows="4">{{ old('language_skills', is_array($application->language_skills) ? collect($application->language_skills)->map(function($item){ return $item['language'] . ':' . $item['level']; })->implode('; ') : '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg">{{ isset($application->id) ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' }}</button>
        </div>
    </form>
</div>
</body>
</html>
