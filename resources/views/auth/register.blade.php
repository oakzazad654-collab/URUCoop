<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สมัครสมาชิก | URU Coop-IS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#edf7ef,#f8faf8 58%,#e4f2e7)}
        .register-screen{min-height:100vh;padding:36px 16px;display:grid;place-items:center}
        .register-card{width:min(100%,760px);padding:30px;background:#fff;border:1px solid #dce8df;border-radius:14px;box-shadow:0 18px 50px rgba(20,83,45,.12)}
        .register-fields[hidden]{display:none}
        @media(max-width:520px){.register-card{padding:22px 18px}}
    </style>
</head>
<body>
<main class="register-screen">
    <section class="register-card">
        <p class="page-eyebrow mb-2">URU Coop-IS · มหาวิทยาลัยราชภัฏอุตรดิตถ์</p>
        <h1 class="h3 mb-2">สมัครบัญชีผู้ใช้งาน</h1>
        <p class="text-muted mb-4">กรอกข้อมูลตามสถานะของตนเอง ข้อมูลบทบาทบุคลากรและสถานประกอบการจะรอ Admin ตรวจสอบก่อนใช้งาน</p>
        @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('register.store') }}" class="row g-3">
            @csrf
            <div class="col-12"><label for="role" class="form-label">สมัครในสถานะ</label><select id="role" name="role" class="form-select" required>
                <option value="">เลือกสถานะ</option>
                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>นักศึกษา</option>
                <option value="advisor" {{ old('role') === 'advisor' ? 'selected' : '' }}>อาจารย์/อาจารย์ที่ปรึกษา</option>
                <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>อาจารย์นิเทศ</option>
                <option value="mentor" {{ old('role') === 'mentor' ? 'selected' : '' }}>ผู้นิเทศงานสถานประกอบการ</option>
                <option value="company" {{ old('role') === 'company' ? 'selected' : '' }}>สถานประกอบการ</option>
            </select></div>
            <div class="col-md-6"><label for="name" class="form-label">ชื่อ-นามสกุล/ชื่อผู้ติดต่อ</label><input id="name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255"></div>
            <div class="col-md-6"><label for="email" class="form-label">อีเมล</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required maxlength="255" autocomplete="email"></div>
            <div class="col-md-6"><label for="phone" class="form-label">โทรศัพท์</label><input id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel"></div>
            <div class="col-md-6 role-field" data-roles="student"><label for="student_number" class="form-label">รหัสนักศึกษา</label><input id="student_number" name="student_number" class="form-control" value="{{ old('student_number') }}" maxlength="50" data-required="true"></div>
            <div class="col-md-6 role-field" data-roles="student,advisor,supervisor"><label for="faculty_id" class="form-label">คณะ</label><select id="faculty_id" name="faculty_id" class="form-select" data-required="true"><option value="">เลือกคณะ</option>@foreach($faculties as $faculty)<option value="{{ $faculty->id }}" {{ (string) old('faculty_id') === (string) $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>@endforeach</select></div>
            <div class="col-md-6 role-field" data-roles="student,advisor,supervisor"><label for="department_id" class="form-label">สาขา/ภาควิชา</label><select id="department_id" name="department_id" class="form-select" data-required="true"><option value="">เลือกสาขา</option>@foreach($faculties as $faculty)@foreach($faculty->departments as $department)<option value="{{ $department->id }}" data-faculty="{{ $faculty->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>@endforeach @endforeach</select></div>
            <div class="col-12 role-field" data-roles="student,advisor,supervisor"><label for="program_id" class="form-label">หลักสูตร/สาขาวิชา</label><select id="program_id" name="program_id" class="form-select" data-required="true"><option value="">เลือกหลักสูตร</option>@foreach($programs as $program)<option value="{{ $program->program_id }}" data-faculty="{{ $program->faculty_id }}" {{ (string) old('program_id') === (string) $program->program_id ? 'selected' : '' }}>{{ $program->program_name }}</option>@endforeach</select></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="title_name" class="form-label">คำนำหน้าชื่อ</label><input id="title_name" name="title_name" class="form-control" value="{{ old('title_name') }}" maxlength="30"></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="gender" class="form-label">เพศ</label><select id="gender" name="gender" class="form-select"><option value="">ไม่ระบุ</option><option value="MALE" {{ old('gender') === 'MALE' ? 'selected' : '' }}>ชาย</option><option value="FEMALE" {{ old('gender') === 'FEMALE' ? 'selected' : '' }}>หญิง</option><option value="OTHER" {{ old('gender') === 'OTHER' ? 'selected' : '' }}>อื่น ๆ</option></select></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="birth_date" class="form-label">วันเกิด</label><input id="birth_date" name="birth_date" type="date" class="form-control" value="{{ old('birth_date') }}"></div>
            <div class="col-md-6 role-field" data-roles="student"><label for="nationality" class="form-label">สัญชาติ</label><input id="nationality" name="nationality" class="form-control" value="{{ old('nationality') }}" maxlength="100"></div>
            <div class="col-md-6 role-field" data-roles="student"><label for="religion" class="form-label">ศาสนา</label><input id="religion" name="religion" class="form-control" value="{{ old('religion') }}" maxlength="100"></div>
            <div class="col-md-6 role-field" data-roles="student"><label for="cumulative_gpa" class="form-label">เกรดเฉลี่ยสะสม (ถ้ามี)</label><input id="cumulative_gpa" name="cumulative_gpa" type="number" min="0" max="4" step="0.01" class="form-control" value="{{ old('cumulative_gpa') }}"></div>
            <div class="col-12 role-field" data-roles="student"><label for="current_address" class="form-label">ที่อยู่ปัจจุบัน</label><textarea id="current_address" name="current_address" class="form-control" rows="2" maxlength="5000">{{ old('current_address') }}</textarea></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="emergency_contact_name" class="form-label">ผู้ติดต่อฉุกเฉิน</label><input id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}" maxlength="200"></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="emergency_contact_relation" class="form-label">ความสัมพันธ์</label><input id="emergency_contact_relation" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation') }}" maxlength="100"></div>
            <div class="col-md-4 role-field" data-roles="student"><label for="emergency_contact_phone" class="form-label">โทรศัพท์ผู้ติดต่อฉุกเฉิน</label><input id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}" maxlength="30"></div>
            <div class="col-md-6 role-field" data-roles="advisor,supervisor"><label for="staff_code" class="form-label">รหัสบุคลากร</label><input id="staff_code" name="staff_code" class="form-control" value="{{ old('staff_code') }}" maxlength="30" data-required="true"></div>
            <div class="col-md-6 role-field" data-roles="advisor,supervisor,mentor"><label for="position_name" class="form-label">ตำแหน่ง</label><input id="position_name" name="position_name" class="form-control" value="{{ old('position_name') }}" maxlength="150" data-required="{{ old('role') === 'mentor' ? 'true' : 'false' }}"></div>
            <div class="col-12 role-field" data-roles="mentor"><label for="company_id" class="form-label">สถานประกอบการที่สังกัด</label><select id="company_id" name="company_id" class="form-select" data-required="true"><option value="">เลือกสถานประกอบการที่ได้รับอนุมัติ</option>@foreach($companies as $company)<option value="{{ $company->id }}" {{ (string) old('company_id') === (string) $company->id ? 'selected' : '' }}>{{ $company->company_name_th ?: $company->name }}</option>@endforeach</select></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="company_name" class="form-label">ชื่อสถานประกอบการ</label><input id="company_name" name="company_name" class="form-control" value="{{ old('company_name') }}" maxlength="255" data-required="true"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="business_type" class="form-label">ประเภทธุรกิจ</label><input id="business_type" name="business_type" class="form-control" value="{{ old('business_type') }}" maxlength="200" data-required="true"></div>
            <div class="col-12 role-field" data-roles="company"><label for="address" class="form-label">ที่อยู่สถานประกอบการ</label><textarea id="address" name="address" class="form-control" rows="2" maxlength="2000" data-required="true">{{ old('address') }}</textarea></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="tax_id" class="form-label">เลขประจำตัวผู้เสียภาษี (ถ้ามี)</label><input id="tax_id" name="tax_id" class="form-control" value="{{ old('tax_id') }}" maxlength="20"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="website" class="form-label">เว็บไซต์ (ถ้ามี)</label><input id="website" name="website" type="url" class="form-control" value="{{ old('website') }}" maxlength="255"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="province" class="form-label">จังหวัด</label><input id="province" name="province" class="form-control" value="{{ old('province') }}" maxlength="255"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="postal_code" class="form-label">รหัสไปรษณีย์</label><input id="postal_code" name="postal_code" class="form-control" value="{{ old('postal_code') }}" maxlength="10"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="latitude" class="form-label">ละติจูด (Latitude)</label><input id="latitude" name="latitude" type="number" step="any" min="-90" max="90" class="form-control" value="{{ old('latitude') }}" data-required="true"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="longitude" class="form-label">ลองจิจูด (Longitude)</label><input id="longitude" name="longitude" type="number" step="any" min="-180" max="180" class="form-control" value="{{ old('longitude') }}" data-required="true"></div>
            <div class="col-md-6 role-field" data-roles="company"><label for="contact_person" class="form-label">ชื่อผู้ประสานงาน</label><input id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person') }}" maxlength="255" data-required="true"></div>
            <div class="col-md-6"><label for="password" class="form-label">รหัสผ่าน (อย่างน้อย 10 ตัวอักษร)</label><input id="password" name="password" type="password" class="form-control" required minlength="10" autocomplete="new-password"></div>
            <div class="col-md-6"><label for="password_confirmation" class="form-label">ยืนยันรหัสผ่าน</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="10" autocomplete="new-password"></div>
            <div class="col-12 d-flex flex-wrap gap-2 pt-2"><button class="btn btn-primary" type="submit">ส่งข้อมูลสมัคร</button><a class="btn btn-outline-secondary" href="{{ route('login') }}">กลับเข้าสู่ระบบ</a></div>
        </form>
    </section>
