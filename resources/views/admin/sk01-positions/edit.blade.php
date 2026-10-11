@extends('layouts.app')

@section('title', 'ปรับตำแหน่งฟอร์ม '.strtoupper(str_replace('-', '.', $type)).' | URU Coop-IS')
@section('breadcrumb', 'ปรับตำแหน่งฟอร์ม '.strtoupper(str_replace('-', '.', $type)))

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">จัดการแบบฟอร์ม · ผู้ดูแลระบบ</p><h1>ปรับตำแหน่งช่องกรอก {{ strtoupper(str_replace('-', '.', $type)) }}</h1><p class="page-subtitle">{{ $definition['title'] }}</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.sk-positions.index') }}">กลับรายการแบบฟอร์ม</a>
</header>

@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

<section class="card mb-3">
    <div class="card-body">
        <p class="mb-2"><strong>วิธีใช้:</strong> ลองจัดวางจากข้อความใน PDF ก่อน ระบบจะวางช่องที่พบคำกำกับให้ แล้วตรวจแก้เฉพาะช่องที่คลาดเคลื่อนด้วยการลากหรือปรับตัวเลข</p>
        <p class="small text-muted mb-2">ระบบอ่านได้เฉพาะ PDF ที่มีข้อความให้ค้นหา หากไฟล์เป็นภาพสแกนหรือไม่พบคำกำกับ จะคงตำแหน่งเดิมไว้ ช่องในตารางซ้ำอาจต้องตรวจแก้เอง</p>
        <button class="btn btn-outline-primary btn-sm" type="button" id="autoPositionButton" disabled>จัดวางจากข้อความใน PDF</button>
        <span class="small text-muted ms-2" id="autoPositionStatus" role="status">กำลังเตรียมเอกสาร...</span>
    </div>
</section>

