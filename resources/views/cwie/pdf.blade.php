<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'thsarabunnew', sans-serif; font-size: 10pt; line-height: 1.1; margin: 0; padding: 0; color: #111; }
        h1 { font-size: 15pt; text-align: center; margin: 1mm 0 2mm; }
        h2 { font-size: 10.5pt; margin: 3mm 0 1.5mm; }
        p { margin: 1mm 0 2mm; }
        table { width: 100% !important; table-layout: fixed !important; border-collapse: collapse; margin-bottom: 1.3mm; }
        td, th { border: 0.2mm solid #333; padding: 2px 4px; vertical-align: top; }
        th { font-weight: bold; text-align: center; }
        .field-label { width: 34%; }
        .header td { border: 0; padding: 0; }
        .code { text-align: right; }
        .intro { text-align: justify; }
        .weekly th, .weekly td { text-align: center; padding: .65mm 0; }
        .weekly .task { width: 42%; text-align: left; padding-left: 1mm; }
        .weekly .week { width: 3.1%; font-size: 8pt; }
        .weekly .number { width: 4%; }
        .approval td { padding: 1.6mm; }
        .signature-table { margin-top: 5mm; }
        .signature-table td { border: 0; padding: 2mm 1mm; text-align: center; }
        img { max-width: 100%; height: auto; }
        .student-photo { width: 100px; height: 120px; }
        .student-photo-placeholder { width: 100px; height: 120px; margin: 0 0 0 auto; border-collapse: collapse; }
        .student-photo-placeholder td { border: 0.2mm solid #333; text-align: center; vertical-align: middle; font-size: 9pt; line-height: 1.5; }
        .qr-code-img, .map-img { max-width: 150px; max-height: 150px; }
        .photo { width: 100px; height: 120px; }
        .map-image { max-width: 150px; max-height: 150px; }
        .center { text-align: center; }
        .note { font-size: 9pt; margin-top: 3mm; }
    </style>
</head>
<body>
    <table class="header"><tr>
        <td>มหาวิทยาลัยราชภัฏอุตรดิตถ์</td>
        <td class="code">แบบฟอร์ม {{ strtoupper(str_replace('-', ' ', $type)) }}<br>{{ $definition['role'] ?? 'สำหรับนักศึกษา' }}</td>
    </tr></table>
    <h1>{{ $definition['title'] }}</h1>
    @if(!empty($definition['intro']))<p class="intro">{{ $definition['intro'] }}</p>@endif
    @if($type === 'cwie-01')
        <table class="header"><tr><td></td><td class="code">
            @if(isset($imageAssets['student_photo']))
                <img class="student-photo" src="{{ $imageAssets['student_photo']['path'] }}" width="{{ $imageAssets['student_photo']['width'] }}" height="{{ $imageAssets['student_photo']['height'] }}" alt="รูปถ่ายนักศึกษา">
            @else
                <table class="student-photo-placeholder"><tr><td>รูปถ่าย<br>หน้าตรง<br>ขนาด 1 นิ้ว</td></tr></table>
            @endif
        </td></tr></table>
    @endif
    @foreach($definition['sections'] as $section)
        <h2>{{ $section['title'] }}</h2>
        <table><thead><tr><th class="field-label">รายการ</th><th>รายละเอียด</th></tr></thead><tbody>
            @foreach($section['fields'] as $field)
                @php($fieldValue = data_get($data, $field['key'], ''))
                <tr><td>{{ $field['label'] }}</td><td>
                    @if(($field['type'] ?? '') === 'checkboxes')
                        @foreach($field['options'] as $option){{ in_array($option, (array) $fieldValue, true) ? '[x] ' : '[ ] ' }}{{ $option }}@if(!$loop->last) &nbsp; @endif @endforeach
                    @elseif(($field['type'] ?? '') === 'select')
                        @if($fieldValue !== '')[x] {{ $fieldValue }}@else — @endif
                    @elseif(is_array($fieldValue))
                        {{ implode(', ', $fieldValue) ?: '—' }}
                    @else
                        {{ $fieldValue !== '' ? $fieldValue : '—' }}
                    @endif
                </td></tr>
            @endforeach
        </tbody></table>
    @endforeach
    @foreach($definition['repeaters'] ?? [] as $repeater)
        <h2>{{ $repeater['label'] }}</h2>
        @if(in_array($type, ['cwie-04', 'sk-08'], true) && $repeater['key'] === 'schedule')
            @php($scheduleRows = data_get($data, 'schedule', []))
            @php($printRows = max(12, count($scheduleRows)))
            <table class="weekly">
                <thead>
                    <tr><th class="number" rowspan="2">ลำดับที่</th><th class="task" rowspan="2">หัวข้องาน</th>@for($month = 1; $month <= 4; $month++)<th colspan="4">เดือนที่ {{ $month }}</th>@endfor</tr>
                    <tr>@for($week = 1; $week <= 16; $week++)<th class="week">{{ (($week - 1) % 4) + 1 }}</th>@endfor</tr>
                </thead>
                <tbody>
                    @for($rowIndex = 0; $rowIndex < $printRows; $rowIndex++)
                        @php($scheduleRow = $scheduleRows[$rowIndex] ?? [])
                        @php($selectedWeeks = array_map('intval', data_get($scheduleRow, 'weeks', [])))
                        <tr><td>{{ $rowIndex + 1 }}</td><td class="task">{{ data_get($scheduleRow, 'task', '') }}</td>@for($week = 1; $week <= 16; $week++)<td class="week">{{ in_array($week, $selectedWeeks, true) ? 'X' : '' }}</td>@endfor</tr>
                    @endfor
                </tbody>
            </table>
        @else
            <table><thead><tr>@foreach($repeater['fields'] as $field)<th>{{ $field['label'] }}</th>@endforeach</tr></thead><tbody>
                @php($rows = data_get($data, $repeater['key'], []))
                @php($rowCount = $type === 'cwie-06' ? max(3, count($rows)) : count($rows))
                @if($rowCount)
                    @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
                        @php($row = $rows[$rowIndex] ?? [])
                        <tr>@foreach($repeater['fields'] as $field)<td>{{ in_array(($field['type'] ?? ''), ['months', 'weeks'], true) ? implode(', ', data_get($row, $field['key'], [])) : data_get($row, $field['key'], '') }}</td>@endforeach</tr>
                    @endfor
                @else
                    <tr><td colspan="{{ count($repeater['fields']) }}">—</td></tr>
                @endif
            </tbody></table>
        @endif
    @endforeach
    @foreach($definition['files'] as $file)
        @if(isset($imageAssets[$file['key']]) && !($type === 'cwie-01' && $file['key'] === 'student_photo'))
            <h2>{{ $file['label'] }}</h2>
            <div class="center"><img class="{{ $file['key'] === 'student_photo' ? 'student-photo' : 'map-img' }}" src="{{ $imageAssets[$file['key']]['path'] }}" width="{{ $imageAssets[$file['key']]['width'] }}" height="{{ $imageAssets[$file['key']]['height'] }}" alt="{{ $file['label'] }}"></div>
        @endif
    @endforeach
    @if($type === 'cwie-02')
        <p>ข้าพเจ้าขอหนังสือขอความอนุเคราะห์เพื่อเข้าปฏิบัติงานสหกิจศึกษาตามรายละเอียดข้างต้น</p>
        <table class="signature-table"><tr><td>ลงชื่อ ................................................ นักศึกษา<br>(........................................................)<br>วันที่ ........../........../..........</td></tr></table>
        <p class="note">หมายเหตุ: เอกสารประกอบ ได้แก่ แบบฟอร์ม CWIE 01, ประวัติส่วนตัว, ใบแสดงผลการเรียน, สำเนาบัตรนักศึกษา และสำเนาบัตรประชาชน</p>
    @elseif($type === 'cwie-03')
        <h2>7. การรับรองข้อมูล</h2>
        <table class="signature-table"><tr><td>นักศึกษา<br>ลงชื่อ ........................................<br>(................................................)<br>วันที่ ........../........../..........</td><td>ผู้รับรองข้อมูล<br>ลงชื่อ ........................................<br>(................................................)<br>ตำแหน่ง ........................ วันที่ ........../........../..........</td></tr></table>
        <p class="note">หมายเหตุ: กรุณาแนบแผนที่หรือ QR Code Google Maps ของสถานที่ปฏิบัติงาน และส่งแบบฟอร์มภายในสัปดาห์แรกของการปฏิบัติงาน</p>
    @elseif($type === 'cwie-04')
        <table class="signature-table"><tr><td>ลงชื่อ ................................................ นักศึกษา<br>(................................................)<br>วันที่ ........................................</td><td>ลงชื่อ ................................................ ผู้นิเทศงาน (พี่เลี้ยง)<br>(................................................)<br>ตำแหน่ง ........................ วันที่ ................................</td></tr></table>
        <p class="note">หมายเหตุ: โปรดส่งคืนอาจารย์นิเทศหรือศูนย์พัฒนาคุณภาพการศึกษาบูรณาการกับการทำงานภายในสัปดาห์ที่ 2 ของการปฏิบัติงาน</p>
    @elseif($type === 'cwie-05')
        <table class="signature-table"><tr><td>ลงชื่อ ................................................ นักศึกษา<br>(................................................)<br>วันที่ ................................</td><td>ลงชื่อ ................................................ ผู้นิเทศงาน (พี่เลี้ยง)<br>(................................................)<br>ตำแหน่ง ........................ วันที่ ................................</td></tr></table>
        <p class="note">หมายเหตุ: โปรดส่งคืนศูนย์พัฒนาคุณภาพการศึกษาบูรณาการกับการทำงานภายในสัปดาห์ที่ 3 ของการปฏิบัติงาน</p>
    @elseif($type === 'cwie-06')
        <h2>4. การรับรองและการอนุมัติ</h2>
        <table class="approval"><tbody>
            <tr><td>ผู้ขออนุญาต</td><td>ลงชื่อ ................................................ วันที่ ................................</td></tr>
            <tr><td>อาจารย์นิเทศงานสหกิจศึกษา</td><td>ลงชื่อ ................................................ วันที่ ................................</td></tr>
            <tr><td>ประธานหลักสูตร</td><td>ลงชื่อ ................................................ วันที่ ................................</td></tr>
            <tr><td>คณบดี/รองคณบดี/ผู้ช่วยคณบดี (ผู้ได้รับมอบหมาย)</td><td>[ ] อนุญาต [ ] ไม่อนุญาต<br>ลงชื่อ ................................ วันที่ ................................</td></tr>
            <tr><td>ผู้อำนวยการศูนย์พัฒนาคุณภาพการศึกษาบูรณาการกับการทำงาน</td><td>ลงชื่อ ................................................ วันที่ ................................</td></tr>
        </tbody></table>
        <p class="note">หมายเหตุ: กรุณาส่งแบบคำขอล่วงหน้าอย่างน้อย 7 วันก่อนกำหนดออกนิเทศ</p>
    @else
        <table class="signature-table"><tr><td>ลงชื่อ ................................................ นักศึกษา<br>(................................................)<br>วันที่ ................................</td></tr></table>
    @endif
</body>
</html>
