@extends('layouts.app')

@section('title', strtoupper(str_replace('-', ' ', $type)).' | URU Coop-IS')
@section('breadcrumb', strtoupper(str_replace('-', ' ', $type)))

@section('content')
@php
    $formData = old('data', $document->data ?? []);
    $isEditing = isset($document->id);
    $role = auth()->user()->role;
    $hasPdfPositions = $type === 'sk-01' || (strpos($type, 'sk-') === 0 && !empty($definition['positions_configured']));
    if ($role === 'advisor') {
        $formAction = route('advisor.cwie06.store');
        $backUrl = route('advisor.approvals.dashboard');
    } else {
        $formAction = $isEditing ? route('student.cwie.update', [$type, $document]) : route('student.cwie.store', $type);
        $backUrl = route('student.cwie.index');
    }
@endphp
<header class="page-heading"><div><p class="page-eyebrow">{{ strtoupper(str_replace('-', ' ', $type)) }}</p><h1>{{ $definition['title'] }}</h1><p class="page-subtitle">{{ $hasPdfPositions ? 'กรอกข้อมูลในช่องว่างบนเอกสารต้นฉบับ แล้วบันทึกข้อมูล' : 'กรอกข้อมูลให้ครบถ้วน แล้วบันทึกเพื่อดำเนินการต่อ' }}</p></div><a href="{{ $backUrl }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" class="me-1" style="width:15px;height:15px"></i>กลับรายการ</a></header>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$hasPdfPositions)
    <nav class="section-nav" aria-label="ข้ามไปยังส่วนของแบบฟอร์ม">
        @foreach($definition['sections'] as $section)<a href="#form-section-{{ $loop->index }}">{{ $section['title'] }}</a>@if($type === 'cwie-01' && $loop->iteration === 2)<a href="#form-file-student_photo">รูปถ่ายนักศึกษา</a>@endif @endforeach
        @foreach($definition['repeaters'] ?? [] as $repeater)<a href="#form-repeater-{{ $repeater['key'] }}">{{ $repeater['label'] }}</a>@endforeach
        @foreach($definition['files'] as $file)<a href="#form-file-{{ $file['key'] }}">{{ $file['label'] }}</a>@endforeach
    </nav>
    @endif
    <form method="POST" enctype="multipart/form-data" action="{{ $formAction }}">
        @csrf
        @if($isEditing)@method('PUT')@endif
        @if($type === 'sk-01')
            @include('cwie.sk01-overlay')
        @elseif($hasPdfPositions)
            @include('cwie.sk-pdf-overlay')
        @else
        @foreach($definition['sections'] as $section)
            <section class="card form-section mb-3" id="form-section-{{ $loop->index }}"><div class="card-body">
                <h2 class="section-title mb-3">{{ $section['title'] }}</h2>
                <div class="row g-3">
                    @foreach($section['fields'] as $field)
                        @php($value = data_get($formData, $field['key'], ''))
                        <div class="{{ ($field['type'] ?? '') === 'textarea' ? 'col-12' : 'col-md-6' }}">
                            <label for="data_{{ $field['key'] }}" class="form-label">{{ $field['label'] }}{{ !empty($field['required']) ? ' *' : '' }}</label>
                            @if(($field['type'] ?? '') === 'select')
                                <select id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" class="form-select" {{ !empty($field['required']) ? 'required' : '' }}>
                                    <option value="">เลือก</option>
                                    @foreach($field['options'] as $option)<option value="{{ $option }}" {{ (string) $value === (string) $option ? 'selected' : '' }}>{{ $option }}</option>@endforeach
                                </select>
                            @elseif(($field['type'] ?? '') === 'checkboxes')
                                @php($checkedValues = is_array($value) ? array_map('strval', $value) : [])
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($field['options'] as $option)
                                        <label class="form-check"><input class="form-check-input" type="checkbox" name="data[{{ $field['key'] }}][]" value="{{ $option }}" {{ in_array((string) $option, $checkedValues, true) ? 'checked' : '' }}> <span class="form-check-label">{{ $option }}</span></label>
                                    @endforeach
                                </div>
                            @elseif(($field['type'] ?? '') === 'textarea')
                                <textarea id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" rows="3" class="form-control" {{ !empty($field['required']) ? 'required' : '' }}>{{ $value }}</textarea>
                            @else
                                <input id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" type="{{ $field['type'] ?? 'text' }}" class="form-control" value="{{ $value }}" {{ !empty($field['required']) ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div></section>
            @if($type === 'cwie-01' && $loop->iteration === 2)
                @php($photoFile = $definition['files'][0] ?? null)
                @if($photoFile)
                    @php($storedPath = $document->{$photoFile['column']})
                    <section class="card form-section mb-3 photo-upload-section" id="form-file-{{ $photoFile['key'] }}"><div class="card-body">
                        <div class="photo-upload-heading"><div><h2 class="section-title mb-1">รูปถ่ายนักศึกษา</h2><p class="small text-muted mb-3">รูปหน้าตรง ขนาด 1 นิ้ว · JPG, PNG หรือ WebP · ไม่เกิน 4 MB</p></div>@if($storedPath)<span class="badge bg-success">แนบรูปแล้ว</span>@endif</div>
                        <input type="file" id="{{ $photoFile['key'] }}" name="{{ $photoFile['key'] }}" class="image-upload visually-hidden" accept="image/jpeg,image/png,image/webp" aria-label="อัปโหลดรูปถ่ายนักศึกษา">
                        <label class="dropzone photo-dropzone" for="{{ $photoFile['key'] }}" data-dropzone="{{ $photoFile['key'] }}" tabindex="0">
                            <span><i data-lucide="cloud-upload"></i><strong class="d-block">{{ $storedPath ? 'เลือกรูปอื่น หรือวางรูปใหม่ที่นี่' : 'คลิกเพื่อเลือกรูป หรือวางไฟล์ที่นี่' }}</strong><small>รูปจะปรากฏในกรอบรูปของแบบฟอร์ม CWIE 01</small></span>
                        </label>
                        <img class="preview-image photo-upload-preview mt-3 {{ $storedPath ? '' : 'd-none' }}" data-preview="{{ $photoFile['key'] }}" src="{{ $storedPath ? asset('storage/'.$storedPath) : '' }}" alt="ตัวอย่างรูปถ่ายนักศึกษา CWIE 01">
                    </div></section>
                @endif
            @endif
        @endforeach
        @endif
        @if(!$hasPdfPositions)
        @foreach($definition['repeaters'] ?? [] as $repeater)
            @php($rows = data_get($formData, $repeater['key'], []))
            @if(empty($rows))@php($rows = [[]])@endif
            <section class="card form-section mb-3" id="form-repeater-{{ $repeater['key'] }}"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 section-title mb-0 flex-grow-1">{{ $repeater['label'] }}</h2><button type="button" class="btn btn-sm btn-outline-primary add-row" data-target="rows_{{ $repeater['key'] }}">เพิ่มแถว</button></div>
                <div class="table-responsive"><table class="table table-bordered align-middle repeater-table mb-0">
                    <thead><tr>@foreach($repeater['fields'] as $field)<th>{{ $field['label'] }}</th>@endforeach<th></th></tr></thead>
                    <tbody id="rows_{{ $repeater['key'] }}" data-next-index="{{ count($rows) }}">
                    @foreach($rows as $rowIndex => $row)
                        <tr class="repeat-row">
                            @foreach($repeater['fields'] as $field)
                                <td>
                                    @if(($field['type'] ?? '') === 'weeks')
                                        <div class="week-groups">
                                            @for($month = 1; $month <= 4; $month++)
                                                <fieldset class="week-month"><legend>เดือนที่ {{ $month }}</legend>
                                                    @for($weekInMonth = 1; $weekInMonth <= 4; $weekInMonth++)
                                                        @php($week = (($month - 1) * 4) + $weekInMonth)
                                                        <label><input type="checkbox" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}][]" value="{{ $week }}" {{ in_array((string) $week, array_map('strval', data_get($row, $field['key'], [])), true) ? 'checked' : '' }}> {{ $weekInMonth }}</label>
                                                    @endfor
                                                </fieldset>
                                            @endfor
                                        </div>
                                    @elseif(($field['type'] ?? '') === 'months')
                                        <div class="d-flex flex-wrap gap-2">
                                            @for($period = 1; $period <= 4; $period++)
                                                <label class="form-check form-check-inline mb-0"><input class="form-check-input" type="checkbox" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}][]" value="{{ $period }}" {{ in_array((string) $period, array_map('strval', data_get($row, $field['key'], [])), true) ? 'checked' : '' }}> เดือน {{ $period }}</label>
                                            @endfor
                                        </div>
                                    @elseif(($field['type'] ?? '') === 'select')
                                        <select class="form-select" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}]"><option value="">เลือก</option>@foreach($field['options'] as $option)<option value="{{ $option }}" {{ data_get($row, $field['key']) === $option ? 'selected' : '' }}>{{ $option }}</option>@endforeach</select>
                                    @else
                                        <input type="{{ in_array(($field['type'] ?? ''), ['date', 'time', 'number'], true) ? $field['type'] : 'text' }}" class="form-control" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}]" value="{{ data_get($row, $field['key'], '') }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="ลบแถว">ลบ</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                <template id="template_{{ $repeater['key'] }}"><tr class="repeat-row">
                    @foreach($repeater['fields'] as $field)
                        <td>
                            @if(($field['type'] ?? '') === 'weeks')
                                <div class="week-groups">@for($month = 1; $month <= 4; $month++)<fieldset class="week-month"><legend>เดือนที่ {{ $month }}</legend>@for($weekInMonth = 1; $weekInMonth <= 4; $weekInMonth++)@php($week = (($month - 1) * 4) + $weekInMonth)<label><input type="checkbox" name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}][]" value="{{ $week }}"> {{ $weekInMonth }}</label>@endfor</fieldset>@endfor</div>
                            @elseif(($field['type'] ?? '') === 'months')
                                <div class="d-flex flex-wrap gap-2">@for($period = 1; $period <= 4; $period++)<label class="form-check form-check-inline mb-0"><input class="form-check-input" type="checkbox" name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}][]" value="{{ $period }}"> เดือน {{ $period }}</label>@endfor</div>
                            @elseif(($field['type'] ?? '') === 'select')
                                <select class="form-select" name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}]"><option value="">เลือก</option>@foreach($field['options'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select>
                            @else
                                <input type="{{ in_array(($field['type'] ?? ''), ['date', 'time', 'number'], true) ? $field['type'] : 'text' }}" class="form-control" name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}]" {{ !empty($field['required']) ? 'required' : '' }}>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="ลบแถว">ลบ</button></td>
                </tr></template>
            </div></section>
        @endforeach
        @endif
        @foreach($definition['files'] as $file)
            @if($type !== 'cwie-01')
                @php($storedPath = $document->{$file['column']})
                <section class="card form-section mb-3" id="form-file-{{ $file['key'] }}"><div class="card-body">
                    <label for="{{ $file['key'] }}" class="form-label fw-semibold">{{ $file['label'] }}</label>
                    <input type="file" id="{{ $file['key'] }}" name="{{ $file['key'] }}" class="image-upload visually-hidden" accept="image/jpeg,image/png,image/webp" aria-label="อัปโหลด {{ $file['label'] }}">
                    <label class="dropzone" for="{{ $file['key'] }}" data-dropzone="{{ $file['key'] }}" tabindex="0">
                        <span><i data-lucide="cloud-upload"></i><strong class="d-block">วางรูปภาพที่นี่ หรือเลือกไฟล์</strong><small>JPG, PNG, WebP · ไม่เกิน 4 MB</small></span>
                    </label>
                    <img class="preview-image mt-3 {{ $storedPath ? '' : 'd-none' }}" data-preview="{{ $file['key'] }}" src="{{ $storedPath ? asset('storage/'.$storedPath) : '' }}" alt="ตัวอย่างรูปภาพ">
                </div></section>
            @endif
        @endforeach
        <div class="form-actions"><a href="{{ $isEditing ? route('student.cwie.show', [$type, $document]) : $backUrl }}" class="btn btn-outline-secondary">ยกเลิก</a><button type="submit" class="btn btn-primary"><i data-lucide="save" class="me-1" style="width:16px;height:16px"></i>{{ $role === 'advisor' ? 'ส่งคำขอให้เจ้าหน้าที่' : ($isEditing ? 'บันทึกการแก้ไข' : 'บันทึกเอกสาร') }}</button></div>
    </form>
    @endsection

    @push('styles')
    <style>.week-groups{display:grid;grid-template-columns:repeat(4,minmax(100px,1fr));gap:.45rem}.week-month{min-width:0;padding:.45rem;border:1px solid var(--slate-200);border-radius:9px}.week-month legend{float:none;width:auto;margin:0 0 .3rem;color:var(--slate-600);font-size:10px;font-weight:600}.week-month label{margin:0 5px 3px 0;color:var(--slate-600);font-size:10px}.repeater-table th{white-space:nowrap}.photo-upload-section{border-color:#cfe8d3}.photo-upload-section .card-body{background:linear-gradient(135deg,#fff,#f5fbf6)}.photo-upload-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}.photo-dropzone{min-height:125px;background:#fff}.photo-upload-preview{width:100px;height:120px;object-fit:cover;border:1px solid var(--slate-300);border-radius:4px}.sk-document-instructions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.sk-paper-wrap{position:relative;width:min(100%,850px);aspect-ratio:210/297;margin:0 auto;overflow:hidden;background:#fff;border:1px solid var(--slate-300);box-shadow:0 12px 30px rgba(15,23,42,.12)}.sk-paper-canvas,.sk-paper-fields{position:absolute;inset:0;width:100%;height:100%;border:0}.sk-paper-canvas{display:block}.sk-paper-fields{pointer-events:none}.sk-paper-fields[hidden]{display:none}.sk-paper-status{position:absolute;z-index:4;top:50%;left:50%;max-width:90%;padding:10px 14px;transform:translate(-50%,-50%);color:#334155;background:rgba(255,255,255,.94);border:1px solid #cbd5e1;border-radius:8px;text-align:center}.sk-paper-input{position:absolute;z-index:2;height:2.2%;min-height:11px;padding:0 2px;color:#111827;background:rgba(239,246,255,.78);border:0;border-bottom:1px solid #2563eb;border-radius:0;font:inherit;font-size:clamp(6px,1.05vw,11px);line-height:1.15;pointer-events:auto}.sk-paper-input:focus{z-index:3;background:#fff;outline:2px solid #2563eb;outline-offset:1px}.sk-paper-select{padding-right:15px;font-size:clamp(5px,.85vw,9px)}.sk-paper-radio{position:absolute;z-index:2;width:1.1%;height:1.1%;min-width:7px;min-height:7px;margin:0;appearance:none;background:transparent;border:0;transform:translate(-50%,-50%);pointer-events:auto;cursor:pointer}.sk-paper-radio:checked:after{position:absolute;inset:-45% 0 0;color:#1d4ed8;content:'X';font-size:clamp(7px,1.2vw,12px);font-weight:700;line-height:1;text-align:center}.sk-document-card .card-body{padding:clamp(8px,2vw,20px)}@media(max-width:700px){.week-groups{grid-template-columns:repeat(2,minmax(90px,1fr))}.sk-document-instructions{align-items:flex-start;flex-direction:column}.sk-paper-input{min-height:9px;padding:0 1px}.sk-paper-select{padding-right:8px}.sk-paper-radio{min-width:6px;min-height:6px}}</style>
    @endpush

    @push('scripts')
@if($hasPdfPositions)
<script src="{{ asset('js/pdf.min.js') }}"></script>
<script src="{{ asset('js/sk-form-pdf.js') }}"></script>
@endif
<script>
    document.querySelectorAll('.add-row').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.dataset.target;
            const tbody = document.getElementById(targetId);
            const template = document.getElementById('template_' + targetId.replace('rows_', ''));
            const rowIndex = Number(tbody.dataset.nextIndex);
            tbody.dataset.nextIndex = rowIndex + 1;
            tbody.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, rowIndex));
        });
    });
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row')) event.target.closest('.repeat-row').remove();
    });
    document.querySelectorAll('.image-upload').forEach(function (input) {
        const dropzone = document.querySelector('[data-dropzone="' + input.id + '"]');
        function showPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const preview = document.querySelector('[data-preview="' + input.id + '"]');
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        }
        input.addEventListener('change', function () {
            showPreview(input.files && input.files[0]);
        });
        ['dragenter', 'dragover'].forEach(function (eventName) { dropzone.addEventListener(eventName, function (event) { event.preventDefault(); dropzone.classList.add('dragover'); }); });
        ['dragleave', 'drop'].forEach(function (eventName) { dropzone.addEventListener(eventName, function (event) { event.preventDefault(); dropzone.classList.remove('dragover'); }); });
        dropzone.addEventListener('drop', function (event) {
            const file = event.dataTransfer.files && event.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const transfer = new DataTransfer(); transfer.items.add(file); input.files = transfer.files; showPreview(file);
        });
        dropzone.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); } });
    });
</script>
@endpush