<section class="card sk-position-card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.sk-positions.update', $type) }}" id="positionForm">
            @csrf
            @method('PUT')
            <div class="sk-editor-toolbar">
                <label class="flex-grow-1" for="fieldSelect">ช่องที่เลือก
                    <select id="fieldSelect" class="form-select">
                        @foreach($fields as $field)
                            <option value="{{ $field['key'] }}">{{ $field['label'] }} ({{ $field['key'] }})</option>
                        @endforeach
                    </select>
                </label>
                <label for="formSelect">แบบฟอร์ม
                    <select id="formSelect" class="form-select" onchange="window.location.href=this.value">
                        @foreach($forms as $formType => $form)
                            <option value="{{ route('admin.sk-positions.edit', $formType) }}" {{ $type === $formType ? 'selected' : '' }}>{{ strtoupper(str_replace('-', '.', $formType)) }}</option>
                        @endforeach
                    </select>
                </label>
                <label for="positionLeft">ซ้าย (%)
                    <input id="positionLeft" class="form-control" type="number" min="0" max="100" step="0.01">
                </label>
                <label for="positionTop">บน (%)
                    <input id="positionTop" class="form-control" type="number" min="0" max="100" step="0.01">
                </label>
                <label for="positionWidth">กว้าง/ระยะช่อง (%)
                    <input id="positionWidth" class="form-control" type="number" min="0.1" max="100" step="0.01">
                </label>
                <label for="positionRowStep">ระยะห่างแถว (%)
                    <input id="positionRowStep" class="form-control" type="number" min="0" max="20" step="0.01">
                </label>
                <label for="positionPage">หน้า PDF
                    <select id="positionPage" class="form-select" data-pdf-page-select>
                        <option value="1">หน้า 1</option>
                    </select>
                </label>
                <label for="previewPage">แสดงหน้า
                    <select id="previewPage" class="form-select" data-pdf-page>
                        <option value="1">หน้า 1</option>
                    </select>
                </label>
            </div>
            <div class="sk-paper-wrap" data-sk-pdf data-pdf-url="{{ $sourceUrl }}" data-pdf-worker="{{ asset('js/pdf.worker.min.js') }}">
                <canvas class="sk-paper-canvas" aria-label="เอกสารต้นฉบับแบบฟอร์ม {{ strtoupper(str_replace('-', '.', $type)) }}"></canvas>
                <p class="sk-paper-status" data-pdf-error role="status">กำลังโหลดเอกสารต้นฉบับ...</p>
                <div class="sk-paper-fields" hidden>
                    @foreach($fields as $field)
                        @php($markerField = in_array($field['type'], ['radio', 'checkboxes', 'weeks', 'months'], true) || ($field['type'] === 'select' && !empty($field['position']['mark_option'])))
                        <button type="button" class="sk-position-field {{ $markerField ? 'sk-position-radio' : '' }}" data-field="{{ $field['key'] }}" data-label="{{ $field['label'] }}" data-layout="{{ $field['position']['layout'] ?? 'horizontal' }}" data-count="{{ $field['option_count'] }}" data-page="{{ $field['position']['page'] ?? '' }}" data-auto-position="{{ $field['auto_position'] ? '1' : '0' }}" style="left:{{ $field['position']['left'] }}%;top:{{ $field['position']['top'] }}%;width:{{ $field['position']['width'] }}%" aria-label="ลากเพื่อปรับตำแหน่ง {{ $field['label'] }}">
                            @if($markerField)
                                @for($marker = 0; $marker < $field['option_count']; $marker++)<span aria-hidden="true">○</span>@endfor
                            @else
                                {{ $field['label'] }}
                            @endif
                        </button>
                        <input type="hidden" name="positions[{{ $field['key'] }}][left]" value="{{ $field['position']['left'] }}" data-position="{{ $field['key'] }}-left">
                        <input type="hidden" name="positions[{{ $field['key'] }}][top]" value="{{ $field['position']['top'] }}" data-position="{{ $field['key'] }}-top">
                        <input type="hidden" name="positions[{{ $field['key'] }}][width]" value="{{ $field['position']['width'] }}" data-position="{{ $field['key'] }}-width">
                        <input type="hidden" name="positions[{{ $field['key'] }}][page]" value="{{ $field['position']['page'] ?? '' }}" data-position="{{ $field['key'] }}-page">
                        <input type="hidden" name="positions[{{ $field['key'] }}][row_step]" value="{{ $field['position']['row_step'] ?? 0 }}" data-position="{{ $field['key'] }}-row_step">
                    @endforeach
                </div>
            </div>
            <div class="sk-editor-actions">
                <span class="small text-muted">ตำแหน่งวัดเป็นเปอร์เซ็นต์เทียบกับขนาดหน้า PDF</span>
                <button class="btn btn-primary" type="submit">บันทึกตำแหน่งทั้งหมด</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.sk-positions.reset', $type) }}" class="mt-2" onsubmit="return confirm('คืนค่าตำแหน่งช่องทั้งหมดเป็นค่าเริ่มต้นหรือไม่?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">คืนค่าตำแหน่งตั้งต้น</button>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
    .sk-position-card .card-body{padding:clamp(10px,2vw,22px)}
    .sk-editor-toolbar{display:flex;align-items:end;gap:12px;margin-bottom:14px;flex-wrap:wrap}
    .sk-editor-toolbar label{display:grid;gap:5px;font-size:12px;font-weight:600}
    .sk-editor-toolbar .flex-grow-1{min-width:250px}
    .sk-editor-toolbar input{width:105px}
    .sk-paper-wrap{position:relative;width:min(100%,850px);aspect-ratio:210/297;margin:0 auto;overflow:hidden;background:#fff;border:1px solid #cbd5e1;box-shadow:0 12px 30px rgba(15,23,42,.12)}
    .sk-paper-canvas,.sk-paper-fields{position:absolute;inset:0;width:100%;height:100%;border:0}
    .sk-paper-canvas{display:block}
    .sk-paper-fields{pointer-events:none}
    .sk-paper-fields[hidden]{display:none}
    .sk-paper-status{position:absolute;z-index:4;top:50%;left:50%;max-width:90%;padding:10px 14px;transform:translate(-50%,-50%);color:#334155;background:rgba(255,255,255,.94);border:1px solid #cbd5e1;border-radius:8px;text-align:center}
    .sk-position-field{position:absolute;z-index:2;min-width:14px;height:2.2%;min-height:12px;padding:0 2px;overflow:hidden;color:#102a43;background:rgba(147,197,253,.6);border:1px solid #2563eb;border-radius:2px;font-size:clamp(6px,1vw,10px);line-height:1.1;text-align:left;white-space:nowrap;touch-action:none;cursor:move;pointer-events:auto}
    .sk-position-field.is-selected{z-index:3;background:rgba(96,165,250,.84);outline:2px solid #1d4ed8}
    .sk-position-radio{height:1.5%;min-height:9px;padding:0;background:rgba(254,240,138,.7);text-align:center}
    .sk-position-radio span{position:absolute;display:block;width:10px;height:10px;transform:translate(-50%,-50%);line-height:9px}
    .sk-editor-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px}
    @media(max-width:650px){.sk-editor-actions{align-items:stretch;flex-direction:column}}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script src="{{ asset('js/sk-form-pdf.js') }}"></script>
<script src="{{ asset('js/sk01-position-editor.js') }}"></script>
@endpush
