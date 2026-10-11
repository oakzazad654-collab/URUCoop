@extends('layouts.public')

@section('title', 'ขั้นตอนสหกิจศึกษา | URU Coop-IS')

@section('content')
<header class="public-guide-heading">
    <p class="public-home-eyebrow">URU Coop-IS · คู่มือเบื้องต้น</p>
    <h1>ขั้นตอนการดำเนินงานสหกิจศึกษา</h1>
    <p>ภาพรวมเอกสารและการพิจารณาตาม workflow ที่ระบบรองรับ</p>
</header>

<section class="public-guide-steps" aria-label="ลำดับขั้นตอนสหกิจศึกษา">
    <article class="public-guide-step"><span class="guide-step-number">01</span><div><h2>สมัครและตรวจคุณสมบัติ</h2><p>นักศึกษาสร้างใบสมัคร ระบุภาคการศึกษา สถานประกอบการ ตำแหน่ง และช่วงเวลาปฏิบัติงาน จากนั้นส่งให้อาจารย์ที่ปรึกษาตรวจผลการเรียน ความประพฤติ กิจกรรม และสุขภาพ</p><span class="guide-form-label">ใบสมัครสหกิจศึกษา · ตรวจคุณสมบัติ</span></div></article>
    <article class="public-guide-step"><span class="guide-step-number">02</span><div><h2>ขอหนังสือถึงสถานประกอบการ</h2><p>เมื่อใบสมัครผ่านคุณสมบัติ นักศึกษาส่งคำขอหนังสือขอความอนุเคราะห์ อาจารย์ผู้ประสานงานพิจารณา และเจ้าหน้าที่ตรวจความครบถ้วนของเอกสาร</p><span class="guide-form-label">CWIE 02 · คำร้องขอหนังสือ</span></div></article>
    <article class="public-guide-step"><span class="guide-step-number">03</span><div><h2>บันทึกข้อมูลสถานที่ปฏิบัติงาน</h2><p>ระบุข้อมูลสถานประกอบการ ผู้ติดต่อ พี่เลี้ยง ตำแหน่งและลักษณะงาน พร้อมข้อมูลแผนที่ จากนั้นบันทึกผลตอบรับการเข้าปฏิบัติงาน</p><span class="guide-form-label">CWIE 03 · ข้อมูลสถานประกอบการ</span></div></article>
    <article class="public-guide-step"><span class="guide-step-number">04</span><div><h2>จัดทำแผนปฏิบัติงาน</h2><p>หลังสถานประกอบการตอบรับ นักศึกษาจัดทำกิจกรรมตามช่วงเวลาปฏิบัติงาน และส่งแผนให้อาจารย์ที่ปรึกษาพิจารณา</p><span class="guide-form-label">CWIE 04 · แผนปฏิบัติงาน</span></div></article>
    <article class="public-guide-step"><span class="guide-step-number">05</span><div><h2>ส่งโครงร่างรายงาน</h2><p>ระบุหัวข้อ ที่มา วัตถุประสงค์ ขอบเขต วิธีดำเนินงาน และประโยชน์ที่คาดว่าจะได้รับ ก่อนส่งให้อาจารย์ที่ปรึกษาพิจารณา</p><span class="guide-form-label">CWIE 05 · โครงร่างรายงาน</span></div></article>
    <article class="public-guide-step"><span class="guide-step-number">06</span><div><h2>วางแผนและบันทึกผลการนิเทศ</h2><p>อาจารย์จัดทำคำขอและกำหนดการนิเทศ หลังการเข้าพบสามารถบันทึกผลการปฏิบัติงาน ความเห็น ปัญหา และข้อเสนอแนะ</p><span class="guide-form-label">CWIE 06 · คำขอและผลการนิเทศ</span></div></article>
</section>

<aside class="public-guide-note"><i data-lucide="info"></i><p>ขั้นตอนและสิทธิ์การดำเนินการขึ้นอยู่กับสถานะเอกสารและบทบาทผู้ใช้งาน นักศึกษาสมัครแล้วเข้าใช้งานได้ทันที ส่วนบัญชีอาจารย์ ผู้นิเทศงาน และสถานประกอบการต้องรอผู้ดูแลระบบอนุมัติ</p><a class="btn btn-primary" href="{{ route('register') }}">สมัครสมาชิก <i data-lucide="arrow-up-right" class="ms-1" style="width:15px;height:15px"></i></a></aside>
@endsection

@push('styles')
<style>
    .public-guide-heading{max-width:760px;margin:0 auto 30px}.public-guide-heading h1{margin:0;color:#183528;font-size:30px;font-weight:600;line-height:1.45;letter-spacing:0}.public-guide-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px;line-height:1.7}
    .public-guide-steps{max-width:860px;margin:0 auto;border-top:1px solid #d8e3da}.public-guide-step{display:grid;grid-template-columns:76px minmax(0,1fr);gap:14px;padding:22px 8px;border-bottom:1px solid #d8e3da}.guide-step-number{color:#287e4d;font-size:24px;font-weight:500;line-height:1.2}.public-guide-step h2{margin:0 0 7px;color:#20392a;font-size:16px;font-weight:600;letter-spacing:0}.public-guide-step p{max-width:720px;margin:0;color:#586d5d;font-size:13px;line-height:1.9}.guide-form-label{display:inline-block;margin-top:10px;color:#166534;font-size:11px;font-weight:600}
    .public-guide-note{max-width:860px;margin:25px auto 0;padding:18px 20px;display:grid;grid-template-columns:24px minmax(0,1fr) auto;align-items:center;gap:12px;border-left:3px solid #2b8050;background:#eaf2ec}.public-guide-note>svg{width:18px;height:18px;color:#166534}.public-guide-note p{margin:0;color:#3f5948;font-size:12px;line-height:1.8}.public-guide-note .btn{white-space:nowrap;background:#27824f;border-color:#27824f}
    @media(max-width:700px){.public-guide-heading h1{font-size:24px}.public-guide-step{grid-template-columns:42px minmax(0,1fr);gap:10px;padding:18px 2px}.guide-step-number{font-size:18px}.public-guide-step h2{font-size:14px}.public-guide-step p{font-size:12px}.public-guide-note{grid-template-columns:22px 1fr;padding:15px}.public-guide-note .btn{grid-column:2;justify-self:start}}
</style>
@endpush