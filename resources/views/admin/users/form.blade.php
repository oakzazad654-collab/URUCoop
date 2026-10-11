@extends('layouts.app')

@section('title', ($user->exists ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้งาน').' | URU Coop-IS')
@section('breadcrumb', $user->exists ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้งาน')

@section('content')
@php($isEditing = $user->exists)
@php($routeBase = auth()->user()->role === 'officer' ? 'officer.users' : 'admin.users')
<header class="page-heading"><div><p class="page-eyebrow">การจัดการสิทธิ์</p><h1>{{ $isEditing ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้งาน' }}</h1><p class="page-subtitle">กำหนดบทบาทและข้อมูลสังกัดให้ถูกต้อง</p></div><a href="{{ route($routeBase.'.index') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="me-1" style="width:15px;height:15px"></i>กลับรายการ</a></header>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $isEditing ? route($routeBase.'.update', $user) : route($routeBase.'.store') }}">
    @csrf @if($isEditing)@method('PUT')@endif
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">บัญชีผู้ใช้งาน</h2><div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name">ชื่อ-นามสกุล</label><input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="email">อีเมล</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="role">บทบาท</label><select id="role" name="role" class="form-select" required>@foreach($roles as $role)<option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label" for="password">รหัสผ่าน {{ $isEditing ? '(เว้นว่างหากไม่เปลี่ยน)' : '' }}</label><input id="password" type="password" name="password" class="form-control" {{ $isEditing ? '' : 'required' }} minlength="10" autocomplete="new-password"><div class="form-text">ต้องมีอย่างน้อย 10 ตัวอักษร</div></div>
    </div></div></section>
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">ข้อมูลสังกัดและการดูแล</h2><div class="row g-3">
        <div class="col-md-6 student-fields"><label class="form-label" for="student_number">รหัสนักศึกษา</label><input id="student_number" name="student_number" class="form-control" value="{{ old('student_number', $user->student_number) }}"></div>
        <div class="col-md-6 student-fields"><label class="form-label" for="advisor_id">อาจารย์ที่ปรึกษา</label><select id="advisor_id" name="advisor_id" class="form-select"><option value="">ยังไม่กำหนด</option>@foreach($advisors as $advisor)<option value="{{ $advisor->id }}" {{ (string) old('advisor_id', $user->advisor_id) === (string) $advisor->id ? 'selected' : '' }}>{{ $advisor->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label" for="faculty_id">คณะ</label><select id="faculty_id" name="faculty_id" class="form-select"><option value="">ไม่ระบุ</option>@foreach($faculties as $faculty)<option value="{{ $faculty->id }}" {{ (string) old('faculty_id', $user->faculty_id) === (string) $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label" for="department_id">สาขา/ภาควิชา</label><select id="department_id" name="department_id" class="form-select"><option value="">ไม่ระบุ</option>@foreach($faculties as $faculty)@foreach($faculty->departments as $department)<option data-faculty="{{ $faculty->id }}" value="{{ $department->id }}" {{ (string) old('department_id', $user->department_id) === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>@endforeach @endforeach</select></div>
        <div class="col-md-6 company-fields"><label class="form-label" for="company_id">สถานประกอบการ</label><select id="company_id" name="company_id" class="form-select"><option value="">เลือกสถานประกอบการ</option>@foreach($companies as $company)<option value="{{ $company->id }}" {{ (string) old('company_id', $user->company_id) === (string) $company->id ? 'selected' : '' }}>{{ $company->company_name_th ?: $company->name }}</option>@endforeach</select><div class="form-text">บัญชีนี้จะเห็นเฉพาะข้อมูลของสถานประกอบการที่เลือก</div></div>
        <div class="col-md-6 mentor-fields"><label class="form-label" for="position_name">ตำแหน่งผู้นิเทศงาน</label><input id="position_name" name="position_name" class="form-control" value="{{ old('position_name', optional($user->companyContact)->position_name) }}"></div>
    </div></div></section>
    <div class="form-actions"><a href="{{ route($routeBase.'.index') }}" class="btn btn-outline-secondary">ยกเลิก</a><button class="btn btn-primary"><i data-lucide="save" class="me-1" style="width:16px;height:16px"></i>บันทึกผู้ใช้งาน</button></div>
</form>
@endsection

@push('scripts')
<script>
    const roleSelect = document.getElementById('role');
    const studentFields = document.querySelectorAll('.student-fields');
    const companyFields = document.querySelectorAll('.company-fields');
    const mentorFields = document.querySelectorAll('.mentor-fields');
    const facultySelect = document.getElementById('faculty_id');
    const departmentSelect = document.getElementById('department_id');
    function syncRoleFields() { studentFields.forEach(function (field) { field.hidden = roleSelect.value !== 'student'; field.querySelectorAll('input,select').forEach(function (control) { control.disabled = roleSelect.value !== 'student'; }); }); companyFields.forEach(function (field) { var companyRole = ['company', 'mentor'].includes(roleSelect.value); field.hidden = !companyRole; field.querySelectorAll('input,select').forEach(function (control) { control.disabled = !companyRole; }); }); mentorFields.forEach(function (field) { field.hidden = roleSelect.value !== 'mentor'; field.querySelectorAll('input,select').forEach(function (control) { control.disabled = roleSelect.value !== 'mentor'; control.required = roleSelect.value === 'mentor'; }); }); }
    function syncDepartments() { Array.from(departmentSelect.options).forEach(function (option) { if (option.dataset.faculty) option.hidden = option.dataset.faculty !== facultySelect.value; }); if (departmentSelect.selectedOptions[0] && departmentSelect.selectedOptions[0].hidden) departmentSelect.value = ''; }
    roleSelect.addEventListener('change', syncRoleFields);
    facultySelect.addEventListener('change', syncDepartments);
    syncRoleFields(); syncDepartments();
</script>
@endpush
