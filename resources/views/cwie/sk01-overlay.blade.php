@php($sourceUrl = route('public.forms.download', ['file' => $definition['source']]).'?inline=1')
<section class="card form-section sk-document-card mb-3">
    <div class="card-body">
        <div class="sk-document-instructions">
            <div><h2 class="section-title mb-1">กรอกข้อมูลลงในเอกสาร สก.01</h2><p class="small text-muted mb-0">คลิกช่องสีฟ้าบนเอกสารเพื่อกรอกข้อมูล ระบบจะบันทึกข้อมูลลงในแบบฟอร์มฉบับนี้</p></div>
            <a href="{{ route('public.forms.download', ['file' => $definition['source']]).'?inline=1' }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">เปิดเอกสารต้นฉบับ</a>
        </div>
        <div class="sk-paper-wrap" data-sk-pdf data-pdf-url="{{ $sourceUrl }}" data-pdf-worker="{{ asset('js/pdf.worker.min.js') }}">
            <canvas class="sk-paper-canvas" aria-label="เอกสารต้นฉบับแบบฟอร์ม สก.01"></canvas>
            <p class="sk-paper-status" data-pdf-error role="status">กำลังโหลดเอกสารต้นฉบับ...</p>
            <div class="sk-paper-fields" hidden>
                @foreach($definition['sections'] as $section)
                    @foreach($section['fields'] as $field)
                        @continue(empty($field['position']))
                        @php($value = data_get($formData, $field['key'], ''))
                        @php($position = $field['position'])
                        <label class="visually-hidden" for="data_{{ $field['key'] }}">{{ $field['label'] }}</label>
                        @if(($field['type'] ?? '') === 'radio')
                            @foreach($field['options'] as $optionIndex => $option)
                                @php($choiceLeft = ($position['left'] + ($position['layout'] === 'horizontal' ? $position['width'] * $optionIndex : 0)).'%')
                                @php($choiceTop = ($position['top'] + ($position['layout'] === 'vertical' ? $position['width'] * $optionIndex : 0)).'%')
                                <input type="radio" id="data_{{ $field['key'] }}_{{ $optionIndex }}" name="data[{{ $field['key'] }}]" value="{{ $option }}" class="sk-paper-radio" style="left:{{ $choiceLeft }};top:{{ $choiceTop }}" aria-label="{{ $field['label'] }}: {{ $option }}" {{ (string) $value === (string) $option ? 'checked' : '' }}>
                            @endforeach
                        @elseif(($field['type'] ?? '') === 'select')
                            <select id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" class="sk-paper-input sk-paper-select" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['width'] }}%" aria-label="{{ $field['label'] }}">
                                <option value="">เลือก</option>
                                @foreach($field['options'] as $option)<option value="{{ $option }}" {{ (string) $value === (string) $option ? 'selected' : '' }}>{{ $option }}</option>@endforeach
                            </select>
                        @else
                            <input id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" type="{{ in_array(($field['type'] ?? 'text'), ['text', 'number', 'date', 'email', 'tel'], true) ? $field['type'] : 'text' }}" class="sk-paper-input" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['width'] }}%" value="{{ $value }}" aria-label="{{ $field['label'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                        @endif
                    @endforeach
                @endforeach
                <input type="hidden" name="data[address]" value="{{ data_get($formData, 'address', '') }}">
                <input type="hidden" name="data[emergency_address]" value="{{ data_get($formData, 'emergency_address', '') }}">
                <input type="hidden" name="data[guardian_address]" value="{{ data_get($formData, 'guardian_address', '') }}">
            </div>
        </div>
        <p class="small text-muted mt-2 mb-0">เลื่อนดูเอกสารได้ทั้งหน้า ช่องกรอกจะปรับตำแหน่งตามขนาดหน้าจอ</p>
    </div>
</section>
