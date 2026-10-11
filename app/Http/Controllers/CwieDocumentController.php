<?php

namespace App\Http\Controllers;

use App\Models\CwieDocument;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\CwieNotificationService;
use App\Services\DataAccessService;
use App\Services\SkFormCatalog;

class CwieDocumentController extends Controller
{
    private $access;
    private $skForms;

    public function __construct(DataAccessService $access, SkFormCatalog $skForms)
    {
        $this->access = $access;
        $this->skForms = $skForms;
    }

    private function definitions()
    {
        $definitions = [
            'cwie-01' => [
                'title' => 'แบบฟอร์มสมัครและพิจารณาคุณสมบัตินักศึกษาสหกิจศึกษา',
                'role' => 'สำหรับนักศึกษา',
                'sections' => [
                    ['title' => 'ส่วนที่ 1 ข้อมูลสถานประกอบการที่ต้องการสมัคร', 'fields' => [
                        ['key' => 'company_name', 'label' => 'ชื่อสถานประกอบการ', 'required' => true],
                        ['key' => 'company_address', 'label' => 'ที่อยู่', 'type' => 'textarea'], ['key' => 'company_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                        ['key' => 'work_period', 'label' => 'ระยะเวลาปฏิบัติงาน (รูปแบบเดิม)'],
                        ['key' => 'work_start', 'label' => 'วันที่เริ่มปฏิบัติงาน', 'type' => 'date'], ['key' => 'work_end', 'label' => 'วันที่สิ้นสุดปฏิบัติงาน', 'type' => 'date'],
                    ]],
                    ['title' => 'ส่วนที่ 2 ข้อมูลนักศึกษา: ข้อมูลทั่วไป', 'fields' => [
                        ['key' => 'student_name', 'label' => 'ชื่อ-สกุล (ไทย)', 'required' => true], ['key' => 'student_name_en', 'label' => 'Name (English)'],
                        ['key' => 'student_id', 'label' => 'รหัสนักศึกษา', 'required' => true], ['key' => 'major', 'label' => 'หลักสูตร/สาขาวิชา'],
                        ['key' => 'faculty', 'label' => 'คณะ'], ['key' => 'year_level', 'label' => 'ชั้นปี'], ['key' => 'advisor_name', 'label' => 'อาจารย์ที่ปรึกษา'],
                        ['key' => 'study_type', 'label' => 'ประเภทภาคการศึกษา', 'type' => 'select', 'options' => ['ภาคปกติ', 'ภาคพิเศษ']],
                        ['key' => 'credits_completed', 'label' => 'จำนวนหน่วยกิตที่สอบได้ปัจจุบัน', 'type' => 'number'],
                        ['key' => 'semester', 'label' => 'ภาคการศึกษาที่คาดว่าจะไปปฏิบัติงานสหกิจศึกษา', 'type' => 'number'],
                        ['key' => 'academic_year', 'label' => 'ปีการศึกษาที่คาดว่าจะไปปฏิบัติงานสหกิจศึกษา', 'type' => 'number'],
                        ['key' => 'gpa', 'label' => 'GPA ภาคล่าสุด', 'type' => 'number'], ['key' => 'gpax', 'label' => 'GPAX', 'type' => 'number'],
                    ]],
                    ['title' => 'ข้อมูลส่วนตัว', 'fields' => [
                        ['key' => 'birth_date', 'label' => 'วัน เดือน ปีเกิด', 'type' => 'date'], ['key' => 'age', 'label' => 'อายุ', 'type' => 'number'],
                        ['key' => 'gender', 'label' => 'เพศ', 'type' => 'select', 'options' => ['ชาย', 'หญิง', 'อื่นๆ']],
                        ['key' => 'citizen_id', 'label' => 'เลขบัตรประชาชน'], ['key' => 'nationality', 'label' => 'สัญชาติ'], ['key' => 'religion', 'label' => 'ศาสนา'],
                        ['key' => 'height', 'label' => 'ส่วนสูง (ซม.)', 'type' => 'number'], ['key' => 'weight', 'label' => 'น้ำหนัก (กก.)', 'type' => 'number'],
                        ['key' => 'chronic_disease', 'label' => 'โรคประจำตัว', 'type' => 'select', 'options' => ['ไม่มี', 'มี']], ['key' => 'chronic_disease_detail', 'label' => 'ระบุโรคประจำตัว'],
                        ['key' => 'current_address', 'label' => 'ที่อยู่ปัจจุบัน', 'type' => 'textarea'], ['key' => 'phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                        ['key' => 'email', 'label' => 'E-mail', 'type' => 'email'],
                    ]],
                    ['title' => 'บุคคลที่ติดต่อกรณีฉุกเฉิน', 'fields' => [
                        ['key' => 'emergency_name', 'label' => 'ชื่อ-สกุล'], ['key' => 'emergency_relation', 'label' => 'ความสัมพันธ์'],
                        ['key' => 'emergency_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'emergency_occupation', 'label' => 'อาชีพ'],
                    ]],
                    ['title' => 'ส่วนที่ 3 ความเห็นอาจารย์ที่ปรึกษา', 'fields' => [
                        ['key' => 'academic_readiness', 'label' => 'ความพร้อมทางวิชาการ', 'type' => 'select', 'options' => ['เหมาะสม', 'ไม่เหมาะสม']],
                        ['key' => 'conduct', 'label' => 'ความประพฤติ', 'type' => 'select', 'options' => ['เหมาะสม', 'ไม่เหมาะสม']],
                        ['key' => 'review_result', 'label' => 'ผลการพิจารณา', 'type' => 'select', 'options' => ['อนุมัติให้ไปปฏิบัติสหกิจศึกษา', 'รอพิจารณาในโอกาสต่อไป', 'อื่น ๆ']],
                        ['key' => 'review_note', 'label' => 'หมายเหตุ/ข้อเสนอแนะ', 'type' => 'textarea'], ['key' => 'reviewer_name', 'label' => 'อาจารย์ผู้ประสานงานรายวิชา', 'type' => 'text'],
                        ['key' => 'review_date', 'label' => 'วันที่พิจารณา', 'type' => 'date'],
                        ['key' => 'qualification_result', 'label' => 'ผลตรวจสอบคุณสมบัติ (ข้อมูลเดิม)', 'type' => 'textarea'],
                        ['key' => 'approval_note', 'label' => 'ความเห็นการพิจารณา (ข้อมูลเดิม)', 'type' => 'textarea'],
                    ]],
                ],
                'repeaters' => [
                    ['key' => 'education', 'label' => 'ประวัติการศึกษา', 'fields' => [
                        ['key' => 'level', 'label' => 'ระดับ', 'required' => true], ['key' => 'school', 'label' => 'สถานศึกษา'], ['key' => 'graduation_year', 'label' => 'ปีที่จบ'],
                    ]],
                    ['key' => 'languages', 'label' => 'ความสามารถทางภาษา', 'fields' => [
                        ['key' => 'language', 'label' => 'ภาษา', 'required' => true],
                        ['key' => 'listening', 'label' => 'ฟัง', 'type' => 'select', 'options' => ['ดี', 'พอใช้', 'น้อย']],
                        ['key' => 'speaking', 'label' => 'พูด', 'type' => 'select', 'options' => ['ดี', 'พอใช้', 'น้อย']],
                        ['key' => 'reading', 'label' => 'อ่าน', 'type' => 'select', 'options' => ['ดี', 'พอใช้', 'น้อย']],
                        ['key' => 'writing', 'label' => 'เขียน', 'type' => 'select', 'options' => ['ดี', 'พอใช้', 'น้อย']],
                    ]],
                ],
                'files' => [['key' => 'student_photo', 'label' => 'รูปถ่ายนักศึกษา 1 นิ้ว', 'column' => 'student_photo_path']],
            ],
            'cwie-02' => [
                'title' => 'คำร้องขอหนังสือขอความอนุเคราะห์สำหรับนักศึกษาสหกิจศึกษา',
                'role' => 'สำหรับนักศึกษา',
                'sections' => [
                    ['title' => '1. ข้อมูลนักศึกษา', 'fields' => [
                        ['key' => 'request_date', 'label' => 'วันที่ส่งแบบคำร้อง', 'type' => 'date'], ['key' => 'student_name', 'label' => 'ชื่อ-สกุล', 'required' => true],
                        ['key' => 'student_id', 'label' => 'รหัสนักศึกษา', 'required' => true], ['key' => 'major', 'label' => 'หลักสูตร/สาขาวิชา'],
                        ['key' => 'faculty', 'label' => 'คณะ/วิทยาลัย'], ['key' => 'student_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                        ['key' => 'student_email', 'label' => 'E-mail', 'type' => 'email'], ['key' => 'semester', 'label' => 'ภาคเรียน'], ['key' => 'academic_year', 'label' => 'ปีการศึกษา'],
                        ['key' => 'period_start', 'label' => 'เริ่มปฏิบัติงาน', 'type' => 'date'], ['key' => 'period_end', 'label' => 'สิ้นสุดปฏิบัติงาน', 'type' => 'date'],
                    ]],
                    ['title' => '2. ข้อมูลสถานประกอบการ', 'fields' => [
                        ['key' => 'company_name', 'label' => 'ชื่อสถานประกอบการ', 'required' => true], ['key' => 'company_address', 'label' => 'ที่อยู่', 'type' => 'textarea'],
                        ['key' => 'company_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'company_email', 'label' => 'E-mail', 'type' => 'email'],
                        ['key' => 'recipient', 'label' => 'เรียน (ชื่อผู้รับ/ตำแหน่ง)'],
                    ]],
                    ['title' => '4. อาจารย์ผู้ประสานงานรายวิชาสหกิจศึกษา', 'fields' => [
                        ['key' => 'coordinator_decision', 'label' => 'ผลการพิจารณา', 'type' => 'select', 'options' => ['เห็นชอบ', 'ไม่เห็นชอบ', 'อื่น ๆ']],
                        ['key' => 'coordinator_note', 'label' => 'รายละเอียดอื่น ๆ'], ['key' => 'coordinator_name', 'label' => 'ลงชื่อ'], ['key' => 'coordinator_date', 'label' => 'วันที่', 'type' => 'date'],
                    ]],
                    ['title' => '5. เจ้าหน้าที่ตรวจสอบเอกสาร', 'fields' => [
                        ['key' => 'document_review', 'label' => 'ผลตรวจเอกสาร', 'type' => 'select', 'options' => ['เอกสารครบถ้วน', 'เอกสารไม่ครบ']],
                        ['key' => 'missing_documents', 'label' => 'รายการเอกสารที่ไม่ครบ'], ['key' => 'staff_name', 'label' => 'ลงชื่อ'], ['key' => 'staff_date', 'label' => 'วันที่', 'type' => 'date'],
                    ]],
                    ['title' => '6. เอกสารประกอบ', 'fields' => [
                        ['key' => 'attachments', 'label' => 'รายการเอกสารที่แนบ', 'type' => 'checkboxes', 'options' => ['แบบฟอร์ม CWIE 01', 'ประวัติส่วนตัว', 'ใบแสดงผลการเรียน', 'สำเนาบัตรนักศึกษา', 'สำเนาบัตรประชาชน']],
                    ]],
                ],
                'repeaters' => [['key' => 'students', 'label' => 'รายชื่อนักศึกษาที่ขอร่วมกัน', 'fields' => [
                    ['key' => 'name', 'label' => 'ชื่อ-สกุล', 'required' => true], ['key' => 'student_id', 'label' => 'รหัสนักศึกษา'],
                    ['key' => 'major', 'label' => 'สาขาวิชา'], ['key' => 'faculty', 'label' => 'คณะ'], ['key' => 'signature', 'label' => 'ลายมือชื่อ'],
                ]]],
                'files' => [],
            ],
            'cwie-03' => [
                'title' => 'แบบฟอร์มข้อมูลสถานประกอบการสำหรับสหกิจศึกษา',
                'role' => 'สำหรับนักศึกษา',
                'sections' => [
                    ['title' => '1. ข้อมูลสถานประกอบการ', 'fields' => [
                        ['key' => 'company_name', 'label' => 'ชื่อสถานประกอบการ (ภาษาไทย)', 'required' => true], ['key' => 'company_name_en', 'label' => 'ชื่อสถานประกอบการ (ภาษาอังกฤษ)'],
                        ['key' => 'address_number', 'label' => 'เลขที่'], ['key' => 'address_village', 'label' => 'หมู่/ซอย'], ['key' => 'address_road', 'label' => 'ถนน'],
                        ['key' => 'address_subdistrict', 'label' => 'ตำบล/แขวง'], ['key' => 'address_district', 'label' => 'อำเภอ/เขต'],
                        ['key' => 'address_province', 'label' => 'จังหวัด'], ['key' => 'address_postcode', 'label' => 'รหัสไปรษณีย์'], ['key' => 'company_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                    ]],
                    ['title' => '2.1 ผู้จัดการ/ผู้บริหาร', 'fields' => [
                        ['key' => 'manager_name', 'label' => 'ชื่อ-นามสกุล'], ['key' => 'manager_position', 'label' => 'ตำแหน่ง'],
                        ['key' => 'manager_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'manager_email', 'label' => 'E-mail', 'type' => 'email'],
                        ['key' => 'coordination_type', 'label' => 'การประสานงานกับมหาวิทยาลัย', 'type' => 'select', 'options' => ['ติดต่อผู้จัดการโดยตรง', 'มอบหมายผู้ประสานงาน']],
                    ]],
                    ['title' => '2.2 ผู้ประสานงานสถานประกอบการ', 'fields' => [
                        ['key' => 'contact_name', 'label' => 'ชื่อ-นามสกุล'], ['key' => 'contact_position', 'label' => 'ตำแหน่ง'], ['key' => 'contact_department', 'label' => 'แผนก/ฝ่าย'],
                        ['key' => 'contact_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'contact_email', 'label' => 'E-mail', 'type' => 'email'],
                    ]],
                    ['title' => '3. พี่เลี้ยง/ผู้นิเทศงาน', 'fields' => [
                        ['key' => 'mentor_name', 'label' => 'ชื่อ-นามสกุล'], ['key' => 'mentor_position', 'label' => 'ตำแหน่ง'], ['key' => 'mentor_department', 'label' => 'แผนก/ฝ่าย'],
                        ['key' => 'mentor_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'mentor_email', 'label' => 'E-mail', 'type' => 'email'],
                    ]],
                    ['title' => '4. ข้อมูลนักศึกษาและงานที่ได้รับมอบหมาย', 'fields' => [
                        ['key' => 'student_name', 'label' => 'ชื่อ-นามสกุลนักศึกษา'], ['key' => 'student_id', 'label' => 'รหัสนักศึกษา'],
                        ['key' => 'student_major', 'label' => 'สาขาวิชา'], ['key' => 'student_faculty', 'label' => 'คณะ/วิทยาลัย'],
                        ['key' => 'job_position', 'label' => 'ตำแหน่งงาน (Job Position)'], ['key' => 'job_description', 'label' => 'ลักษณะงาน (Job Description)', 'type' => 'textarea'],
                    ]],
                    ['title' => '5. ผู้ติดต่อกรณีฉุกเฉิน', 'fields' => [
                        ['key' => 'emergency_name', 'label' => 'ชื่อ-นามสกุล'], ['key' => 'emergency_relation', 'label' => 'ความสัมพันธ์'],
                        ['key' => 'emergency_address', 'label' => 'ที่อยู่', 'type' => 'textarea'], ['key' => 'emergency_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                        ['key' => 'emergency_email', 'label' => 'E-mail', 'type' => 'email'],
                    ]],
                    ['title' => '6. แผนที่สถานประกอบการ', 'fields' => [
                        ['key' => 'map_url', 'label' => 'ลิงก์ Google Maps'], ['key' => 'travel_direction', 'label' => 'เส้นทางการเดินทางโดยย่อ', 'type' => 'textarea'],
                        ['key' => 'landmark', 'label' => 'จุดสังเกตสำคัญ'], ['key' => 'latitude', 'label' => 'ละติจูด', 'type' => 'number'], ['key' => 'longitude', 'label' => 'ลองจิจูด', 'type' => 'number'],
                    ]],
                ],
                'files' => [['key' => 'map_image', 'label' => 'ภาพแผนที่/QR Code Google Maps', 'column' => 'map_image_path']],
            ],
            'cwie-04' => [
                'title' => 'แจ้งแบบแผนปฏิบัติงานสหกิจศึกษา',
                'role' => 'สำหรับนักศึกษา',
                'sections' => [
                    ['title' => '1. ข้อมูลนักศึกษา', 'fields' => [
                        ['key' => 'student_name', 'label' => 'ชื่อ-นามสกุล', 'required' => true], ['key' => 'student_id', 'label' => 'รหัสนักศึกษา', 'required' => true],
                        ['key' => 'major', 'label' => 'สาขาวิชา'], ['key' => 'faculty', 'label' => 'คณะ/วิทยาลัย'],
                    ]],
                    ['title' => '2. ข้อมูลการปฏิบัติงาน', 'fields' => [
                        ['key' => 'company_name', 'label' => 'สถานประกอบการ'], ['key' => 'semester', 'label' => 'ภาคการศึกษาที่'], ['key' => 'academic_year', 'label' => 'ปีการศึกษา'],
                        ['key' => 'supervisor_name', 'label' => 'ผู้นิเทศงาน/พี่เลี้ยง'],
                    ]],
                ],
                'repeaters' => [['key' => 'schedule', 'label' => 'แผนปฏิบัติงาน 4 เดือน (เลือกสัปดาห์ที่ดำเนินงาน)', 'fields' => [
                    ['key' => 'task', 'label' => 'หัวข้องาน', 'required' => true], ['key' => 'weeks', 'label' => 'ช่วงเวลา (16 สัปดาห์)', 'type' => 'weeks'],
                ]]],
                'files' => [],
            ],
            'cwie-05' => [
                'title' => 'แบบแจ้งโครงร่างรายงานการปฏิบัติงานสหกิจศึกษา',
                'role' => 'สำหรับนักศึกษา',
                'intro' => 'รายงานนี้เป็นส่วนหนึ่งของการปฏิบัติงานสหกิจศึกษา นักศึกษาควรปรึกษาผู้นิเทศงานเพื่อกำหนดหัวข้อรายงานที่เหมาะสม และส่งโครงร่างภายในสัปดาห์ที่ 3 ของการปฏิบัติงาน',
                'sections' => [
                    ['title' => 'ข้อมูลนักศึกษาและสถานประกอบการ', 'fields' => [
                        ['key' => 'student_name', 'label' => 'ชื่อ-นามสกุล', 'required' => true], ['key' => 'student_id', 'label' => 'รหัสนักศึกษา', 'required' => true],
                        ['key' => 'major', 'label' => 'สาขาวิชา'], ['key' => 'faculty', 'label' => 'คณะ/วิทยาลัย'],
                        ['key' => 'study_type', 'label' => 'ประเภทการศึกษา', 'type' => 'select', 'options' => ['ปกติ', 'พิเศษ']],
                        ['key' => 'semester', 'label' => 'ภาคการศึกษา'], ['key' => 'academic_year', 'label' => 'ปีการศึกษา'],
                        ['key' => 'company_name', 'label' => 'สถานประกอบการ'], ['key' => 'address_number', 'label' => 'เลขที่'], ['key' => 'address_soi', 'label' => 'ซอย'],
                        ['key' => 'address_road', 'label' => 'ถนน'], ['key' => 'address_subdistrict', 'label' => 'ตำบล/แขวง'], ['key' => 'address_district', 'label' => 'อำเภอ/เขต'],
                        ['key' => 'address_province', 'label' => 'จังหวัด'], ['key' => 'address_postcode', 'label' => 'รหัสไปรษณีย์'],
                        ['key' => 'company_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'], ['key' => 'company_fax', 'label' => 'โทรสาร'], ['key' => 'company_email', 'label' => 'E-mail', 'type' => 'email'],
                    ]],
                    ['title' => '1. หัวข้อรายงาน (Report Title)', 'fields' => [
                        ['key' => 'topic_th', 'label' => 'ภาษาไทย', 'required' => true], ['key' => 'topic_en', 'label' => 'ภาษาอังกฤษ'],
                    ]],
                    ['title' => '2. รายละเอียดเนื้อหาของรายงาน', 'fields' => [
                        ['key' => 'report_content', 'label' => 'รายละเอียดเนื้อหา (เปลี่ยนแปลงเพิ่มเติมได้ภายหลัง)', 'type' => 'textarea'],
                    ]],
                    ['title' => 'ผู้เกี่ยวข้อง', 'fields' => [
                        ['key' => 'supervisor_name', 'label' => 'ผู้นิเทศงาน (พี่เลี้ยง)'], ['key' => 'advisor_name', 'label' => 'อาจารย์ที่ปรึกษาสหกิจศึกษา'],
                    ]],
                ],
                'files' => [],
            ],
            'cwie-06' => [
                'title' => 'แบบฟอร์มขออนุญาตการออกนิเทศงานสหกิจศึกษา',
                'role' => 'สำหรับอาจารย์นิเทศงานสหกิจศึกษา',
                'intro' => 'เรื่อง ขออนุญาตออกนิเทศงานสหกิจศึกษาของนักศึกษา',
                'sections' => [['title' => '1. ข้อมูลผู้ขออนุญาต', 'fields' => [
                    ['key' => 'request_date', 'label' => 'วันที่ขออนุญาต', 'type' => 'date'], ['key' => 'dean_name', 'label' => 'เรียน คณบดีคณะ/วิทยาลัย'],
                    ['key' => 'advisor_name', 'label' => 'ชื่อ-นามสกุลอาจารย์นิเทศ', 'required' => true], ['key' => 'advisor_position', 'label' => 'ตำแหน่ง'],
                    ['key' => 'major', 'label' => 'สาขาวิชา'], ['key' => 'faculty', 'label' => 'คณะ/วิทยาลัย'], ['key' => 'advisor_phone', 'label' => 'โทรศัพท์', 'type' => 'tel'],
                    ['key' => 'semester', 'label' => 'ภาคการศึกษาที่'], ['key' => 'academic_year', 'label' => 'ปีการศึกษา'],
                    ['key' => 'approval_decision', 'label' => 'ผลการอนุมัติ', 'type' => 'select', 'options' => ['อนุญาต', 'ไม่อนุญาต']],
                ]]],
                'repeaters' => [
                    ['key' => 'visiting_team', 'label' => '2. คณะผู้ออกนิเทศ', 'fields' => [
                        ['key' => 'name', 'label' => 'ชื่อ-นามสกุล', 'required' => true], ['key' => 'position', 'label' => 'ตำแหน่ง'],
                    ]],
                    ['key' => 'visits', 'label' => '3. กำหนดการออกนิเทศ', 'fields' => [
                        ['key' => 'company_name', 'label' => 'สถานประกอบการ', 'required' => true], ['key' => 'province', 'label' => 'จังหวัด'],
                        ['key' => 'visit_date', 'label' => 'วันที่', 'type' => 'date'], ['key' => 'visit_time', 'label' => 'เวลา', 'type' => 'time'],
                    ]],
                ],
                'files' => [],
            ],
        ];

        return array_merge($definitions, $this->skForms->all());
    }

    private function definition($type)
    {
        abort_unless(isset($this->definitions()[$type]), 404);
        return $this->definitions()[$type];
    }

    public function definitionForApproval($type)
    {
        return $this->definition($type);
    }

    private function authorizeDocument(CwieDocument $document, $forEditing = false)
    {
        $user = request()->user();
        if ($user->role === 'student') {
            abort_unless((int) $document->user_id === (int) $user->id, 403);
            if ($forEditing) abort_unless(in_array($document->status, ['draft', 'revision_requested'], true), 403, 'เอกสารที่ส่งพิจารณาแล้วไม่สามารถแก้ไขได้');
            return;
        }

        abort_unless(in_array($user->role, ['advisor', 'officer', 'admin'], true) && !$forEditing, 403);
        if ($user->role === 'advisor') {
            if ($document->type === 'cwie-06') {
                abort_unless((int) $document->user_id === (int) $user->id, 403);
            } else {
                abort_unless($document->owner && (int) $document->owner->advisor_id === (int) $user->id
                    && $this->access->canAccessLegacyStudent($user, $document->owner), 403);
            }
        } elseif ($user->role === 'officer' && !$this->access->isGlobalOperator($user)) {
            if ($document->type === 'cwie-06') {
                abort_unless($document->owner && (int) $document->owner->faculty_id === (int) $this->access->facultyId($user), 403);
            } else {
                abort_unless($this->access->legacyStudents($user)->whereKey($document->user_id)->exists(), 403);
            }
        }
    }

    private function routeName($base)
    {
        $role = request()->user()->role;
        if ($role === 'student') return 'student.'.$base;
        if ($role === 'advisor') return 'advisor.'.$base;
        if ($role === 'officer') return 'officer.'.$base;
        return 'admin.'.$base;
    }

    public function officerDocuments(Request $request)
    {
        abort_unless($request->user()->role === 'officer', 403);
        $query = CwieDocument::with(['owner.faculty', 'advisor', 'officer'])->latest();
        if (!$this->access->isGlobalOperator($request->user())) {
            $query->whereIn('user_id', $this->access->legacyStudents($request->user())->select('id'));
        }
        if ($request->filled('faculty_id')) {
            $query->whereHas('owner', function ($ownerQuery) use ($request) {
                $ownerQuery->where('faculty_id', $request->input('faculty_id'));
            });
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $documents = $query->paginate(30)->withQueryString();
        $faculties = \App\Models\Faculty::orderBy('name')->get();
        return view('officer.documents', compact('documents', 'faculties'));
    }

    private function checkType($type, CwieDocument $document)
    {
        abort_unless($document->type === $type, 404);
    }

    private function validationRules($definition)
    {
        $rules = ['data' => 'nullable|array'];
        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fieldRules = [!empty($field['required']) ? 'required' : 'nullable'];
                $type = $field['type'] ?? 'text';
                if ($type === 'number') $fieldRules = array_merge($fieldRules, ['numeric', 'min:0']);
                elseif ($type === 'email') $fieldRules[] = 'email';
                elseif ($type === 'date') $fieldRules[] = 'date';
                elseif ($type === 'checkboxes') $fieldRules[] = 'array';
                elseif (in_array($type, ['select', 'radio'], true)) $fieldRules[] = 'in:'.implode(',', $field['options']);
                else $fieldRules[] = 'string';
                $rules['data.'.$field['key']] = implode('|', $fieldRules);
                if ($type === 'checkboxes') $rules['data.'.$field['key'].'.*'] = 'string|in:'.implode(',', $field['options']);
            }
        }
        foreach ($definition['repeaters'] ?? [] as $repeater) {
            $requiresRows = count(array_filter($repeater['fields'], function ($field) { return !empty($field['required']); })) > 0;
            $rules['data.'.$repeater['key']] = $requiresRows ? 'required|array|min:1' : 'nullable|array';
            foreach ($repeater['fields'] as $field) {
                $fieldRequired = !empty($field['required']) ? 'required' : 'nullable';
                $type = $field['type'] ?? 'text';
                $fieldPath = 'data.'.$repeater['key'].'.*.'.$field['key'];
                if (in_array($type, ['months', 'weeks'], true)) {
                    $rules[$fieldPath] = 'nullable|array';
                    $max = $type === 'weeks' ? 16 : 4;
                    $rules[$fieldPath.'.*'] = 'integer|between:1,'.$max;
                } elseif ($type === 'select') {
                    $rules[$fieldPath] = $fieldRequired.'|in:'.implode(',', $field['options']);
                } elseif ($type === 'number') {
                    $rules[$fieldPath] = $fieldRequired.'|numeric|min:0';
                } elseif ($type === 'date') {
                    $rules[$fieldPath] = $fieldRequired.'|date';
                } else {
                    $rules[$fieldPath] = $fieldRequired.'|string|max:2000';
                }
            }
        }
        foreach ($definition['files'] as $file) $rules[$file['key']] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096';
        return $rules;
    }

    private function filteredData($input, $definition)
    {
        $input = is_array($input) ? $input : [];
        $data = [];
        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $key = $field['key'];
                if (array_key_exists($key, $input)) $data[$key] = $input[$key];
            }
        }
        foreach ($definition['repeaters'] ?? [] as $repeater) {
            $key = $repeater['key'];
            $data[$key] = [];
            foreach (($input[$key] ?? []) as $row) {
                if (!is_array($row)) continue;
                $filteredRow = [];
                foreach ($repeater['fields'] as $field) {
                    if (array_key_exists($field['key'], $row)) $filteredRow[$field['key']] = $row[$field['key']];
                }
                if (array_filter($filteredRow, function ($value) { return $value !== null && $value !== '' && $value !== []; })) $data[$key][] = $filteredRow;
            }
        }
        return $data;
    }

    private function saveUploads(Request $request, CwieDocument $document, $definition)
    {
        foreach ($definition['files'] as $file) {
            if (!$request->hasFile($file['key'])) continue;
            $column = $file['column'];
            $newPath = $request->file($file['key'])->store('cwie/'.$document->type, 'public');
            if ($document->$column) Storage::disk('public')->delete($document->$column);
            $document->$column = $newPath;
        }
    }

    public function index(Request $request)
    {
        $types = $this->skForms->all();
        $query = CwieDocument::whereIn('type', array_keys($types))->latest();
        if ($request->user()->role === 'student') {
            $query->where('user_id', $request->user()->id);
        } elseif ($request->user()->role === 'advisor') {
            $visibleStudentIds = $this->access->legacyStudents($request->user())->select('id');
            $query->where('status', 'pending_advisor')
                ->whereHas('owner', function ($query) use ($request) {
                    $query->where('advisor_id', $request->user()->id);
                })->whereIn('user_id', $visibleStudentIds)->where(function ($query) use ($request) {
                    $query->whereNull('advisor_id')->orWhere('advisor_id', $request->user()->id);
            });
        } elseif ($request->user()->role === 'officer') {
            $visibleStudentIds = $this->access->legacyStudents($request->user())->select('id');
            $query->where('status', 'pending_officer')->where(function ($query) use ($request) {
                $query->whereNull('officer_id')->orWhere('officer_id', $request->user()->id);
            })->whereIn('user_id', $visibleStudentIds);
        } else {
            $query->whereIn('status', ['pending_advisor', 'pending_officer']);
        }
        if ($request->filled('type') && isset($types[$request->type])) $query->where('type', $request->type);
        $documents = $query->get();
        return view('cwie.index', compact('types', 'documents'));
    }

    public function create($type)
    {
        abort_unless($type !== 'cwie-06', 404);
        $definition = $this->definition($type);
        $document = new CwieDocument(['type' => $type, 'data' => []]);
        return view('cwie.form', compact('type', 'definition', 'document'));
    }

    public function store(Request $request, $type)
    {
        $definition = $this->definition($type);
        $request->validate($this->validationRules($definition));
        $data = $this->filteredData($request->input('data', []), $definition);
        $document = CwieDocument::create(['type' => $type, 'title' => $definition['title'], 'user_id' => $request->user()->id, 'status' => 'draft', 'student_id' => $data['student_id'] ?? null, 'data' => $data]);
        $this->saveUploads($request, $document, $definition);
        $document->save();
        return redirect()->route($this->routeName('cwie.show'), [$type, $document])->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function createAdvisorCwie06()
    {
        $type = 'cwie-06';
        $definition = $this->definition($type);
        $document = new CwieDocument(['type' => $type, 'data' => []]);
        return view('cwie.form', compact('type', 'definition', 'document'));
    }

    public function storeAdvisorCwie06(Request $request)
    {
        $type = 'cwie-06';
        $definition = $this->definition($type);
        $request->validate($this->validationRules($definition));
        $data = $this->filteredData($request->input('data', []), $definition);
        $document = CwieDocument::create([
            'type' => $type,
            'title' => $definition['title'],
            'user_id' => $request->user()->id,
            'advisor_id' => $request->user()->id,
            'status' => 'pending_officer',
            'student_id' => $data['student_id'] ?? null,
            'data' => $data,
        ]);
        ApprovalHistory::create([
            'document_type' => $type,
            'document_id' => $document->id,
            'action_by' => $request->user()->id,
            'action_type' => 'advisor_submitted',
            'comment' => null,
        ]);
        app(CwieNotificationService::class)->documentSubmitted($document->fresh(['owner.advisor']));
        return redirect()->route('advisor.cwie06.show', $document)->with('success', 'ส่งคำขอ CWIE 06 ให้เจ้าหน้าที่พิจารณาแล้ว');
    }

    public function showAdvisorCwie06(CwieDocument $document)
    {
        $this->checkType('cwie-06', $document);
        $this->authorizeDocument($document);
        $type = 'cwie-06';
        $definition = $this->definition($type);
        return view('cwie.show', compact('type', 'definition', 'document'));
    }

    public function pdfAdvisorCwie06(Request $request, CwieDocument $document)
    {
        return $this->pdf($request, 'cwie-06', $document);
    }

    public function show($type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        abort_unless($type !== 'cwie-06', 404);
        $this->authorizeDocument($document);
        $definition = $this->definition($type);
        return view('cwie.show', compact('type', 'definition', 'document'));
    }

    public function edit($type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        $this->authorizeDocument($document, true);
        $definition = $this->definition($type);
        return view('cwie.form', compact('type', 'definition', 'document'));
    }

    public function update(Request $request, $type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        $this->authorizeDocument($document, true);
        $definition = $this->definition($type);
        $request->validate($this->validationRules($definition));
        $data = $this->filteredData($request->input('data', []), $definition);
        $document->data = $data;
        $document->student_id = $data['student_id'] ?? null;
        $this->saveUploads($request, $document, $definition);
        $document->save();
        return redirect()->route($this->routeName('cwie.show'), [$type, $document])->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        $this->authorizeDocument($document, true);
        abort_unless($document->status === 'draft', 403, 'ส่งเอกสารพิจารณาแล้วจึงไม่สามารถลบได้');
        foreach (['student_photo_path', 'map_image_path'] as $column) if ($document->$column) Storage::disk('public')->delete($document->$column);
        $document->delete();
        return redirect()->route($this->routeName('cwie.index'))->with('success', 'ลบเอกสารเรียบร้อยแล้ว');
    }

    public function pdf(Request $request, $type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        $this->authorizeDocument($document);
        $definition = $this->definition($type);
        $data = $document->data ?: [];
        $imageAssets = [];
        foreach ($definition['files'] as $file) {
            $storedPath = $document->{$file['column']};
            if (!$storedPath) continue;

            $imagePath = public_path('storage/'.str_replace('\\', '/', $storedPath));
            if (!file_exists($imagePath)) $imagePath = Storage::disk('public')->path($storedPath);
            if (!file_exists($imagePath)) continue;

            $dimensions = @getimagesize($imagePath);
            if (!$dimensions) continue;
            $width = (int) $dimensions[0];
            $height = (int) $dimensions[1];
            if ($file['key'] === 'student_photo') {
                $width = 100;
                $height = 120;
            } else {
                $scale = min(1, 150 / max($width, 1), 150 / max($height, 1));
                $width = max(1, (int) round($width * $scale));
                $height = max(1, (int) round($height * $scale));
            }
            $imageAssets[$file['key']] = ['path' => $imagePath, 'width' => $width, 'height' => $height];
        }

        $html = view('cwie.pdf', compact('type', 'definition', 'document', 'data', 'imageAssets'))->render();
        $fontDir = public_path('fonts');
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => sys_get_temp_dir(),
        ];
        if (file_exists($fontDir.'/THSarabunNew.ttf')) {
            $config['fontDir'] = [$fontDir];
            $config['default_font'] = 'thsarabunnew';
            $config['fontdata'] = ['thsarabunnew' => ['R' => 'THSarabunNew.ttf', 'B' => 'THSarabunNew Bold.ttf', 'I' => 'THSarabunNew Italic.ttf', 'BI' => 'THSarabunNew BoldItalic.ttf']];
        }
        $mpdf = new \Mpdf\Mpdf($config);
        $hasPositionedSkPdf = strpos($type, 'sk-') === 0
            && ($type === 'sk-01' || !empty($definition['positions_configured']));
        if ($hasPositionedSkPdf) {
            $sourcePath = base_path('docs'.DIRECTORY_SEPARATOR.'คู่มือต่างๆ'.DIRECTORY_SEPARATOR.$definition['source']);
            if (!is_file($sourcePath) && $type === 'sk-12.1') {
                $sourcePath = public_path('forms'.DIRECTORY_SEPARATOR.$definition['source']);
            }
            $sourceContents = file_get_contents($sourcePath);
            if ($sourceContents === false) {
                throw new \RuntimeException('Unable to read the original SK form PDF.');
            }

            $sourceReader = \setasign\Fpdi\PdfParser\StreamReader::createByString($sourceContents);
            $pageCount = $mpdf->setSourceFile($sourceReader);
            $mpdf->SetFont($config['default_font'], '', 10);

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                if ($pageNumber > 1) {
                    $mpdf->AddPage();
                }
                $template = $mpdf->importPage($pageNumber);
                $pageSize = $mpdf->useTemplate($template, 0, 0, null, null, true);
                $pageWidth = (float) $pageSize['width'];
                $pageHeight = (float) $pageSize['height'];

                foreach ($definition['sections'] as $section) {
                    foreach ($section['fields'] as $field) {
                        $position = $field['position'] ?? null;
                        if (!$position || (int) ($position['page'] ?? 1) !== $pageNumber) {
                            continue;
                        }

                        $value = data_get($data, $field['key']);
                        $this->writeSkFieldValue($mpdf, $field, $position, $value, 0, $pageWidth, $pageHeight);
                    }
                }

                foreach ($definition['repeaters'] ?? [] as $repeater) {
                    $rows = data_get($data, $repeater['key'], []);
                    if (!is_array($rows)) {
                        continue;
                    }

                    foreach ($rows as $rowIndex => $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        foreach ($repeater['fields'] as $field) {
                            $position = $field['position'] ?? null;
                            if (!$position || (int) ($position['page'] ?? 1) !== $pageNumber) {
                                continue;
                            }
                            $this->writeSkFieldValue($mpdf, $field, $position, data_get($row, $field['key']), (int) $rowIndex, $pageWidth, $pageHeight);
                        }
                    }
                }
            }
        } else {
            $mpdf->WriteHTML($html);
        }
        $output = $mpdf->Output('', 'S');
        $filename = strpos($type, 'sk-') === 0
            ? strtoupper($type).'-'.$document->id.'.pdf'
            : $type.'-'.$document->id.'.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        return response($output)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Length', (string) strlen($output))
            ->header('Content-Disposition', $disposition.'; filename="'.$filename.'"')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function writeSkFieldValue($mpdf, $field, $position, $value, $rowIndex, $pageWidth, $pageHeight)
    {
        $type = $field['type'] ?? '';
        if (in_array($type, ['weeks', 'months', 'checkboxes'], true)) {
            $selectedValues = is_array($value) ? array_map('strval', $value) : [];
            $options = $type === 'weeks'
                ? range(1, 16)
                : ($type === 'months' ? range(1, 4) : ($field['options'] ?? []));
            $left = (float) $position['left'];
            $top = (float) $position['top'] + ((float) ($position['row_step'] ?? 0) * $rowIndex);
            foreach ($options as $optionIndex => $option) {
                if (!in_array((string) $option, $selectedValues, true)) {
                    continue;
                }
                $optionLeft = $left + (($position['layout'] ?? 'horizontal') === 'horizontal' ? (float) $position['width'] * $optionIndex : 0);
                $optionTop = $top + (($position['layout'] ?? 'horizontal') === 'vertical' ? (float) $position['width'] * $optionIndex : 0);
                $mpdf->SetXY(($optionLeft * $pageWidth / 100) - 1, ($optionTop * $pageHeight / 100) - 1);
                $mpdf->WriteCell(4, 4, 'X', 0, 0, 'C', false);
            }
            return;
        }

        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }
        if (!is_scalar($value) || (string) $value === '') {
            return;
        }

        $left = (float) $position['left'];
        $top = (float) $position['top'] + ((float) ($position['row_step'] ?? 0) * $rowIndex);
        $width = (float) $position['width'];
        if ($type === 'select' && !empty($position['mark_option'])) {
            $options = array_map('strval', $field['options'] ?? []);
            $optionIndex = array_search((string) $value, $options, true);
            if ($optionIndex === false) {
                return;
            }
            $left += (($position['layout'] ?? 'horizontal') === 'horizontal' ? $width * $optionIndex : 0);
            $top += (($position['layout'] ?? 'horizontal') === 'vertical' ? $width * $optionIndex : 0);
            $mpdf->SetXY(($left * $pageWidth / 100) - 1, ($top * $pageHeight / 100) - 1);
            $mpdf->WriteCell(4, 4, 'X', 0, 0, 'C', false);
            return;
        }
        if (($field['type'] ?? '') === 'radio') {
            if (!in_array((string) $value, $field['options'] ?? [], true)) {
                return;
            }
            $optionIndex = array_search((string) $value, $field['options'], true);
            $left += (($position['layout'] ?? 'horizontal') === 'horizontal' ? $width * $optionIndex : 0);
            $top += (($position['layout'] ?? 'horizontal') === 'vertical' ? $width * $optionIndex : 0);
            $mpdf->SetXY(($left * $pageWidth / 100) - 1, ($top * $pageHeight / 100) - 1);
            $mpdf->WriteCell(4, 4, 'X', 0, 0, 'C', false);
            return;
        }

        $mpdf->SetXY($left * $pageWidth / 100, $top * $pageHeight / 100);
        $mpdf->WriteCell($width * $pageWidth / 100, 5, (string) $value, 0, 0, 'L', false);
    }
}
