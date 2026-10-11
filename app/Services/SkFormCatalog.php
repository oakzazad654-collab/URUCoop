<?php

namespace App\Services;

use App\Models\WebsiteSetting;

class SkFormCatalog
{
    public function all()
    {
        $forms = [
            'sk-01' => $this->form('สก.01 แบบพิจารณาคุณสมบัตินักศึกษาสหกิจศึกษา', 'สำหรับนักศึกษา', 'สก.pdf', [
                $this->section('ส่วนที่ 1 ข้อมูลนักศึกษา', [
                    $this->positionedField('student_name', 'ชื่อ-นามสกุล', 'text', true, [], 25.2, 17.6, 37.6),
                    $this->positionedField('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true, [], 71.9, 17.6, 18),
                    $this->positionedField('student_name_en', 'ชื่อ-นามสกุลภาษาอังกฤษ', 'text', false, [], 15.3, 19.7, 47.7),
                    $this->positionedField('year_level', 'ชั้นปี', 'number', false, [], 81.3, 19.7, 6),
                    $this->positionedField('program', 'หลักสูตร/สาขาวิชา', 'text', false, [], 20.8, 22.4, 18.5),
                    $this->positionedField('faculty', 'คณะ/วิทยาลัย', 'text', false, [], 50, 22.4, 25.6),
                    $this->positionedField('study_type', 'ประเภทภาคการศึกษา', 'radio', false, ['ภาคปกติ', 'ภาคพิเศษ'], 76.8, 22.4, 7.7),
                    $this->positionedField('credits_completed', 'จำนวนหน่วยกิตที่สอบได้ปัจจุบัน', 'number', false, [], 28.4, 24.7, 22.5),
                    $this->positionedField('gpax', 'ระดับคะแนนเฉลี่ยสะสม', 'number', false, [], 70.9, 24.7, 21.5),
                    $this->positionedField('semester', 'ภาคการศึกษาที่คาดว่าจะไปปฏิบัติงาน', 'number', false, [], 47.9, 27, 16),
                    $this->positionedField('academic_year', 'ปีการศึกษา', 'number', false, [], 72.1, 27, 22),
                    $this->field('address', 'ที่อยู่ปัจจุบัน (ข้อมูลเดิม)'),
                    $this->positionedField('address_number', 'บ้านเลขที่', 'text', false, [], 15.5, 29.4, 6),
                    $this->positionedField('address_road', 'ถนน', 'text', false, [], 25, 29.4, 12),
                    $this->positionedField('address_subdistrict', 'ตำบล/แขวง', 'text', false, [], 44.4, 29.4, 10.8),
                    $this->positionedField('address_district', 'อำเภอ/เขต', 'text', false, [], 62.7, 29.4, 10),
                    $this->positionedField('address_province', 'จังหวัด', 'text', false, [], 79.1, 29.4, 14),
                    $this->positionedField('postal_code', 'รหัสไปรษณีย์', 'text', false, [], 16.1, 31.8, 10),
                    $this->positionedField('phone', 'โทรศัพท์', 'tel', false, [], 33.4, 31.8, 18),
                    $this->positionedField('email', 'E-mail', 'email', false, [], 55.6, 31.8, 34),
                    $this->field('emergency_address', 'ที่พักในภาคการศึกษาที่ปฏิบัติงาน (ข้อมูลเดิม)'),
                    $this->positionedField('emergency_number', 'บ้านเลขที่ที่พัก', 'text', false, [], 15.6, 36.2, 6),
                    $this->positionedField('emergency_road', 'ถนนที่พัก', 'text', false, [], 25.2, 36.2, 14.5),
                    $this->positionedField('emergency_subdistrict', 'ตำบล/แขวงที่พัก', 'text', false, [], 47.2, 36.2, 9.2),
                    $this->positionedField('emergency_district', 'อำเภอ/เขตที่พัก', 'text', false, [], 64.2, 36.2, 10),
                    $this->positionedField('emergency_province', 'จังหวัดที่พัก', 'text', false, [], 81.7, 36.2, 9.5),
                    $this->positionedField('emergency_postal_code', 'รหัสไปรษณีย์ที่พัก', 'text', false, [], 17.5, 38.7, 10),
                    $this->positionedField('emergency_phone', 'โทรศัพท์ติดต่อระหว่างปฏิบัติงาน', 'tel', false, [], 33.6, 38.7, 23),
                    $this->positionedField('emergency_email', 'E-mail ติดต่อระหว่างปฏิบัติงาน', 'email', false, [], 62.7, 38.7, 28.5),
                    $this->positionedField('guardian_name', 'ผู้ปกครอง/ผู้ที่ติดต่อได้', 'text', false, [], 17, 43.3, 37),
                    $this->positionedField('guardian_relationship', 'ความสัมพันธ์กับนักศึกษา', 'text', false, [], 69.2, 43.3, 21.7),
                    $this->field('guardian_address', 'ที่อยู่ผู้ปกครอง (ข้อมูลเดิม)'),
                    $this->positionedField('guardian_number', 'บ้านเลขที่ผู้ปกครอง', 'text', false, [], 15.1, 45.8, 7),
                    $this->positionedField('guardian_road', 'ถนนผู้ปกครอง', 'text', false, [], 25.4, 45.8, 13.3),
                    $this->positionedField('guardian_subdistrict', 'ตำบล/แขวงผู้ปกครอง', 'text', false, [], 47.9, 45.8, 9.2),
                    $this->positionedField('guardian_district', 'อำเภอ/เขตผู้ปกครอง', 'text', false, [], 64, 45.8, 12.3),
                    $this->positionedField('guardian_province', 'จังหวัดผู้ปกครอง', 'text', false, [], 79.3, 45.8, 10.2),
                    $this->positionedField('guardian_postal_code', 'รหัสไปรษณีย์ผู้ปกครอง', 'text', false, [], 17, 48.3, 10),
                    $this->positionedField('guardian_phone', 'โทรศัพท์ผู้ปกครอง', 'tel', false, [], 30.4, 48.3, 24.2),
                    $this->positionedField('guardian_email', 'E-mail ผู้ปกครอง', 'email', false, [], 58.6, 48.3, 31.3),
                    $this->positionedField('student_signature_name', 'ชื่อผู้ลงนามนักศึกษา', 'text', false, [], 55.8, 52.2, 18),
                    $this->positionedField('student_signed_date', 'วันที่ลงชื่อ', 'date', false, [], 54, 56.6, 25),
                ]),
                $this->section('ส่วนที่ 2 ผลการพิจารณาของอาจารย์ที่ปรึกษา', [
                    $this->positionedField('academic_readiness', 'ความพร้อมทางวิชาการ', 'radio', false, ['เหมาะสม', 'ไม่เหมาะสม'], 38.7, 65.7, 13.8),
                    $this->positionedField('conduct', 'ความประพฤติ', 'radio', false, ['เหมาะสม', 'ไม่เหมาะสม'], 38.7, 68.3, 13.8),
                    $this->positionedField('advisor_comment', 'หมายเหตุ/ข้อเสนอแนะ', 'text', false, [], 61.5, 65.7, 28),
                    $this->positionedField('review_result', 'ผลการพิจารณา', 'radio', false, ['สามารถไปปฏิบัติงานสหกิจศึกษาได้', 'รอพิจารณาในโอกาสต่อไป', 'อื่น ๆ'], 14.8, 73.3, 1.7, 'vertical'),
                    $this->positionedField('reviewer_name', 'ชื่ออาจารย์ผู้ประสานงานรายวิชา', 'text', false, [], 18, 81, 28),
                    $this->positionedField('review_date', 'วันที่พิจารณา', 'date', false, [], 18, 85, 28),
                    $this->positionedField('program_advisor_name', 'ชื่ออาจารย์ที่ปรึกษาหลักสูตร', 'text', false, [], 64, 81, 28),
                    $this->positionedField('program_advisor_date', 'วันที่ลงนามอาจารย์ที่ปรึกษาหลักสูตร', 'date', false, [], 64, 85, 28),
                    $this->positionedField('program_chair_name', 'ชื่อประธานหลักสูตร', 'text', false, [], 37, 91, 30),
                    $this->positionedField('program_chair_date', 'วันที่ลงนามประธานหลักสูตร', 'date', false, [], 37, 95, 30),
                ]),
            ]),
            'sk-02' => $this->form('สก.02 แบบเสนองานนักศึกษาสหกิจศึกษา', 'สำหรับสถานประกอบการ', 'สก02.pdf', [
                $this->section('ข้อมูลสถานประกอบการและตำแหน่งงาน', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ (ภาษาไทย)', 'text', true),
                    $this->field('company_name_en', 'ชื่อสถานประกอบการ (ภาษาอังกฤษ)'),
                    $this->field('company_address', 'ที่อยู่สถานประกอบการ', 'textarea'),
                    $this->field('business_type', 'ประเภทกิจการ/ธุรกิจ/ผลิตภัณฑ์'),
                    $this->field('employee_count', 'จำนวนพนักงานรวม (คน)', 'number'),
                    $this->field('work_hours', 'จำนวนชั่วโมงการทำงาน (ชม./สัปดาห์)', 'number'),
                    $this->field('coordinator_name', 'ชื่อผู้จัดการ/หัวหน้าหน่วยงาน'),
                    $this->field('coordinator_position', 'ตำแหน่ง'),
                    $this->field('coordinator_department', 'แผนก/ฝ่าย'),
                    $this->field('phone', 'โทรศัพท์', 'tel'),
                    $this->field('fax', 'โทรสาร'),
                    $this->field('email', 'E-mail', 'email'),
                    $this->field('contact_method', 'วิธีประสานงาน', 'select', false, ['ติดต่อโดยตรง', 'มอบหมายผู้ประสานงาน']),
                    $this->field('position_name', 'ตำแหน่งงานที่เสนอ (Job Position)'),
                    $this->field('job_description', 'ลักษณะงานที่นักศึกษาต้องปฏิบัติ (Job description)', 'textarea'),
                    $this->field('work_period', 'ระยะเวลาที่ต้องการให้นักศึกษาปฏิบัติงาน'),
                    $this->field('work_start_date', 'วันที่เริ่มงาน', 'date'),
                    $this->field('work_end_date', 'วันที่สิ้นสุดงาน', 'date'),
                    $this->field('benefits', 'สวัสดิการ/ที่พัก/การเดินทาง', 'textarea'),
                ]),
            ], [
                $this->repeater('coordinator_contacts', 'ตำแหน่งงาน/จำนวนนักศึกษาที่ต้องการ', [
                    $this->field('major', 'สาขาวิชาที่ต้องการ', 'text', true),
                    $this->field('student_count', 'จำนวนนักศึกษา', 'number'),
                    $this->field('position', 'ตำแหน่งงานที่เสนอ'),
                    $this->field('skills', 'ความสามารถ/ทักษะที่ต้องการ'),
                ]),
                $this->repeater('requested_students', 'จำนวนนักศึกษา/คุณสมบัติที่ต้องการ', [
                    $this->field('student_name', 'ชื่อ-สกุลนักศึกษา'),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('skills', 'ความสามารถ/ทักษะที่ต้องการ'),
                ]),
            ]),
            'sk-03' => $this->form('สก.03 ใบสมัครงานสหกิจศึกษา', 'สำหรับนักศึกษา', 'สก03.pdf', [
                $this->section('สถานประกอบการที่ต้องการสมัคร', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ', 'text', true),
                    $this->field('company_address', 'ข้อมูลที่อยู่สถานประกอบการ', 'textarea'),
                    $this->field('work_start', 'วันที่เริ่มปฏิบัติงาน', 'date'),
                    $this->field('work_end', 'วันที่สิ้นสุดปฏิบัติงาน', 'date'),
                ]),
                $this->section('ข้อมูลส่วนตัวนักศึกษา', [
                    $this->field('student_name', 'ชื่อ-สกุลภาษาไทย', 'text', true),
                    $this->field('student_name_en', 'Name & Surname (English)'),
                    $this->field('student_id', 'รหัสนักศึกษา', 'text', true),
                    $this->field('program', 'หลักสูตร'),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ'),
                    $this->field('year_level', 'นักศึกษาชั้นปีที่', 'number'),
                    $this->field('advisor_name', 'อาจารย์ที่ปรึกษา'),
                    $this->field('gpa', 'เกรดเฉลี่ยภาคการศึกษาที่ผ่านมา', 'number'),
                    $this->field('gpax', 'เกรดเฉลี่ยรวม', 'number'),
                    $this->field('citizen_id', 'บัตรประจำตัวประชาชนเลขที่'),
                    $this->field('issued_at', 'ออกให้ ณ'),
                    $this->field('issued_date', 'วันที่ออกบัตร', 'date'),
                    $this->field('race', 'เชื้อชาติ'),
                    $this->field('nationality', 'สัญชาติ'),
                    $this->field('religion', 'ศาสนา'),
                    $this->field('birth_date', 'วันเดือนปีเกิด', 'date'),
                    $this->field('age', 'อายุ', 'number'),
                    $this->field('gender', 'เพศ'),
                    $this->field('height', 'ส่วนสูง (ซม.)', 'number'),
                    $this->field('weight', 'น้ำหนัก (กก.)', 'number'),
                    $this->field('chronic_disease', 'โรคประจำตัว', 'textarea'),
                    $this->field('address', 'ที่อยู่ในภาคการศึกษานี้', 'textarea'),
                    $this->field('phone', 'โทรศัพท์มือถือ', 'tel'),
                    $this->field('email', 'E-mail', 'email'),
                    $this->field('emergency_contact', 'บุคคลที่ติดต่อกรณีฉุกเฉิน', 'textarea'),
                ]),
                $this->section('ความสามารถและข้อมูลเพิ่มเติม', [
                    $this->field('skills', 'ความสามารถพิเศษ/ทักษะ', 'textarea'),
                ]),
            ]),
            'sk-04' => $this->form('สก.04 คำร้องขอหนังสือขอความอนุเคราะห์สำหรับนักศึกษาสหกิจศึกษา', 'สำหรับนักศึกษา', 'สก04.pdf', [
                $this->section('ข้อมูลคำร้องและนักศึกษา', [
                    $this->field('request_date', 'วันที่ส่งแบบคำร้อง', 'date'),
                    $this->field('student_name', 'ชื่อ-นามสกุล', 'text', true),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
                    $this->field('program', 'หลักสูตร/สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('address', 'ที่อยู่', 'textarea'),
                    $this->field('phone', 'โทรศัพท์', 'tel'),
                    $this->field('semester', 'ภาคการศึกษาที่', 'number'),
                    $this->field('academic_year', 'ปีการศึกษา', 'number'),
                    $this->field('work_start', 'เริ่มปฏิบัติงานวันที่', 'date'),
                    $this->field('work_end', 'ถึงวันที่', 'date'),
                    $this->field('company_name', 'ชื่อสถานประกอบการ'),
                    $this->field('company_address', 'สถานที่ตั้งสถานประกอบการ', 'textarea'),
                    $this->field('company_phone', 'เบอร์โทรศัพท์สถานประกอบการ', 'tel'),
                    $this->field('company_email', 'E-mail สถานประกอบการ', 'email'),
                    $this->field('company_contact', 'ชื่อ/ตำแหน่งผู้รับหนังสือ'),
                    $this->field('student_signature_date', 'วันที่ลงชื่อ', 'date'),
                ]),
                $this->section('ความเห็นผู้พิจารณา', [
                    $this->field('advisor_result', 'ความเห็นอาจารย์ผู้สอนรายวิชา/อาจารย์ที่ปรึกษา', 'textarea'),
                    $this->field('coordinator_result', 'ความเห็นประธานหลักสูตร', 'textarea'),
                    $this->field('approval_result', 'ผลการพิจารณา', 'select', false, ['เห็นควรอนุญาต', 'อื่น ๆ']),
                    $this->field('reviewer_name', 'ชื่อผู้พิจารณา'),
                    $this->field('review_date', 'วันที่พิจารณา', 'date'),
                ]),
            ], [
                $this->repeater('other_students', 'นักศึกษาที่เข้าปฏิบัติงานสถานประกอบการเดียวกัน', [
                    $this->field('student_name', 'ชื่อ-นามสกุล'),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา'),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ'),
                ]),
            ]),
            'sk-05' => $this->form('สก.05 แบบยืนยันการตอบรับนักศึกษาสหกิจศึกษา', 'สำหรับสถานประกอบการ', 'สก05.pdf', [
                $this->section('ข้อมูลสถานประกอบการและการตอบรับ', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ', 'text', true),
                    $this->field('coordinator_name', 'ชื่อผู้ประสานงาน'),
                    $this->field('position', 'ตำแหน่ง'),
                    $this->field('phone', 'โทรศัพท์', 'tel'),
                    $this->field('fax', 'โทรสาร'),
                    $this->field('email', 'E-mail', 'email'),
                    $this->field('decision', 'ผลการตอบรับ', 'select', true, ['รับนักศึกษาตามที่เสนอมา', 'ไม่สามารถรับนักศึกษาได้', 'มีความต้องการอื่น ๆ']),
                    $this->field('decision_note', 'รายละเอียด/เหตุผลเพิ่มเติม', 'textarea'),
                    $this->field('contact_instruction', 'ช่องทางติดต่อ/รายละเอียดเพิ่มเติม', 'textarea'),
                    $this->field('signed_by', 'ผู้มีอำนาจลงนาม'),
                    $this->field('signed_date', 'วันที่ลงนาม', 'date'),
                ]),
            ], [
                $this->repeater('students', 'รายชื่อนักศึกษา', [
                    $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('note', 'หมายเหตุ'),
                ]),
            ]),
            'sk-06' => $this->form('สก.06 หนังสือสัญญาการเข้ารับการปฏิบัติงานของนักศึกษาสหกิจศึกษา', 'สำหรับสถานประกอบการและนักศึกษา', 'สก06.pdf', [
                $this->section('ข้อมูลหนังสือสัญญาและนักศึกษา', [
                    $this->field('written_at', 'เขียนที่'),
                    $this->field('signed_date', 'วันที่ทำสัญญา', 'date'),
                    $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
                    $this->field('birth_date', 'วันเดือนปีเกิด', 'date'),
                    $this->field('age', 'อายุ', 'number'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('student_id', 'รหัสนักศึกษา', 'text', true),
                    $this->field('company_name', 'สถานประกอบการ'),
                    $this->field('placement_location', 'สถานที่ปฏิบัติงาน', 'textarea'),
                    $this->field('agreement_note', 'ข้อตกลงเพิ่มเติม', 'textarea'),
                    $this->field('student_signature', 'ลงชื่อ (นักศึกษา)'),
                    $this->field('company_signatory', 'ผู้มีอำนาจลงนามแทนสถานประกอบการ'),
                    $this->field('witness', 'พยาน'),
                ]),
            ]),
            'sk-07' => $this->form('สก.07 แบบแจ้งรายละเอียดการปฏิบัติงาน', 'สำหรับสถานประกอบการ', 'สก07.pdf', [
                $this->section('ข้อมูลสถานประกอบการ', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ (ภาษาไทย)', 'text', true),
                    $this->field('company_name_en', 'ชื่อสถานประกอบการ (ภาษาอังกฤษ)'),
                    $this->field('company_address', 'ที่อยู่ เลขที่/ซอย/ถนน/ตำบล/อำเภอ/จังหวัด/รหัสไปรษณีย์', 'textarea'),
                    $this->field('company_phone', 'โทรศัพท์', 'tel'),
                    $this->field('company_fax', 'โทรสาร'),
                    $this->field('company_email', 'E-mail', 'email'),
                    $this->field('manager_name', 'ชื่อผู้จัดการ/หัวหน้าหน่วยงาน'),
                    $this->field('manager_position', 'ตำแหน่งผู้จัดการ'),
                    $this->field('coordinator_name', 'ผู้ประสานงานกับมหาวิทยาลัย'),
                    $this->field('coordinator_position', 'ตำแหน่งผู้ประสานงาน'),
                    $this->field('mentor_name', 'ผู้นิเทศงาน/พี่เลี้ยง'),
                    $this->field('mentor_position', 'ตำแหน่งผู้นิเทศงาน'),
                    $this->field('mentor_department', 'แผนก/ฝ่าย'),
                    $this->field('mentor_phone', 'โทรศัพท์ผู้นิเทศงาน', 'tel'),
                    $this->field('mentor_email', 'E-mail ผู้นิเทศงาน', 'email'),
                ]),
                $this->section('ตำแหน่งและลักษณะงาน', [
                    $this->field('position_name', 'ตำแหน่งงานที่นักศึกษาปฏิบัติ'),
                ]),
            ], [
                $this->repeater('students', 'งานที่มอบหมายให้นักศึกษา', [
                    $this->field('student_name', 'ชื่อนักศึกษา', 'text', true),
                    $this->field('student_id', 'รหัสนักศึกษา'),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('assigned_tasks', 'งานที่มอบหมาย', 'textarea'),
                ]),
            ]),
            'sk-08' => $this->form('สก.08 แบบแจ้งแผนปฏิบัติงานสหกิจศึกษา', 'สำหรับนักศึกษาและผู้นิเทศงาน/พี่เลี้ยง', 'สก08.pdf', [
                $this->section('ข้อมูลผู้จัดทำและสถานประกอบการ', [
                    $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('company_name', 'สถานประกอบการ'),
                    $this->field('mentor_name', 'ผู้นิเทศงาน/พี่เลี้ยง'),
                    $this->field('semester', 'ภาคการศึกษาที่', 'number'),
                    $this->field('academic_year', 'ปีการศึกษา', 'number'),
                ]),
            ], [
                $this->repeater('schedule', 'ตารางแผนปฏิบัติงานสหกิจศึกษา', [
                    $this->field('task', 'หัวข้องาน', 'text', true),
                    $this->field('weeks', 'ช่วงสัปดาห์ที่ปฏิบัติงาน', 'weeks'),
                ]),
            ]),
            'sk-09' => $this->form('สก.09 แบบแจ้งโครงร่างรายงานการปฏิบัติงานสหกิจศึกษา', 'สำหรับนักศึกษาและผู้นิเทศงาน/พี่เลี้ยง', 'สก09.pdf', [
                $this->section('ข้อมูลนักศึกษาและสถานประกอบการ', [
                    $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('study_type', 'ประเภทภาคการศึกษา', 'select', false, ['ภาคปกติ', 'ภาคพิเศษ']),
                    $this->field('company_name', 'สถานประกอบการ'),
                    $this->field('semester', 'ภาคการศึกษาที่', 'number'),
                    $this->field('academic_year', 'ปีการศึกษา', 'number'),
                    $this->field('company_address', 'ที่อยู่สถานประกอบการ', 'textarea'),
                    $this->field('company_phone', 'โทรศัพท์', 'tel'),
                    $this->field('company_fax', 'โทรสาร'),
                    $this->field('company_email', 'E-mail', 'email'),
                ]),
                $this->section('โครงร่างรายงาน', [
                    $this->field('topic_th', 'หัวข้อรายงานภาษาไทย', 'text', true),
                    $this->field('topic_en', 'หัวข้อรายงานภาษาอังกฤษ'),
                    $this->field('report_content', 'รายละเอียดเนื้อหาของรายงาน', 'textarea'),
                    $this->field('supervisor_name', 'ผู้นิเทศงาน/พี่เลี้ยง'),
                    $this->field('advisor_name', 'อาจารย์ที่ปรึกษาสหกิจศึกษา'),
                ]),
            ]),
            'sk-10' => $this->form('สก.10 แบบฟอร์มขออนุญาตการออกนิเทศงานสหกิจศึกษา', 'สำหรับอาจารย์นิเทศงานสหกิจศึกษา', 'สก10.pdf', [
                $this->section('ข้อมูลผู้ขออนุญาต', [
                    $this->field('request_date', 'วันที่', 'date'),
                    $this->field('dean_name', 'เรียน คณบดีคณะ/วิทยาลัย'),
                    $this->field('advisor_name', 'ชื่อ-นามสกุลอาจารย์นิเทศ', 'text', true),
                    $this->field('advisor_position', 'ตำแหน่ง'),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('advisor_phone', 'เบอร์โทรศัพท์', 'tel'),
                    $this->field('semester', 'ภาคเรียนที่', 'number'),
                    $this->field('academic_year', 'ปีการศึกษา', 'number'),
                    $this->field('approver_name', 'ผู้อนุญาต'),
                ]),
            ], [
                $this->repeater('visiting_team', 'คณะผู้ออกนิเทศ', [
                    $this->field('name', 'ชื่อ-นามสกุล', 'text', true),
                    $this->field('position', 'ตำแหน่ง'),
                ]),
                $this->repeater('visits', 'กำหนดการออกนิเทศ', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ', 'text', true),
                    $this->field('province', 'จังหวัด'),
                    $this->field('visit_date', 'วันที่นิเทศ', 'date'),
                    $this->field('start_time', 'เวลาเริ่ม', 'time'),
                    $this->field('end_time', 'เวลาสิ้นสุด', 'time'),
                ]),
            ]),
            'sk-11' => $this->form('สก.11 แบบแจ้งยืนยันการนิเทศงานนักศึกษาสหกิจศึกษา', 'สำหรับสถานประกอบการ', 'สก11.pdf', [
                $this->section('ข้อมูลการนิเทศและการยืนยัน', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ', 'text', true),
                    $this->field('visit_topic', 'หัวข้อที่หารือในการนิเทศ', 'textarea'),
                    $this->field('student_meeting_date', 'วันและเวลาที่พบนักศึกษา', 'textarea'),
                    $this->field('mentor_meeting_date', 'วันและเวลาที่พบผู้นิเทศงาน/พี่เลี้ยง', 'textarea'),
                    $this->field('university_visitors', 'รายชื่ออาจารย์/เจ้าหน้าที่จากมหาวิทยาลัย', 'textarea'),
                    $this->field('confirmed_date', 'วันที่ยืนยันกำหนดการนิเทศ', 'date'),
                    $this->field('confirmed_time', 'เวลาที่ยืนยัน', 'time'),
                    $this->field('availability', 'ความพร้อมรับการนิเทศ', 'select', false, ['สะดวกตามกำหนดการ', 'ไม่สะดวกตามกำหนดการ']),
                    $this->field('unavailable_reason', 'วันที่/เวลาที่สะดวกหรือเหตุผล', 'textarea'),
                    $this->field('contact_name', 'ชื่อผู้ประสานงาน'),
                    $this->field('contact_position', 'ตำแหน่ง'),
                    $this->field('contact_phone', 'โทรศัพท์', 'tel'),
                    $this->field('contact_email', 'E-mail', 'email'),
                    $this->field('signed_date', 'วันที่ลงนาม', 'date'),
                ]),
            ]),
            'sk-12' => $this->form('สก.12 แบบสอบถามนักศึกษาเกี่ยวกับสถานประกอบการ', 'สำหรับอาจารย์นิเทศงานและนักศึกษา', 'สก12.pdf', [
                $this->section('ข้อมูลทั่วไป', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ', 'text', true),
                    $this->field('student_name', 'ชื่อนักศึกษา', 'text', true),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
                    $this->field('semester', 'ภาคเรียนที่', 'number'),
                    $this->field('academic_year', 'ปีการศึกษา', 'number'),
                    $this->field('overall_comment', 'ข้อคิดเห็นเพิ่มเติม', 'textarea'),
                ]),
                $this->section('ความเข้าใจในปรัชญาของสหกิจศึกษา', [
                    $this->field('philosophy_management', 'เจ้าหน้าที่ระดับบริหารและฝ่ายบุคคล', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('philosophy_advisor', 'อาจารย์นิเทศงาน', 'select', false, ['5', '4', '3', '2', '1']),
                ]),
                $this->section('การจัดการและสนับสนุนการปฏิบัติงาน', [
                    $this->field('support_coordination', 'การประสานงานด้านการจัดการและสนับสนุนระหว่างฝ่ายบุคคลและผู้นิเทศงาน', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('support_guidance', 'การให้คำแนะนำและดูแลนักศึกษา', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('support_staff', 'บุคลากรในสถานประกอบการให้ความสนใจสนับสนุนนักศึกษา', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('support_equipment', 'ความพร้อมของอุปกรณ์/เครื่องมือสำหรับนักศึกษา', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('support_compensation', 'ค่าตอบแทนที่เหมาะสม', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('support_welfare', 'สวัสดิการและที่พักสำหรับนักศึกษา', 'select', false, ['5', '4', '3', '2', '1']),
                ]),
                $this->section('ลักษณะงานที่มอบหมายให้นักศึกษาปฏิบัติ', [
                    $this->field('job_quantity', 'ปริมาณงานมีความเหมาะสม', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('job_description', 'ความเหมาะสมของคุณลักษณะงาน (Job description)', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('job_major_match', 'งานที่ได้รับมอบหมายตรงกับสาขาวิชาเอก', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('job_learning', 'งานที่ได้รับมอบหมายตรงกับความสนใจและทักษะ', 'select', false, ['5', '4', '3', '2', '1']),
                    $this->field('job_challenge', 'งานช่วยให้เกิดการเรียนรู้และพัฒนาทักษะ', 'select', false, ['5', '4', '3', '2', '1']),
                ]),
            ]),
            'sk-12.1' => $this->form('สก.12.1 แบบบันทึกการนิเทศงานสหกิจศึกษา', 'สำหรับอาจารย์นิเทศงานสหกิจศึกษา', 'SK-12.1.pdf', [
                $this->section('ข้อมูลสถานประกอบการและการนิเทศ', [
                    $this->field('company_name', 'ชื่อสถานประกอบการ (ภาษาไทย)', 'text', true),
                    $this->field('company_name_en', 'ชื่อสถานประกอบการ (ภาษาอังกฤษ)'),
                    $this->field('company_address', 'ที่อยู่', 'textarea'),
                    $this->field('company_phone', 'โทรศัพท์', 'tel'),
                    $this->field('company_fax', 'โทรสาร'),
                    $this->field('advisor_name', 'ชื่ออาจารย์นิเทศงาน', 'text', true),
                    $this->field('visit_date', 'วันที่นิเทศ', 'date'),
                    $this->field('visit_time', 'เวลา', 'time'),
                ]),
            ], [
                $this->repeater('students', 'รายชื่อนักศึกษาที่ได้รับการนิเทศ', [
                    $this->field('student_name', 'ชื่อ-สกุล', 'text', true),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('signature', 'ลายมือชื่อนักศึกษา'),
                ]),
            ]),
            'sk-13' => $this->form('สก.13 แบบประเมินผลนักศึกษาสหกิจศึกษา', 'สำหรับผู้นิเทศงาน (พี่เลี้ยง)', 'สก13.pdf', [
                $this->section('ข้อมูลทั่วไป / Work Term Information', [
                    $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
                    $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
                    $this->field('major', 'สาขาวิชา'),
                    $this->field('faculty', 'คณะ/วิทยาลัย'),
                    $this->field('company_name', 'ชื่อสถานประกอบการ'),
                    $this->field('evaluator_name', 'ชื่อ-นามสกุลผู้ประเมิน', 'text', true),
                    $this->field('position', 'ตำแหน่ง'),
                    $this->field('department', 'แผนก/ฝ่าย'),
                    $this->field('work_achievement', 'ผลสำเร็จของงาน/Work Achievement (คะแนนเต็ม 20)', 'number'),
                    $this->field('knowledge_ability', 'ความรู้ความสามารถ (คะแนนเต็ม 20)', 'number'),
                    $this->field('responsibility', 'ความรับผิดชอบและคุณธรรม (คะแนนเต็ม 20)', 'number'),
                    $this->field('personality', 'ลักษณะส่วนบุคคล (คะแนนเต็ม 20)', 'number'),
                    $this->field('overall_score', 'คะแนนรวม (คะแนนเต็ม 100)', 'number'),
                    $this->field('strengths', 'จุดเด่น/ข้อคิดเห็นเพิ่มเติม', 'textarea'),
                    $this->field('improvement', 'ข้อควรปรับปรุง', 'textarea'),
                    $this->field('evaluator_date', 'วันที่ประเมิน', 'date'),
                ]),
            ]),
            'sk-14' => $this->reportEvaluationForm('sk-14', 'สก.14 แบบประเมินรายงานนักศึกษาสหกิจศึกษา', 'สำหรับผู้นิเทศงาน (พี่เลี้ยง)', 'สก14.pdf'),
            'sk-15' => $this->reportEvaluationForm('sk-15', 'สก.15 แบบประเมินรายงานนักศึกษาสหกิจศึกษา', 'สำหรับอาจารย์นิเทศงานสหกิจศึกษา', 'สก15.pdf'),
        ];

        $this->applyPositionOverrides($forms);

        return $forms;
    }

    private function applyPositionOverrides(&$forms)
    {
        foreach ($forms as $type => &$form) {
            $key = 'sk_'.str_replace(['-', '.'], '_', substr($type, 3)).'_field_positions';
            $stored = WebsiteSetting::where('key', $key)->value('value');
            $overrides = is_string($stored) ? json_decode($stored, true) : null;
            $configuredDefaults = config('sk_form_positions', []);
            $defaults = is_array($configuredDefaults) ? ($configuredDefaults[$type] ?? []) : [];
            $expected = 0;
            $applied = 0;
            $index = 0;

            foreach ($form['sections'] as &$section) {
                foreach ($section['fields'] as &$field) {
                    if ($type === 'sk-01' && empty($field['position'])) {
                        continue;
                    }
                    $positionKey = $type === 'sk-01' ? $field['key'] : 'field_'.$field['key'];
                    $defaultPosition = $defaults['fields'][$field['key']] ?? ($field['position'] ?? null);
                    $legacyPositionKey = 'field_'.$index;
                    if ($type === 'sk-10' && $field['key'] === 'approver_name') {
                        $legacyPositionKey = 'field_10';
                    }
                    $savedPosition = is_array($overrides)
                        ? ($overrides[$positionKey] ?? $overrides[$legacyPositionKey] ?? null)
                        : null;
                    $position = $this->mergePositions($defaultPosition, $savedPosition);
                    $expected++;
                    if ($this->applyPosition($field, $position)) {
                        $applied++;
                    }
                    $index++;
                }
                unset($field);
            }
            unset($section);

            foreach ($form['repeaters'] as $repeaterIndex => &$repeater) {
                foreach ($repeater['fields'] as $fieldIndex => &$field) {
                    $positionKey = 'repeater_'.$repeaterIndex.'_field_'.$fieldIndex;
                    $defaultPosition = $defaults['repeaters'][$repeater['key']][$field['key']] ?? null;
                    $savedPosition = is_array($overrides) ? ($overrides[$positionKey] ?? null) : null;
                    $position = $this->mergePositions($defaultPosition, $savedPosition);
                    $expected++;
                    if ($this->applyPosition($field, $position, 2.8)) {
                        $applied++;
                    }
                }
                unset($field);
            }
            unset($repeater);

            $form['positions_configured'] = $expected > 0 && $applied === $expected;
        }
        unset($form);
    }

    private function mergePositions($defaults, $overrides)
    {
        $defaults = $this->normalizePosition($defaults);
        $overrides = $this->normalizePosition($overrides);
        if (!is_array($defaults)) {
            return $overrides;
        }
        if (!is_array($overrides)) {
            return $defaults;
        }

        return array_merge($defaults, $overrides);
    }

    private function normalizePosition($position)
    {
        if (!is_array($position)) {
            return null;
        }
        if (array_key_exists(0, $position)) {
            return [
                'left' => $position[0] ?? null,
                'top' => $position[1] ?? null,
                'width' => $position[2] ?? null,
                'page' => $position[3] ?? 1,
                'row_step' => $position[4] ?? 0,
                'layout' => $position[5] ?? 'horizontal',
                'mark_option' => !empty($position[6]),
                'input_width' => $position[7] ?? null,
                'input_center' => !empty($position[8]),
            ];
        }

        $position['page'] = $position['page'] ?? 1;
        $position['row_step'] = $position['row_step'] ?? 0;

        return $position;
    }

    private function applyPosition(&$field, $position, $defaultRowStep = 0)
    {
        if (!is_array($position)) {
            return false;
        }

        $left = filter_var($position['left'] ?? null, FILTER_VALIDATE_FLOAT);
        $top = filter_var($position['top'] ?? null, FILTER_VALIDATE_FLOAT);
        $width = filter_var($position['width'] ?? null, FILTER_VALIDATE_FLOAT);
        $page = filter_var($position['page'] ?? null, FILTER_VALIDATE_INT);
        $rowStep = filter_var($position['row_step'] ?? $defaultRowStep, FILTER_VALIDATE_FLOAT);
        if ($left === false || $top === false || $width === false || $page === false || $rowStep === false
            || $left < 0 || $top < 0 || $width <= 0 || $left + $width > 100
            || $top > 100 || $page < 1 || $rowStep < 0) {
            return false;
        }

        $field['position'] = array_merge($field['position'] ?? [], $position, [
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'page' => $page,
            'row_step' => $rowStep,
        ]);

        return true;
    }

    private function reportEvaluationForm($type, $title, $role, $source)
    {
        $fields = [
            $this->field('student_name', 'ชื่อ-นามสกุลนักศึกษา', 'text', true),
            $this->field('student_id', 'รหัสประจำตัวนักศึกษา', 'text', true),
            $this->field('major', 'สาขาวิชา'),
            $this->field('faculty', 'คณะ/วิทยาลัย'),
            $this->field('company_name', 'ชื่อสถานประกอบการ'),
            $this->field('evaluator_name', 'ชื่อ-นามสกุลผู้ประเมิน', 'text', true),
            $this->field('position', 'ตำแหน่ง'),
            $this->field('department', 'แผนก/ฝ่าย'),
            $this->field('report_title_th', 'ชื่อรายงานภาษาไทย'),
            $this->field('report_title_en', 'ชื่อรายงานภาษาอังกฤษ'),
        ];
        $criteria = $type === 'sk-14'
            ? ['กิตติกรรมประกาศ', 'บทคัดย่อ', 'สารบัญ สารบัญรูปภาพ และสารบัญตาราง', 'บทนำและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตการศึกษา', 'ทฤษฎีและเอกสารที่เกี่ยวข้อง', 'วิธีการดำเนินงาน', 'ผลการปฏิบัติงาน', 'การวิเคราะห์ผล', 'สรุปผลและข้อเสนอแนะ', 'การอ้างอิงเอกสาร', 'รูปแบบและความถูกต้องของรายงาน', 'การนำเสนอผลงาน']
            : ['กิตติกรรมประกาศ', 'บทคัดย่อ', 'สารบัญ สารบัญรูปภาพ และสารบัญตาราง', 'ความเป็นมาและความสำคัญ', 'วัตถุประสงค์', 'ขอบเขตการศึกษา', 'ทฤษฎีและเอกสารที่เกี่ยวข้อง', 'วิธีการดำเนินงาน', 'ผลการปฏิบัติงาน', 'การวิเคราะห์ผล', 'สรุปผลและข้อเสนอแนะ', 'การอ้างอิงเอกสาร', 'รูปแบบและความถูกต้องของรายงาน', 'การนำเสนอผลงาน'];
        foreach ($criteria as $index => $criterion) {
            $fields[] = $this->field('criterion_'.($index + 1), $criterion.' (คะแนนเต็ม 5)', 'number');
        }
        $fields[] = $this->field('total_score', 'คะแนนรวม', 'number');
        $fields[] = $this->field('comments', 'ข้อคิดเห็นเพิ่มเติม', 'textarea');
        $fields[] = $this->field('evaluation_date', 'วันที่ประเมิน', 'date');

        return $this->form($title, $role, $source, [
            $this->section('ข้อมูลทั่วไปและผลการประเมินรายงาน', $fields),
        ]);
    }

    private function form($title, $role, $source, $sections, $repeaters = [])
    {
        return [
            'title' => $title,
            'role' => $role,
            'source' => $source,
            'sections' => $sections,
            'repeaters' => $repeaters,
            'files' => [],
        ];
    }

    private function section($title, $fields)
    {
        return ['title' => $title, 'fields' => $fields];
    }

    private function repeater($key, $label, $fields)
    {
        return ['key' => $key, 'label' => $label, 'fields' => $fields];
    }

    private function field($key, $label, $type = 'text', $required = false, $options = [])
    {
        $field = ['key' => $key, 'label' => $label, 'type' => $type];
        if ($required) {
            $field['required'] = true;
        }
        if ($options) {
            $field['options'] = $options;
        }
        return $field;
    }

    private function positionedField($key, $label, $type, $required, $options, $left, $top, $width, $layout = 'horizontal')
    {
        $field = $this->field($key, $label, $type, $required, $options);
        $field['position'] = ['left' => $left, 'top' => $top, 'width' => $width];
        if ($type === 'radio') {
            $field['position']['layout'] = $layout;
        }

        return $field;
    }
}
