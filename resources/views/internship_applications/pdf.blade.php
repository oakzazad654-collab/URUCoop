<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบสมัครงานสหกิจศึกษา สก.03</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'thsarabunnew', sans-serif;
            background: #fff;
            color: #000;
            font-size: 10pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            line-height: 1.3;
        }

        .page {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header h2 {
            margin: 0;
            font-size: 20pt;
            font-weight: bold;
        }

        .header h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }

        table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9pt;
            text-align: left;
        }

        th {
            background: #f7f7f7;
            font-weight: bold;
            text-align: center;
        }

        .title {
            font-weight: bold;
            text-align: center;
            background: #f2f2f2;
        }

        .small {
            font-size: 8pt;
        }

        .bold { font-weight: bold; }
        .center { text-align: center; }
        .underline { text-decoration: underline; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h2>ใบสมัครงานสหกิจศึกษา</h2>
        <h3>มหาวิทยาลัยราชภัฏอุตรดิตถ์</h3>
        <div class="small">(สก.03)</div>
    </div>

    <table>
        <tr>
            <th colspan="4" class="title">1. ข้อมูลสถานประกอบการ</th>
        </tr>
        <tr>
            <td style="width: 20%;" class="bold">ชื่อสถานประกอบการ</td>
            <td style="width: 30%;">{{ $application->company_name }}</td>
            <td style="width: 20%;" class="bold">ระยะเวลาปฏิบัติงาน</td>
            <td style="width: 30%;">{{ $application->work_duration_months ?? '-' }} เดือน</td>
        </tr>
        <tr>
            <td class="bold">ที่อยู่</td>
            <td colspan="3">{{ $application->company_address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">เบอร์โทรศัพท์</td>
            <td>{{ $application->company_phone ?? '-' }}</td>
            <td class="bold">อีเมล</td>
            <td>{{ $application->company_email ?? '-' }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <th colspan="6" class="title">2. ข้อมูลส่วนตัวนักศึกษา</th>
        </tr>
        <tr>
            <td style="width: 12%;" class="bold">คำนำหน้า</td>
            <td style="width: 13%;">{{ $application->student_title_th ?? '-' }}</td>
            <td style="width: 15%;" class="bold">ชื่อ (ไทย)</td>
            <td style="width: 20%;">{{ $application->student_first_name_th ?? '-' }}</td>
            <td style="width: 15%;" class="bold">นามสกุล (ไทย)</td>
            <td style="width: 25%;">{{ $application->student_last_name_th ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">ชื่อ (EN)</td>
            <td>{{ $application->student_first_name_en ?? '-' }}</td>
            <td class="bold">นามสกุล (EN)</td>
            <td>{{ $application->student_last_name_en ?? '-' }}</td>
            <td class="bold">รหัสนักศึกษา</td>
            <td>{{ $application->student_id ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">คณะ</td>
            <td colspan="2">{{ $application->faculty ?? '-' }}</td>
            <td class="bold">สาขา</td>
            <td colspan="2">{{ $application->major ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">ชั้นปี</td>
            <td>{{ $application->year_level ?? '-' }}</td>
            <td class="bold">GPA</td>
            <td>{{ $application->gpa ?? '-' }}</td>
            <td class="bold">GPAX</td>
            <td>{{ $application->gpax ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">วันเกิด</td>
            <td>{{ $application->birth_date ? $application->birth_date->format('d/m/Y') : '-' }}</td>
            <td class="bold">สถานที่เกิด</td>
            <td colspan="3">{{ $application->birth_place ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">ที่อยู่</td>
            <td colspan="5">{{ $application->home_address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">เบอร์โทร</td>
            <td>{{ $application->phone ?? '-' }}</td>
            <td class="bold">อีเมล</td>
            <td>{{ $application->email ?? '-' }}</td>
            <td class="bold">ศาสนา</td>
            <td>{{ $application->religion ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">ส่วนสูง</td>
            <td>{{ $application->height ?? '-' }}</td>
            <td class="bold">น้ำหนัก</td>
            <td>{{ $application->weight ?? '-' }}</td>
            <td class="bold">กรุ๊ปเลือด</td>
            <td>{{ $application->blood_type ?? '-' }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <th colspan="3" class="title">3. บุคคลติดต่อฉุกเฉิน</th>
        </tr>
        <tr>
            <td style="width: 35%;" class="bold">ชื่อ-สกุล</td>
            <td style="width: 30%;" class="bold">ความสัมพันธ์</td>
            <td style="width: 35%;" class="bold">เบอร์โทร</td>
        </tr>
        <tr>
            <td>{{ $application->emergency_contact_name ?? '-' }}</td>
            <td>{{ $application->emergency_contact_relation ?? '-' }}</td>
            <td>{{ $application->emergency_contact_phone ?? '-' }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <th colspan="6" class="title">4. ข้อมูลครอบครัว</th>
        </tr>
        <tr>
            <td style="width: 20%;" class="bold">บิดา</td>
            <td style="width: 30%;">{{ $application->father_name ?? '-' }}</td>
            <td style="width: 20%;" class="bold">อาชีพ</td>
            <td style="width: 30%;">{{ $application->father_occupation ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">เบอร์โทร</td>
            <td>{{ $application->father_phone ?? '-' }}</td>
            <td class="bold">มารดา</td>
            <td>{{ $application->mother_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">อาชีพมารดา</td>
            <td>{{ $application->mother_occupation ?? '-' }}</td>
            <td class="bold">เบอร์โทรมารดา</td>
            <td>{{ $application->mother_phone ?? '-' }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <th colspan="2" class="title">5. ประวัติการศึกษา</th>
        </tr>
        <tr>
            <td style="width: 25%;" class="bold">รายการ</td>
            <td style="width: 75%;">{{ is_array($application->education_history) ? implode("<br>", $application->education_history) : '-' }}</td>
        </tr>
    </table>

    <table style="margin-top: 8px;">
        <tr>
            <th colspan="2" class="title">6. ความสามารถทางภาษา</th>
        </tr>
        <tr>
            <td style="width: 25%;" class="bold">ภาษา</td>
            <td style="width: 75%;">
                @if(is_array($application->language_skills) && count($application->language_skills) > 0)
                    @foreach($application->language_skills as $skill)
                        {{ $skill['language'] ?? '' }}: {{ $skill['level'] ?? '' }}<br>
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
