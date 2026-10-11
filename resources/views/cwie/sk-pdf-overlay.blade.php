@php($sourceUrl = route('public.forms.download', ['file' => $definition['source']]).'?inline=1')
<section class="card form-section sk-document-card mb-3">
    <div class="card-body">
        <div class="sk-document-instructions">
            <div><h2 class="section-title mb-1">กรอกข้อมูลลงในเอกสาร {{ strtoupper(str_replace('-', '.', $type)) }}</h2><p class="small text-muted mb-0">คลิกช่องบนเอกสารเพื่อกรอกข้อมูล ข้อมูลจะบันทึกพร้อมเอกสารต้นฉบับ</p></div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($definition['repeaters'] ?? [] as $repeater)
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-sk-row="{{ $repeater['key'] }}">เพิ่มแถว: {{ $repeater['label'] }}</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-remove-sk-row="{{ $repeater['key'] }}">ลบแถวล่าสุด</button>
                @endforeach
                <a href="{{ route('public.forms.download', ['file' => $definition['source']]).'?inline=1' }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">เปิดเอกสารต้นฉบับ</a>
            </div>
        </div>
        <label for="skPdfPage" class="visually-hidden">เลือกหน้าเอกสาร</label>
        <select id="skPdfPage" class="form-select form-select-sm mb-2" data-pdf-page style="max-width:140px">
            <option value="1">หน้า 1</option>
        </select>
        <div class="sk-paper-wrap" data-sk-pdf data-pdf-url="{{ $sourceUrl }}" data-pdf-worker="{{ asset('js/pdf.worker.min.js') }}">
            <canvas class="sk-paper-canvas" aria-label="เอกสารต้นฉบับแบบฟอร์ม {{ strtoupper(str_replace('-', '.', $type)) }}"></canvas>
            <p class="sk-paper-status" data-pdf-error role="status">กำลังโหลดเอกสารต้นฉบับ...</p>
            <div class="sk-paper-fields" hidden>
                @foreach($definition['sections'] as $section)
                    @foreach($section['fields'] as $field)
                        @continue(empty($field['position']))
                        @php($value = data_get($formData, $field['key'], ''))
                        @php($position = $field['position'])
                        @if(in_array(($field['type'] ?? ''), ['select', 'radio'], true))
                            <label class="visually-hidden" for="data_{{ $field['key'] }}">{{ $field['label'] }}</label>
                            <select id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" class="sk-paper-input sk-paper-select" data-field="{{ $field['key'] }}" data-page="{{ $position['page'] }}" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['input_width'] ?? $position['width'] }}%;{{ (!empty($position['input_center']) || (!empty($position['mark_option']) && empty($position['input_width']))) ? 'transform:translateX(-50%)' : '' }}" aria-label="{{ $field['label'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                <option value="">เลือก</option>
                                @foreach($field['options'] ?? [] as $option)<option value="{{ $option }}" {{ (string) $value === (string) $option ? 'selected' : '' }}>{{ $option }}</option>@endforeach
                            </select>
                        @elseif(($field['type'] ?? '') === 'textarea')
                            <label class="visually-hidden" for="data_{{ $field['key'] }}">{{ $field['label'] }}</label>
                            <textarea id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" rows="2" class="sk-paper-input sk-paper-textarea" data-field="{{ $field['key'] }}" data-page="{{ $position['page'] }}" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['width'] }}%" aria-label="{{ $field['label'] }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $value }}</textarea>
                        @else
                            <label class="visually-hidden" for="data_{{ $field['key'] }}">{{ $field['label'] }}</label>
                            <input id="data_{{ $field['key'] }}" name="data[{{ $field['key'] }}]" type="{{ in_array(($field['type'] ?? 'text'), ['text', 'number', 'date', 'time', 'email', 'tel'], true) ? $field['type'] : 'text' }}" class="sk-paper-input" data-field="{{ $field['key'] }}" data-page="{{ $position['page'] }}" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['width'] }}%" value="{{ is_array($value) ? implode(', ', array_map('strval', $value)) : $value }}" aria-label="{{ $field['label'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                        @endif
                    @endforeach
                @endforeach

                @foreach($definition['repeaters'] ?? [] as $repeater)
                    @php($rows = data_get($formData, $repeater['key'], []))
                    @if(empty($rows))@php($rows = [[]])@endif
                    <div data-sk-repeater="{{ $repeater['key'] }}" data-next-index="{{ count($rows) }}">
                        @foreach($rows as $rowIndex => $row)
                            @foreach($repeater['fields'] as $field)
                                @continue(empty($field['position']))
                                @php($position = $field['position'])
                                @php($top = $position['top'] + (($position['row_step'] ?? 2.8) * $rowIndex))
                                @php($value = data_get($row, $field['key'], ''))
                                @if(in_array(($field['type'] ?? ''), ['weeks', 'months', 'checkboxes'], true))
                                    @php($options = ($field['type'] ?? '') === 'weeks' ? range(1, 16) : (($field['type'] ?? '') === 'months' ? range(1, 4) : ($field['options'] ?? [])))
                                    @php($selectedValues = is_array($value) ? array_map('strval', $value) : [])
                                    @foreach($options as $optionIndex => $option)
                                        @php($left = $position['left'] + (($position['layout'] ?? 'horizontal') === 'horizontal' ? $position['width'] * $optionIndex : 0))
                                        @php($choiceTop = $top + (($position['layout'] ?? 'horizontal') === 'vertical' ? $position['width'] * $optionIndex : 0))
                                        <input id="data_{{ $repeater['key'] }}_{{ $rowIndex }}_{{ $field['key'] }}_{{ $optionIndex }}" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}][]" type="checkbox" value="{{ $option }}" class="sk-paper-radio sk-paper-row-input" data-field="{{ $repeater['key'] }}_{{ $field['key'] }}" data-page="{{ $position['page'] }}" data-top="{{ $position['top'] }}" data-row-step="{{ $position['row_step'] ?? 2.8 }}" style="left:{{ $left }}%;top:{{ $choiceTop }}%" aria-label="{{ $field['label'] }} {{ $option }} แถวที่ {{ $rowIndex + 1 }}" {{ in_array((string) $option, $selectedValues, true) ? 'checked' : '' }}>
                                    @endforeach
                                @else
                                    <label class="visually-hidden" for="data_{{ $repeater['key'] }}_{{ $rowIndex }}_{{ $field['key'] }}">{{ $field['label'] }}</label>
                                    <input id="data_{{ $repeater['key'] }}_{{ $rowIndex }}_{{ $field['key'] }}" name="data[{{ $repeater['key'] }}][{{ $rowIndex }}][{{ $field['key'] }}]" type="{{ in_array(($field['type'] ?? 'text'), ['text', 'number', 'date', 'time', 'email', 'tel'], true) ? $field['type'] : 'text' }}" class="sk-paper-input sk-paper-row-input" data-field="{{ $repeater['key'] }}_{{ $field['key'] }}" data-page="{{ $position['page'] }}" data-top="{{ $position['top'] }}" data-row-step="{{ $position['row_step'] ?? 2.8 }}" style="left:{{ $position['left'] }}%;top:{{ $top }}%;width:{{ $position['width'] }}%" value="{{ is_array($value) ? implode(', ', array_map('strval', $value)) : $value }}" aria-label="{{ $field['label'] }} แถวที่ {{ $rowIndex + 1 }}" {{ !empty($field['required']) ? 'required' : '' }}>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                    <template id="skRepeaterTemplate_{{ $repeater['key'] }}">
                        @foreach($repeater['fields'] as $field)
                            @if(!empty($field['position']))
                                @php($position = $field['position'])
                                @if(in_array(($field['type'] ?? ''), ['weeks', 'months', 'checkboxes'], true))
                                    @php($options = ($field['type'] ?? '') === 'weeks' ? range(1, 16) : (($field['type'] ?? '') === 'months' ? range(1, 4) : ($field['options'] ?? [])))
                                    @foreach($options as $optionIndex => $option)
                                        @php($left = $position['left'] + (($position['layout'] ?? 'horizontal') === 'horizontal' ? $position['width'] * $optionIndex : 0))
                                        @php($choiceTop = $position['top'] + (($position['layout'] ?? 'horizontal') === 'vertical' ? $position['width'] * $optionIndex : 0))
                                        <input name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}][]" type="checkbox" value="{{ $option }}" class="sk-paper-radio sk-paper-row-input" data-field="{{ $repeater['key'] }}_{{ $field['key'] }}" data-page="{{ $position['page'] }}" data-top="{{ $position['top'] }}" data-row-step="{{ $position['row_step'] ?? 2.8 }}" style="left:{{ $left }}%;top:{{ $choiceTop }}%" aria-label="{{ $field['label'] }} {{ $option }}">
                                    @endforeach
                                @else
                                    <input name="data[{{ $repeater['key'] }}][__INDEX__][{{ $field['key'] }}]" type="{{ in_array(($field['type'] ?? 'text'), ['text', 'number', 'date', 'time', 'email', 'tel'], true) ? $field['type'] : 'text' }}" class="sk-paper-input sk-paper-row-input" data-field="{{ $repeater['key'] }}_{{ $field['key'] }}" data-page="{{ $position['page'] }}" data-top="{{ $position['top'] }}" data-row-step="{{ $position['row_step'] ?? 2.8 }}" style="left:{{ $position['left'] }}%;top:{{ $position['top'] }}%;width:{{ $position['width'] }}%" aria-label="{{ $field['label'] }}">
                                @endif
                            @endif
                        @endforeach
                    </template>
                @endforeach
            </div>
        </div>
        <p class="small text-muted mt-2 mb-0">เลื่อนดูแต่ละหน้าได้จากรายการด้านบน ช่องกรอกจะคงตำแหน่งตามเอกสารเมื่อเปลี่ยนขนาดหน้าจอ</p>
    </div>
