@extends('layouts.app')

@section('title', 'นักศึกษาในดูแล | URU Coop-IS')
@section('breadcrumb', 'นักศึกษาในดูแล')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">ขอบเขตการดูแล</p><h1>นักศึกษาในความดูแล</h1><p class="page-subtitle">แสดงเฉพาะนักศึกษาที่ได้รับมอบหมายให้คุณเป็นอาจารย์ที่ปรึกษา</p></div></header>
<section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">รายชื่อนักศึกษา</h2><span class="small text-muted">{{ $students->count() }} คน</span></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>ชื่อ</th><th>รหัสนักศึกษา</th><th>คณะ</th><th>สาขา</th><th>อีเมล</th></tr></thead><tbody>
@forelse($students as $student)<tr data-search-row><td><strong>{{ $student->name }}</strong></td><td>{{ $student->student_number ?: '—' }}</td><td>{{ $student->faculty->name ?? '—' }}</td><td>{{ $student->department->name ?? '—' }}</td><td>{{ $student->email }}</td></tr>@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="users-round"></i></span><strong>ยังไม่มีนักศึกษาที่ได้รับมอบหมาย</strong><span>ติดต่อเจ้าหน้าที่ศูนย์เพื่อกำหนดอาจารย์ที่ปรึกษา</span></div></td></tr>@endforelse
</tbody></table></div></section>
@endsection
