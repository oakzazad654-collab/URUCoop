@extends('layouts.app')

@section('title', 'แบบฟอร์ม สก. | URU Coop-IS')
@section('breadcrumb', 'แบบฟอร์ม สก.')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">แบบฟอร์มสหกิจศึกษา · มหาวิทยาลัยราชภัฏอุตรดิตถ์</p><h1>แบบฟอร์ม สก.</h1><p class="page-subtitle">กรอกข้อมูล บันทึกฉบับร่าง แก้ไข ตรวจสอบพรีวิว และดาวน์โหลด PDF สำหรับแบบฟอร์ม สก. ทุกฉบับ</p></div></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="form-type-grid mb-4">
    @foreach($types as $type => $definition)
        <article class="form-type-tile">
            <span class="form-type-mark">{{ strtoupper(str_replace('sk-', '', $type)) }}</span>
            <span class="form-type-copy"><strong>{{ $definition['title'] }}</strong><small>{{ $definition['role'] }}</small></span>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <a class="btn btn-sm btn-primary" href="{{ route('student.cwie.create', $type) }}">กรอกแบบฟอร์ม</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('public.forms.download', ['file' => $definition['source']]) }}" target="_blank" rel="noopener">ดูต้นฉบับ</a>
            </div>
        </article>
    @endforeach
</div>
<section class="card">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="h6 mb-0">รายการแบบฟอร์มที่บันทึก</h2><span class="small text-muted">{{ $documents->count() }} รายการ</span></div><form method="GET" action="{{ route('student.cwie.index') }}" class="d-flex gap-2"><select name="type" class="form-select form-select-sm" aria-label="กรองตามประเภท"><option value="">แบบฟอร์มทั้งหมด</option>@foreach($types as $type => $definition)<option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('-', ' ', $type)) }}</option>@endforeach</select><button class="btn btn-sm btn-outline-primary">กรอง</button></form></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>แบบฟอร์ม</th><th>นักศึกษา/ผู้กรอก</th><th>สถานะ</th><th>แก้ไขล่าสุด</th><th class="text-end">เปิด</th></tr></thead><tbody>
        @forelse($documents as $document)
            <tr><td><strong>{{ strtoupper(str_replace('-', ' ', $document->type)) }}</strong><div class="small text-muted">{{ $document->title }}</div></td><td>{{ data_get($document->data, 'student_name', auth()->user()->name) }}<div class="small text-muted">{{ $document->student_id ?: '—' }}</div></td><td>@include('cwie._status_badge', ['status' => $document->status])</td><td class="text-nowrap">{{ $document->updated_at->format('d/m/Y H:i') }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('student.cwie.show', [$document->type, $document]) }}" aria-label="ดูพรีวิว/แก้ไข"><i data-lucide="arrow-up-right" style="width:15px;height:15px"></i></a></td></tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="files"></i></span><strong>ยังไม่มีแบบฟอร์มที่บันทึก</strong><span>เลือกแบบฟอร์ม สก. ด้านบนเพื่อเริ่มกรอกข้อมูล</span></div></td></tr>
        @endforelse
    </tbody></table></div>
</section>
@endsection