</section>

@push('styles')
<style>
    .sk-paper-textarea{height:5%;min-height:22px;resize:none}
    .sk-paper-row-input{height:2.2%;min-height:10px}
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-add-sk-row]').forEach(function (button) {
        button.addEventListener('click', function () {
            const key = button.dataset.addSkRow;
            const container = document.querySelector('[data-sk-repeater="' + key + '"]');
            const template = document.getElementById('skRepeaterTemplate_' + key);
            if (!container || !template) return;
            const index = Number(container.dataset.nextIndex) || 0;
            const html = template.innerHTML.replace(/__INDEX__/g, index);
            container.insertAdjacentHTML('beforeend', html);
            container.querySelectorAll('.sk-paper-row-input').forEach(function (field) {
                const match = field.name.match(/\[(\d+)\]/);
                if (!match || Number(match[1]) !== index) return;
                const top = Number(field.dataset.top) + (Number(field.dataset.rowStep) || 0) * index;
                field.style.top = top + '%';
                field.hidden = Number(field.dataset.page) !== Number(document.getElementById('skPdfPage').value);
            });
            container.dataset.nextIndex = index + 1;
        });
    });
    document.querySelectorAll('[data-remove-sk-row]').forEach(function (button) {
        button.addEventListener('click', function () {
            const container = document.querySelector('[data-sk-repeater="' + button.dataset.removeSkRow + '"]');
            if (!container) return;
            const indexes = Array.from(container.querySelectorAll('.sk-paper-row-input')).map(function (field) {
                const match = field.name.match(/\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            });
            const lastIndex = Math.max.apply(null, indexes);
            if (lastIndex < 0) return;
            container.querySelectorAll('.sk-paper-row-input').forEach(function (field) {
                const match = field.name.match(/\[(\d+)\]/);
                if (match && Number(match[1]) === lastIndex) field.remove();
            });
        });
    });
</script>
@endpush
