@extends('layouts.public')

@section('title', 'ข้อมูลสหกิจศึกษา | URU Coop-IS')

@section('content')
<header class="public-info-heading">
    <p class="public-home-eyebrow">ข้อมูลสหกิจศึกษา · URU Coop-IS</p>
    <h1>สหกิจศึกษา มหาวิทยาลัยราชภัฏอุตรดิตถ์</h1>
    <p>พื้นที่เรียนรู้และข้อมูลสำหรับนักศึกษา อาจารย์ สถานประกอบการ และผู้เกี่ยวข้อง</p>
</header>
<section class="public-info-grid">
    <article class="public-info-card"><span class="public-info-icon"><i data-lucide="landmark"></i></span><h2>ความเป็นมา</h2><p>สหกิจศึกษาเป็นการจัดการเรียนรู้ที่เชื่อมโยงการศึกษาเข้ากับการปฏิบัติงานจริง โดยมหาวิทยาลัยและสถานประกอบการร่วมกันเตรียมความพร้อม กำหนดงาน และติดตามผลการเรียนรู้ของนักศึกษา</p></article>
    <article class="public-info-card"><span class="public-info-icon"><i data-lucide="book-open-check"></i></span><h2>สหกิจศึกษาคืออะไร</h2><p>ประสบการณ์ทำงานในสถานประกอบการตามช่วงเวลาที่หลักสูตรกำหนด นักศึกษาปฏิบัติงานตามตำแหน่งและแผนงาน มีผู้นิเทศงานในสถานประกอบการและอาจารย์นิเทศจากมหาวิทยาลัยร่วมดูแล</p></article>
    <article class="public-info-card"><span class="public-info-icon"><i data-lucide="target"></i></span><h2>วัตถุประสงค์</h2><ul><li>ประยุกต์ความรู้ในหลักสูตรกับการทำงานจริง</li><li>พัฒนาทักษะวิชาชีพ การสื่อสาร และการทำงานร่วมกับผู้อื่น</li><li>สร้างความร่วมมือระหว่างมหาวิทยาลัยกับสถานประกอบการ</li><li>ใช้ผลสะท้อนจากงานจริงพัฒนาการเรียนการสอนและหลักสูตร</li></ul></article>
    <article class="public-info-card"><span class="public-info-icon"><i data-lucide="briefcase-business"></i></span><h2>ลักษณะงานสหกิจศึกษา</h2><p>เป็นงานที่มีขอบเขตและเป้าหมายชัดเจน สอดคล้องกับสาขาวิชา มีผู้มอบหมายงานและผู้ให้คำปรึกษา พร้อมแผนปฏิบัติงาน การติดตามความก้าวหน้า และการประเมินผล ไม่ใช่เพียงการสังเกตการณ์หรือทำงานที่ไม่เกี่ยวข้องกับหลักสูตร</p></article>
</section>
<section class="public-staff-directory">
    <div class="public-info-section-heading"><div><p class="public-home-eyebrow">ผู้รับผิดชอบ</p><h2>บุคลากรและหน่วยงานที่เกี่ยวข้อง</h2></div><span>รายชื่อจากบัญชีบุคลากรที่เปิดใช้งานในระบบ</span></div>
    <div class="public-staff-grid">
        @forelse($responsibleStaff as $member)
            <article class="public-staff-card"><i data-lucide="{{ $member->role === 'officer' ? 'contact-round' : 'user-round-check' }}"></i><div><strong>{{ $member->name }}</strong><span>{{ ['advisor' => 'อาจารย์ประสานงาน/ที่ปรึกษา', 'supervisor' => 'อาจารย์นิเทศ', 'officer' => 'เจ้าหน้าที่ศูนย์สหกิจศึกษา'][$member->role] }}</span><small>{{ optional($member->faculty)->name ?: 'มหาวิทยาลัยราชภัฏอุตรดิตถ์' }}{{ $member->department ? ' · '.$member->department->name : '' }}</small></div></article>
        @empty
            <p class="text-muted mb-0">ติดต่อศูนย์สหกิจศึกษา มหาวิทยาลัยราชภัฏอุตรดิตถ์ เพื่อสอบถามผู้รับผิดชอบ</p>
        @endforelse
    </div>
</section>
<nav class="public-info-actions"><a class="btn btn-primary" href="{{ route('public.forms') }}">ดูคู่มือและแบบฟอร์ม</a><a class="btn btn-outline-primary" href="{{ route('public.guide') }}">ดูขั้นตอนการดำเนินงาน</a></nav>
@endsection

@push('styles')
<style>
    .public-info-heading{max-width:850px;margin:0 auto 30px}.public-info-heading h1{margin:0;color:#183528;font-size:30px;font-weight:600;line-height:1.4}.public-info-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px;line-height:1.8}
    .public-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.public-info-card{padding:22px;background:#fff;border:1px solid #dfe9e1;border-radius:12px}.public-info-icon{width:38px;height:38px;display:grid;place-items:center;margin-bottom:13px;border-radius:10px;background:#e8f3eb;color:#166534}.public-info-icon svg{width:19px;height:19px}.public-info-card h2{margin:0 0 8px;color:#20392a;font-size:16px;font-weight:600}.public-info-card p,.public-info-card li{color:#586d5d;font-size:12px;line-height:1.9}.public-info-card p{margin:0}.public-info-card ul{margin:0;padding-left:18px}
    .public-staff-directory{margin-top:34px}.public-info-section-heading{display:flex;justify-content:space-between;align-items:end;gap:14px;margin-bottom:14px}.public-info-section-heading h2{margin:0;color:#183528;font-size:20px}.public-info-section-heading>span{color:#718176;font-size:11px}.public-staff-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.public-staff-card{display:flex;align-items:flex-start;gap:12px;padding:15px;background:#fff;border:1px solid #dfe9e1;border-radius:10px}.public-staff-card>svg{width:18px;height:18px;color:#27824f;flex:0 0 auto}.public-staff-card strong,.public-staff-card span,.public-staff-card small{display:block}.public-staff-card strong{font-size:13px;color:#20392a}.public-staff-card span{margin-top:2px;color:#43805a;font-size:11px}.public-staff-card small{margin-top:3px;color:#718176;font-size:10px}.public-info-actions{display:flex;gap:10px;margin-top:22px}
    @media(max-width:650px){.public-info-heading h1{font-size:24px}.public-info-grid,.public-staff-grid{grid-template-columns:1fr}.public-info-card{padding:18px}.public-info-section-heading{align-items:flex-start;flex-direction:column}.public-info-actions{flex-wrap:wrap}}
</style>
@endpush
