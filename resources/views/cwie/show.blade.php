@extends('layouts.app')

@section('title', strtoupper(str_replace('-', ' ', $type)).' | URU Coop-IS')
@section('breadcrumb', strtoupper(str_replace('-', ' ', $type)))

@section('content')
@php
    $role = auth()->user()->role;
    $isSkForm = strpos($type, 'sk-') === 0;
    $dashboardRoute = $role === 'student' ? 'student.approvals.dashboard' : ($role === 'advisor' ? 'advisor.approvals.dashboard' : $role.'.approvals.dashboard');
    $pdfRoute = $role === 'student' ? 'student.cwie.pdf' : ($role === 'advisor' && $type === 'cwie-06' ? 'advisor.cwie06.pdf' : $role.'.cwie.pdf');
    $pdfParameters = $role === 'advisor' && $type === 'cwie-06' ? [$document] : [$type, $document];
@endphp
<header class="page-heading"><div><p class="page-eyebrow">{{ strtoupper(str_replace('-', ' ', $type)) }} · {{ $definition['title'] }}</p><h1>รายละเอียดเอกสาร</h1><div class="mt-2">@include('cwie._status_badge', ['status' => $document->status])</div></div><div class="d-flex flex-wrap gap-2">
    @if($isSkForm)<a href="{{ route('public.forms.download', ['file' => $definition['source']]).'?inline=1' }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">แบบฟอร์มต้นฉบับ</a>@endif
    @if($role === 'student' && in_array($document->status, ['draft', 'revision_requested'], true))<a href="{{ route('student.cwie.edit', [$type, $document]) }}" class="btn btn-outline-primary"><i data-lucide="pencil" class="me-1" style="width:15px;height:15px"></i>{{ $type === 'sk-01' ? 'กรอก/แก้ไขบนเอกสาร' : 'แก้ไขข้อมูล' }}</a>@endif
    <a href="{{ route($pdfRoute, $pdfParameters) }}" target="_blank" class="btn btn-outline-primary"><i data-lucide="eye" class="me-1" style="width:15px;height:15px"></i>พรีวิว PDF</a>
    <a href="{{ route($pdfRoute, array_merge($pdfParameters, ['download' => 1])) }}" class="btn btn-primary"><i data-lucide="download" class="me-1" style="width:15px;height:15px"></i>ดาวน์โหลด PDF</a>
    <a href="{{ route($dashboardRoute) }}" class="btn btn-outline-secondary">รายการ</a>
</div></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($role === 'student' && $document->status === 'revision_requested')
    @if($document->advisor_comment)<div class="alert alert-warning"><strong>ข้อเสนอแนะอาจารย์:</strong> {{ $document->advisor_comment }}</div>@endif
    @if($document->officer_comment)<div class="alert alert-warning"><strong>ข้อเสนอแนะเจ้าหน้าที่:</strong> {{ $document->officer_comment }}</div>@endif
@endif
@if(!$isSkForm && $role === 'student' && in_array($document->status, ['draft', 'revision_requested'], true))
    <div class="action-strip mb-3"><div><strong>{{ $document->status === 'draft' ? 'เอกสารฉบับร่าง' : 'พร้อมส่งกลับเพื่อพิจารณา' }}</strong><span>{{ $document->status === 'draft' ? 'ตรวจสอบข้อมูลก่อนส่งให้อาจารย์ที่ปรึกษา' : 'แก้ไขตามข้อเสนอแนะแล้วส่งอีกครั้ง' }}</span></div><form method="POST" action="{{ route('student.approvals.submit', [$type, $document]) }}">@csrf<button class="btn btn-primary">{{ $document->status === 'draft' ? 'ส่งพิจารณา' : 'ส่งแก้ไขให้พิจารณาอีกครั้ง' }}<i data-lucide="arrow-right" class="ms-1" style="width:15px;height:15px"></i></button></form></div>
@elseif($role === 'advisor' && $type === 'cwie-06' && $document->status === 'pending_officer')
    <div class="alert alert-info">ส่งคำขอให้เจ้าหน้าที่ศูนย์สหกิจศึกษาพิจารณาแล้ว</div>
@elseif($role !== 'student' && in_array($document->status, ['pending_advisor', 'pending_officer'], true))
    <a href="{{ route($role === 'admin' ? 'admin.approvals.review' : $role.'.approvals.review', [$type, $document]) }}" class="btn btn-primary mb-3">เปิดหน้าพิจารณา</a>
@endif
<div class="record-summary mb-3"><div><span>ผู้ยื่น</span><strong>{{ $document->owner->name ?? data_get($document->data, 'advisor_name', '—') }}</strong></div><div><span>รหัสนักศึกษา</span><strong>{{ $document->student_id ?: '—' }}</strong></div><div><span>อาจารย์ที่ปรึกษา</span><strong>{{ $document->owner->advisor->name ?? $document->advisor->name ?? '—' }}</strong></div><div><span>แก้ไขล่าสุด</span><strong>{{ $document->updated_at->format('d/m/Y H:i') }}</strong></div></div>
@foreach($definition['sections'] as $section)
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">{{ $section['title'] }}</h2><dl class="row detail-list mb-0">
        @foreach($section['fields'] as $field)
            @php($displayValue = data_get($document->data, $field['key'], '—'))
            @if(is_array($displayValue))@php($displayValue = implode(', ', $displayValue))@endif
            <dt class="col-md-3">{{ $field['label'] }}</dt><dd class="col-md-9">{!! nl2br(e($displayValue ?: '—')) !!}</dd>
        @endforeach
    </dl></div></section>
@endforeach
@foreach($definition['repeaters'] ?? [] as $repeater)
    <section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">{{ $repeater['label'] }}</h2><div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr>@foreach($repeater['fields'] as $field)<th>{{ $field['label'] }}</th>@endforeach</tr></thead><tbody>
        @forelse(data_get($document->data, $repeater['key'], []) as $row)<tr>@foreach($repeater['fields'] as $field)<td>{{ in_array(($field['type'] ?? ''), ['months', 'weeks'], true) ? (($field['type'] === 'weeks' ? 'สัปดาห์ ' : 'เดือน ').implode(', ', data_get($row, $field['key'], []))) : data_get($row, $field['key'], '') }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($repeater['fields']) }}" class="text-muted">ไม่มีข้อมูล</td></tr>@endforelse
    </tbody></table></div></div></section>
@endforeach
@foreach($definition['files'] as $file)
    @if($document->{$file['column']})<section class="card form-section mb-3"><div class="card-body"><h2 class="section-title">{{ $file['label'] }}</h2><a href="{{ asset('storage/'.$document->{$file['column']}) }}" target="_blank"><img class="preview-image" src="{{ asset('storage/'.$document->{$file['column']}) }}" alt="{{ $file['label'] }}"></a></div></section>@endif
@endforeach
@if($role === 'student' && $document->status === 'draft')<div class="d-flex justify-content-end"><form method="POST" action="{{ route('student.cwie.destroy', [$type, $document]) }}" onsubmit="return confirm('ยืนยันการลบเอกสารฉบับร่างนี้หรือไม่?')">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i data-lucide="trash-2" class="me-1" style="width:15px;height:15px"></i>ลบฉบับร่าง</button></form></div>@endif
@endsection