</main>
<script>
    const roleSelector = document.getElementById('role');
    const facultySelector = document.getElementById('faculty_id');
    const departmentSelector = document.getElementById('department_id');
    const programSelector = document.getElementById('program_id');
    function syncRegistrationFields() {
        document.querySelectorAll('.role-field,[data-role-field]').forEach(function (field) {
            const roles = (field.dataset.roles || field.dataset.roleField || '').split(',');
            const visible = roles.includes(roleSelector.value);
            field.hidden = !visible;
            field.querySelectorAll('input,select,textarea').forEach(function (input) {
                input.disabled = !visible;
                input.required = visible && (input.dataset.required === 'true' || (input.id === 'position_name' && roleSelector.value === 'mentor'));
            });
        });
        [departmentSelector, programSelector].forEach(function (select) {
            if (!select) return;
            Array.from(select.options).forEach(function (option) {
                if (option.dataset.faculty) option.hidden = option.dataset.faculty !== facultySelector.value;
            });
            if (select.selectedOptions[0] && select.selectedOptions[0].hidden) select.value = '';
        });
    }
    roleSelector.addEventListener('change', syncRegistrationFields);
    if (facultySelector) facultySelector.addEventListener('change', syncRegistrationFields);
    syncRegistrationFields();
</script>
</body>
</html>
