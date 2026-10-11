@extends('layouts.app')

@section('title', strtoupper(str_replace('-', ' ', $type)).' | URU Coop-IS')
@section('breadcrumb', strtoupper(str_replace('-', ' ', $type)))

@section('content')
@php
    $role = auth()->user()->role;
    $pdfRoute = $role === 'student' ? 'student.cwie.pdf' : ($role === 'advisor' && $type === 'cwie-06' ? 'advisor.cwie06.pdf' : $role.'.cwie.pdf');
    $pdfParameters = $role === 'advisor' && $type === 'cwie-06' ? [$document] : [$type, $document];
@endphp
<header class="page-heading">
    <div><p class="page-eyebrow">{{ strtoupper(str_replace('-', ' ', $type)) }} · {{ $definition['title'] }}</p><h1>ตรวจสอบเอกสาร</h1><div class="mt-2">@include('cwie._status_badge', ['status' => $document->status])</div></div>
    <a href="{{ route($role.'.approvals.dashboard') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="me-1" style="width:15px;height:15px"></i>กลับรายการ</a>
</header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="review-grid">
    <main class="review-main">
        <section class="card mb-3"><div class="card-header bg-white"><h2 class="h6 mb-0">ข้อมูลเอกสาร</h2><span class="small text-muted">{{ $document->owner->name ?? data_get($document->data, 'student_name', '—') }} · {{ $document->student_id ?: '—' }}</span></div><div class="card-body">
            @foreach($definition['sections'] as $section)
                <h3 class="section-title mt-2">{{ $section['title'] }}</h3><dl class="row field-list">
                @foreach($section['fields'] as $field)
                    @php($value = data_get($document->data, $field['key'], '—'))
                    @if(is_array($value))@php($value = implode(', ', $value))@endif
                    <dt class="col-md-4">{{ $field['label'] }}</dt><dd class="col-md-8">{{ $value ?: '—' }}</dd>
                @endforeach
                </dl>
            @endforeach
            @foreach($definition['repeaters'] ?? [] as $repeater)
                <h3 class="section-title mt-3">{{ $repeater['label'] }}</h3><div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr>@foreach($repeater['fields'] as $field)<th>{{ $field['label'] }}</th>@endforeach</tr></thead><tbody>
                    @forelse(data_get($document->data, $repeater['key'], []) as $row)<tr>@foreach($repeater['fields'] as $field)<td>{{ in_array(($field['type'] ?? ''), ['weeks', 'months'], true) ? implode(', ', data_get($row, $field['key'], [])) : data_get($row, $field['key'], '') }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($repeater['fields']) }}">—</td></tr>@endforelse
                </tbody></table></div>
            @endforeach
        </div></section>
        <section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">ตัวอย่าง PDF</h2></div><div class="pdf-preview-wrap"><iframe title="ตัวอย่าง PDF" src="{{ route($pdfRoute, $pdfParameters) }}"></iframe></div></section>
    </main>
    <aside class="review-aside">
        @if($role === 'student')
            <section class="card mb-3"><div class="card-header bg-white"><h2 class="h6 mb-0">ดำเนินการ</h2></div><div class="card-body">
                @if(in_array($document->status, ['draft', 'revision_requested'], true))
                    @if($document->status === 'revision_requested')
                        @if($document->advisor_comment)<p class="review-comment"><strong>ข้อเสนอแนะอาจารย์</strong><br>{{ $document->advisor_comment }}</p>@endif
                        @if($document->officer_comment)<p class="review-comment"><strong>ข้อเสนอแนะเจ้าหน้าที่</strong><br>{{ $document->officer_comment }}</p>@endif
                    @endif
                    <a href="{{ route('student.cwie.edit', [$type, $document]) }}" class="btn btn-outline-primary w-100 mb-2">แก้ไขข้อมูล</a>
                    <form method="POST" action="{{ route('student.approvals.submit', [$type, $document]) }}">@csrf<button class="btn btn-primary w-100">{{ $document->status === 'draft' ? 'ส่งพิจารณา' : 'ส่งแก้ไขให้พิจารณาอีกครั้ง' }}</button></form>
                @else<p class="text-muted mb-0">เอกสารถูกส่งเข้าสู่กระบวนการพิจารณาแล้ว</p>@endif
            </div></section>
        @elseif(in_array($document->status, ['pending_advisor', 'pending_officer'], true) && ($role === 'admin' || $role === ($document->status === 'pending_advisor' ? 'advisor' : 'officer')))
            @php($stage = $document->status === 'pending_advisor' ? 'advisor' : 'officer')
            @php($actionBase = $role === 'admin' ? 'admin.approvals.'.$stage.'.' : $role.'.approvals.')
            <section class="card mb-3"><div class="card-header bg-white"><h2 class="h6 mb-0">พิจารณาเอกสาร</h2></div><div class="card-body">
                <form method="POST" action="{{ route($actionBase.'approve', [$type, $document]) }}" class="mb-3">@csrf<label class="form-label" for="approve_comment">ความเห็นเพิ่มเติม (ถ้ามี)</label><textarea id="approve_comment" name="comment" rows="2" class="form-control mb-2"></textarea><button class="btn btn-success w-100"><i data-lucide="check" class="me-1" style="width:15px;height:15px"></i>อนุมัติ</button></form>
                <form method="POST" action="{{ route($actionBase.'revision', [$type, $document]) }}" class="mb-3">@csrf<label class="form-label" for="revision_comment">ข้อเสนอแนะเพื่อแก้ไข</label><textarea id="revision_comment" name="comment" rows="3" class="form-control mb-2" required></textarea><button class="btn btn-warning w-100"><i data-lucide="rotate-ccw" class="me-1" style="width:15px;height:15px"></i>ส่งกลับแก้ไข</button></form>
                <form method="POST" action="{{ route($actionBase.'reject', [$type, $document]) }}" onsubmit="return confirm('ยืนยันการไม่อนุมัติเอกสารนี้หรือไม่?')">@csrf<label class="form-label" for="reject_comment">เหตุผลที่ไม่อนุมัติ</label><textarea id="reject_comment" name="comment" rows="2" class="form-control mb-2" required></textarea><button class="btn btn-outline-danger w-100">ไม่อนุมัติ</button></form>
            </div></section>
        @endif
        <section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">ประวัติการพิจารณา</h2></div><div class="card-body">
            @forelse($histories as $history)<div class="history-item mb-3"><div class="fw-semibold">{{ str_replace('_', ' ', $history->action_type) }}</div><div class="small text-muted">{{ $history->actor->name ?? 'บัญชีที่ถูกลบ' }} · {{ $history->created_at->format('d/m/Y H:i') }}</div>@if($history->comment)<p class="mb-0 mt-1">{{ $history->comment }}</p>@endif</div>@empty<p class="text-muted mb-0">ยังไม่มีประวัติการดำเนินการ</p>@endforelse
        </div></section>
    </aside>
</div>
@endsection
