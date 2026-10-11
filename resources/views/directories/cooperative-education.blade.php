@extends('layouts.app')

@section('title', 'ทำเนียบสหกิจศึกษา | URU Coop-IS')
@section('breadcrumb', 'ทำเนียบสหกิจศึกษา')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">{{ auth()->user()->role === 'admin' ? 'ผู้ดูแลระบบ' : 'ผู้บริหาร' }} · ฐานข้อมูลสหกิจศึกษา</p><h1>ทำเนียบสหกิจศึกษา</h1><p class="page-subtitle">รายชื่อนักศึกษาที่ลงทะเบียน สถานประกอบการ และบุคลากรที่รับผิดชอบ</p></div></header>
<section class="card mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">นักศึกษาสหกิจศึกษา</h2><span class="small text-muted">{{ $students->total() }} รายชื่อ</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ชื่อ</th><th>รหัสนักศึกษา</th><th>หลักสูตร</th><th>คณะ</th><th>สถานะ</th></tr></thead><tbody>
@forelse($students as $student)<tr><td>{{ optional($student->user)->name ?: '—' }}</td><td>{{ $student->student_code }}</td><td>{{ optional($student->program)->program_name }}</td><td>{{ optional(optional($student->program)->faculty)->name }}</td><td>{{ optional($student->user)->status }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลนักศึกษา</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $students->links('pagination::bootstrap-4') }}</div></section>
<section class="card mb-4"><div class="card-header bg-white"><h2 class="h6 mb-0">สถานประกอบการ</h2><span class="small text-muted">{{ $companies->total() }} แห่ง</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ชื่อสถานประกอบการ</th><th>ประเภทธุรกิจ</th><th>จังหวัด</th><th>พิกัด</th><th>สถานะ</th></tr></thead><tbody>
@forelse($companies as $company)<tr><td>{{ $company->company_name_th ?: $company->name }}</td><td>{{ $company->business_type ?: '—' }}</td><td>{{ $company->province ?: '—' }}</td><td>{{ $company->latitude && $company->longitude ? $company->latitude.', '.$company->longitude : '—' }}</td><td>{{ $company->status }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลสถานประกอบการ</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $companies->links('pagination::bootstrap-4') }}</div></section>
<section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">บุคลากรประจำหลักสูตรและผู้นิเทศ</h2><span class="small text-muted">{{ $staffMembers->total() }} รายชื่อ</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>ชื่อ</th><th>รหัสบุคลากร</th><th>บทบาท</th><th>ตำแหน่ง</th><th>คณะ/หลักสูตร</th></tr></thead><tbody>
@forelse($staffMembers as $staff)<tr><td>{{ optional($staff->user)->name ?: '—' }}</td><td>{{ $staff->staff_code }}</td><td>{{ ['advisor' => 'อาจารย์ประสานงานหลักสูตร/ที่ปรึกษา', 'supervisor' => 'อาจารย์นิเทศ', 'officer' => 'เจ้าหน้าที่ประจำหลักสูตร'][$staff->user->role] ?? $staff->user->role }}</td><td>{{ $staff->position_name ?: '—' }}</td><td>{{ optional($staff->faculty)->name ?: optional(optional($staff->program)->faculty)->name }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีข้อมูลบุคลากร</td>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $staffMembers->links('pagination::bootstrap-4') }}</div></section>
@endsection
