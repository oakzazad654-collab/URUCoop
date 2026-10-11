@extends('layouts.app')

@section('title', 'Workflow สหกิจศึกษา | URU Coop-IS')
@section('breadcrumb', 'Workflow สหกิจศึกษา')

@section('content')
@php
    $role = auth()->user()->role;
    $isStudent = $role === 'student';
    $isAdvisor = $role === 'advisor';
    $isOfficer = $role === 'officer';
    $isAdmin = $role === 'admin';
    $isSupervisor = $role === 'supervisor';
    $isMentor = $role === 'mentor';
    $isExecutive = $role === 'executive';
    $roleLabel = $isStudent ? 'พื้นที่นักศึกษา' : ($isAdvisor ? 'พื้นที่อาจารย์ที่ปรึกษา' : ($isMentor ? 'พื้นที่ผู้นิเทศงาน' : ($isSupervisor ? 'พื้นที่อาจารย์นิเทศ' : ($isExecutive ? 'พื้นที่ผู้บริหาร' : ($isOfficer ? 'พื้นที่เจ้าหน้าที่' : 'พื้นที่ผู้ดูแลระบบ')))));
    $qualifiedApplications = $applications->filter(function ($application) {
        return $application->application_status === 'APPROVED'
            && $application->qualification_result === 'QUALIFIED';
    });
    $availablePlacements = $placements->filter(function ($placement) {
        return $placement->acceptance_status === 'ACCEPTED';
    });
@endphp

<header class="page-heading">
    <div>
        <p class="page-eyebrow">{{ $roleLabel }} · URU Coop-IS</p>
        <h1>Workflow สหกิจศึกษา</h1>
        <p class="page-subtitle">ติดตามใบสมัคร เอกสาร และการดำเนินงานตามภาคการศึกษา</p>
    </div>
</header>

@if(session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>ดำเนินการไม่สำเร็จ</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<section class="stats-grid mb-4" aria-label="สรุป workflow">
    <div class="stat-card"><span class="stat-icon"><i data-lucide="file-check-2"></i></span><span class="stat-copy"><span>ใบสมัคร</span><strong>{{ $applications->count() }}</strong></span></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="building-2"></i></span><span class="stat-copy"><span>สถานที่ปฏิบัติงาน</span><strong>{{ $placements->count() }}</strong></span></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="clipboard-check"></i></span><span class="stat-copy"><span>รายการรอพิจารณา</span><strong>{{ $approvals->count() }}</strong></span></div>
    <div class="stat-card"><span class="stat-icon"><i data-lucide="map-pinned"></i></span><span class="stat-copy"><span>คำขอนิเทศ</span><strong>{{ $supervisionRequests->count() }}</strong></span></div>
</section>

<nav class="workflow-nav mb-4" aria-label="ส่วนของ workflow">
    @if($isStudent)<a class="btn btn-sm btn-outline-secondary" href="#applications">ใบสมัคร</a><a class="btn btn-sm btn-outline-secondary" href="#letters">คำขอหนังสือ</a><a class="btn btn-sm btn-outline-secondary" href="#placements">สถานที่ปฏิบัติงาน</a><a class="btn btn-sm btn-outline-secondary" href="#plans">แผนงานและรายงาน</a>@endif
    @if($isAdvisor || $isAdmin)<a class="btn btn-sm btn-outline-secondary" href="#applications">ตรวจคุณสมบัติ</a>@endif
    @if($isAdvisor || $isAdmin || $isSupervisor || $isExecutive)<a class="btn btn-sm btn-outline-secondary" href="#supervision">การนิเทศ</a>@endif
    @if($isOfficer || $isAdmin)<a class="btn btn-sm btn-outline-secondary" href="#letters">ตรวจเอกสาร</a><a class="btn btn-sm btn-outline-secondary" href="#placements">ตอบรับสถานที่</a>@endif
    <a class="btn btn-sm btn-outline-secondary" href="#approvals">การอนุมัติ</a>
</nav>

@if($isStudent)
<section class="card mb-4" id="applications">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><div><h2 class="h6 mb-1">1. ใบสมัครและตรวจคุณสมบัติ</h2><span class="small text-muted">เลือกภาคการศึกษาและสถานประกอบการที่ต้องการสมัคร</span></div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('workflow.applications.store') }}" class="row g-3 mb-4">
            @csrf
            <div class="col-md-4"><label class="form-label" for="term_id">ภาคการศึกษา</label><select class="form-select" id="term_id" name="term_id" required><option value="">เลือกภาคการศึกษา</option>@foreach($terms as $term)<option value="{{ $term->term_id }}">ภาค {{ $term->semester_no }} / {{ $term->academic_year }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="preferred_company_id">สถานประกอบการ</label><select class="form-select" id="preferred_company_id" name="preferred_company_id" required><option value="">เลือกสถานประกอบการ</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->company_name_th ?: $company->name }}{{ $company->province ? ' · '.$company->province : '' }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="desired_position">ตำแหน่งที่ต้องการ</label><input class="form-control" id="desired_position" name="desired_position" maxlength="200"></div>
            <div class="col-md-6"><label class="form-label" for="work_start_date">วันเริ่มปฏิบัติงาน</label><input class="form-control" id="work_start_date" name="work_start_date" type="date" required></div>
            <div class="col-md-6"><label class="form-label" for="work_end_date">วันสิ้นสุด</label><input class="form-control" id="work_end_date" name="work_end_date" type="date" required></div>
            <div class="col-12"><label class="form-label" for="expected_job_description">รายละเอียดงานที่คาดหวัง</label><textarea class="form-control" id="expected_job_description" name="expected_job_description" rows="2" maxlength="10000"></textarea></div>
            <div class="col-12"><label class="form-label" for="student_note">หมายเหตุ</label><textarea class="form-control" id="student_note" name="student_note" rows="2" maxlength="5000"></textarea></div>
            <div class="col-12"><button class="btn btn-primary" type="submit"><i data-lucide="send" class="me-1" style="width:16px;height:16px"></i>สร้างใบสมัคร</button></div>
        </form>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>เลขที่</th><th>ภาคการศึกษา</th><th>สถานประกอบการ</th><th>ช่วงปฏิบัติงาน</th><th>สถานะ</th><th>ดำเนินการ</th></tr></thead><tbody>
            @forelse($applications as $application)
                <tr><td>{{ $application->application_no }}</td><td>{{ optional($application->term)->semester_no }}/{{ optional($application->term)->academic_year }}</td><td>{{ optional($application->company)->company_name_th ?: optional($application->company)->name }}</td><td>{{ optional($application->work_start_date)->format('d/m/Y') }} – {{ optional($application->work_end_date)->format('d/m/Y') }}</td><td><span class="badge text-bg-light">{{ $application->application_status }} · {{ $application->qualification_result }}</span></td><td>@if(in_array($application->application_status, ['DRAFT', 'UNDER_REVIEW'], true))<form method="POST" action="{{ route('workflow.applications.submit', $application->application_id) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">ส่งพิจารณา</button></form>@else<span class="text-muted small">ส่งแล้ว</span>@endif</td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีใบสมัครใน workflow นี้</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>
@endif

@if($isAdvisor || $isAdmin)
<section class="card mb-4" id="applications">
    <div class="card-header bg-white"><h2 class="h6 mb-1">ตรวจคุณสมบัติใบสมัคร</h2><span class="small text-muted">ตรวจผลการเรียน ความประพฤติ กิจกรรม และสุขภาพ</span></div>
    <div class="card-body">
        @php($reviewQueue = $applications->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW']))
        @forelse($reviewQueue as $application)
            <article class="workflow-record">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div><strong>{{ optional(optional($application->student)->user)->name ?: optional($application->student)->student_code }}</strong><div class="small text-muted">{{ $application->application_no }} · {{ optional($application->company)->company_name_th ?: optional($application->company)->name }}</div></div><span class="badge text-bg-light">{{ $application->application_status }}</span></div>
                <form method="POST" action="{{ route('workflow.applications.review', $application->application_id) }}" class="row g-3">
                    @csrf
                    @foreach(['academic_result' => 'ผลการเรียน', 'behavior_result' => 'ความประพฤติ', 'activity_result' => 'กิจกรรม', 'health_result' => 'สุขภาพ'] as $field => $label)
                        <div class="col-sm-6 col-lg-3"><label class="form-label" for="{{ $field }}_{{ $application->application_id }}">{{ $label }}</label><select class="form-select" id="{{ $field }}_{{ $application->application_id }}" name="{{ $field }}" required><option value="1">ผ่าน</option><option value="0">ไม่ผ่าน</option></select></div>
                    @endforeach
                    <div class="col-md-4"><label class="form-label" for="review_result_{{ $application->application_id }}">ผลตรวจ</label><select class="form-select" id="review_result_{{ $application->application_id }}" name="result" required><option value="QUALIFIED">ผ่านคุณสมบัติ</option><option value="REVISION_REQUIRED">ขอแก้ไข</option><option value="NOT_QUALIFIED">ไม่ผ่าน</option></select></div>
                    <div class="col-md-8"><label class="form-label" for="review_comment_{{ $application->application_id }}">ความเห็น</label><input class="form-control" id="review_comment_{{ $application->application_id }}" name="comment" maxlength="5000"></div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกผลตรวจ</button></div>
                </form>
            </article>
        @empty<p class="text-muted mb-0">ไม่มีใบสมัครรอตรวจในขอบเขตที่คุณดูแล</p>@endforelse
    </div>
</section>
@endif

@if($isStudent || $isAdvisor || $isOfficer || $isAdmin)
<section class="card mb-4" id="letters">
    <div class="card-header bg-white"><h2 class="h6 mb-1">2. คำขอหนังสือ CWIE 02</h2><span class="small text-muted">คำขอต้องอ้างอิงใบสมัครที่ผ่านคุณสมบัติแล้ว</span></div>
    <div class="card-body">
        @if($isStudent && $qualifiedApplications->isNotEmpty())
            <form method="POST" action="{{ route('workflow.letter-requests.store') }}" class="row g-3 mb-4">
                @csrf
                <div class="col-md-5"><label class="form-label" for="letter_application">ใบสมัครที่ผ่านแล้ว</label><select class="form-select" id="letter_application" name="application_ids[]" required>@foreach($qualifiedApplications as $application)<option value="{{ $application->application_id }}">{{ $application->application_no }} · {{ optional($application->company)->company_name_th ?: optional($application->company)->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label" for="receiver_name">ผู้รับหนังสือ</label><input class="form-control" id="receiver_name" name="receiver_name" maxlength="200"></div>
                <div class="col-md-3"><label class="form-label" for="receiver_position">ตำแหน่งผู้รับ</label><input class="form-control" id="receiver_position" name="receiver_position" maxlength="200"></div>
                <div class="col-12"><button class="btn btn-primary" type="submit">ส่งคำขอหนังสือ</button></div>
            </form>
        @elseif($isStudent)<p class="text-muted mb-4">เมื่อใบสมัครผ่านการตรวจคุณสมบัติแล้ว จึงจะสร้างคำขอหนังสือได้</p>@endif
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>เลขที่คำขอ</th><th>สถานประกอบการ</th><th>วันที่</th><th>ผลตรวจเอกสาร</th><th>สถานะ</th><th>ตรวจเอกสาร</th></tr></thead><tbody>
            @forelse($letterRequests as $letterRequest)
                <tr><td>{{ $letterRequest->request_no }}</td><td>{{ optional($letterRequest->company)->company_name_th ?: optional($letterRequest->company)->name }}</td><td>{{ optional($letterRequest->request_date)->format('d/m/Y') }}</td><td>{{ $letterRequest->document_check_result ?: 'รอตรวจ' }}</td><td><span class="badge text-bg-light">{{ $letterRequest->request_status }}</span></td><td>
                    @if(($isOfficer || $isAdmin) && $letterRequest->request_status !== 'LETTER_ISSUED')
                        <form method="POST" action="{{ route('workflow.letter-requests.check', $letterRequest->request_id) }}" class="d-flex flex-wrap gap-2">@csrf<select class="form-select form-select-sm" name="document_check_result" aria-label="ผลตรวจเอกสาร"><option value="COMPLETE">เอกสารครบ</option><option value="INCOMPLETE">เอกสารไม่ครบ</option></select><input class="form-control form-control-sm" name="comment" placeholder="หมายเหตุ" maxlength="5000"><button class="btn btn-sm btn-outline-primary" type="submit">บันทึก</button></form>
                    @else<span class="text-muted small">—</span>@endif
                </td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีคำขอหนังสือ</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>
@endif

@if($isStudent || $isOfficer || $isAdmin)
<section class="card mb-4" id="placements">
    <div class="card-header bg-white"><h2 class="h6 mb-1">3. สถานที่ปฏิบัติงาน CWIE 03</h2><span class="small text-muted">บันทึกข้อมูลตำแหน่งงานและผลตอบรับ</span></div>
    <div class="card-body">
        @php($placementApplications = $qualifiedApplications->filter(function ($application) { return !$application->placement; }))
        @if($placementApplications->isNotEmpty())
            <form method="POST" action="{{ route('workflow.placements.store') }}" class="row g-3 mb-4">
                @csrf
                <div class="col-md-4"><label class="form-label" for="placement_application">ใบสมัคร</label><select class="form-select" id="placement_application" name="application_id" required>@foreach($placementApplications as $application)<option value="{{ $application->application_id }}">{{ $application->application_no }} · {{ optional(optional($application->student)->user)->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label" for="job_position">ตำแหน่งงาน</label><input class="form-control" id="job_position" name="job_position" required maxlength="200"></div>
                <div class="col-md-4"><label class="form-label" for="department_name">แผนก</label><input class="form-control" id="department_name" name="department_name" maxlength="200"></div>
                <div class="col-12"><label class="form-label" for="job_description">รายละเอียดงาน</label><textarea class="form-control" id="job_description" name="job_description" rows="2" required maxlength="10000"></textarea></div>
                <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกข้อมูลปฏิบัติงาน</button></div>
            </form>
        @endif
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>นักศึกษา</th><th>สถานประกอบการ</th><th>ตำแหน่ง</th><th>ผลตอบรับ</th><th>ปรับสถานะ</th></tr></thead><tbody>
            @forelse($placements as $placement)
                <tr><td>{{ optional(optional($placement->student)->user)->name ?: optional($placement->student)->student_code }}</td><td>{{ optional($placement->company)->company_name_th ?: optional($placement->company)->name }}</td><td>{{ $placement->job_position }}</td><td>{{ $placement->acceptance_status }}</td><td>
                    @if(($isOfficer || $isAdmin) && $placement->acceptance_status === 'PENDING')
                        <div class="d-flex gap-2"><form method="POST" action="{{ route('workflow.placements.acceptance', $placement->placement_id) }}">@csrf<input type="hidden" name="acceptance_status" value="ACCEPTED"><button class="btn btn-sm btn-success" type="submit">ตอบรับ</button></form><form method="POST" action="{{ route('workflow.placements.acceptance', $placement->placement_id) }}">@csrf<input type="hidden" name="acceptance_status" value="REJECTED"><button class="btn btn-sm btn-outline-danger" type="submit">ปฏิเสธ</button></form></div>
                    @else<span class="text-muted small">—</span>@endif
                </td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูลสถานที่ปฏิบัติงาน</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>
@endif

@if($isStudent)
<section class="card mb-4" id="plans">
    <div class="card-header bg-white"><h2 class="h6 mb-1">4. แผนปฏิบัติงาน CWIE 04</h2><span class="small text-muted">สร้างแผนได้เมื่อสถานประกอบการตอบรับแล้ว</span></div>
    <div class="card-body">
        @php($placementsWithoutPlan = $availablePlacements->filter(function ($placement) { return !$placement->workPlan; }))
        @if($placementsWithoutPlan->isNotEmpty())
            <form method="POST" action="{{ route('workflow.work-plans.store') }}" class="row g-3 mb-4" id="workPlanForm">
                @csrf
                <div class="col-md-5"><label class="form-label" for="work_plan_placement">สถานที่ปฏิบัติงาน</label><select class="form-select" id="work_plan_placement" name="placement_id" required>@foreach($placementsWithoutPlan as $placement)<option value="{{ $placement->placement_id }}">{{ optional($placement->company)->company_name_th ?: optional($placement->company)->name }} · {{ $placement->job_position }}</option>@endforeach</select></div>
                <div class="col-md-7"><label class="form-label" for="plan_title">ชื่อแผนงาน</label><input class="form-control" id="plan_title" name="plan_title" required maxlength="255"></div>
                <div class="col-md-6"><label class="form-label" for="objective">วัตถุประสงค์</label><textarea class="form-control" id="objective" name="objective" rows="2" maxlength="10000"></textarea></div>
                <div class="col-md-6"><label class="form-label" for="expected_result">ผลที่คาดหวัง</label><textarea class="form-control" id="expected_result" name="expected_result" rows="2" maxlength="10000"></textarea></div>
                <div class="col-12"><div class="d-flex justify-content-between align-items-center mb-2"><strong>กิจกรรม</strong><button class="btn btn-sm btn-outline-secondary" type="button" id="addWorkPlanItem"><i data-lucide="plus" class="me-1" style="width:14px;height:14px"></i>เพิ่มกิจกรรม</button></div><div id="workPlanItems">
                    <div class="workflow-item row g-2 mb-2">
                        <div class="col-md-4"><label class="form-label">ชื่องาน</label><input class="form-control" name="items[0][activity_name]" required maxlength="255"></div>
                        <div class="col-md-4"><label class="form-label">รายละเอียด</label><input class="form-control" name="items[0][activity_description]" maxlength="5000"></div>
                        <div class="col-md-2"><label class="form-label">เริ่ม</label><input class="form-control" name="items[0][start_date]" type="date" required></div>
                        <div class="col-md-2"><label class="form-label">สิ้นสุด</label><input class="form-control" name="items[0][end_date]" type="date" required></div>
                        <div class="col-md-6"><label class="form-label">ผลงานที่คาดหวัง</label><input class="form-control" name="items[0][expected_output]" maxlength="5000"></div>
                        <div class="col-md-3"><label class="form-label">น้ำหนัก (%)</label><input class="form-control" name="items[0][weight_percent]" type="number" min="0" max="100" step="0.01"></div>
                    </div>
                </div></div>
                <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกแผนงาน</button></div>
            </form>
        @endif
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>แผนงาน</th><th>สถานประกอบการ</th><th>กิจกรรม</th><th>สถานะ</th><th></th></tr></thead><tbody>
            @forelse($workPlans as $workPlan)
                <tr><td>{{ $workPlan->plan_title }}</td><td>{{ optional(optional($workPlan->placement)->company)->company_name_th ?: optional(optional($workPlan->placement)->company)->name }}</td><td>{{ $workPlan->items->count() }}</td><td>{{ $workPlan->plan_status }}</td><td>@if(in_array($workPlan->plan_status, ['DRAFT', 'REVISION_REQUIRED'], true))<form method="POST" action="{{ route('workflow.work-plans.submit', $workPlan->work_plan_id) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">ส่งพิจารณา</button></form>@endif</td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีแผนปฏิบัติงาน</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>

<section class="card mb-4">
    <div class="card-header bg-white"><h2 class="h6 mb-1">5. โครงร่างรายงาน CWIE 05</h2><span class="small text-muted">จัดทำหลังสถานประกอบการตอบรับแล้ว</span></div>
    <div class="card-body">
        @php($placementsWithoutOutline = $availablePlacements->filter(function ($placement) { return !$placement->reportOutline; }))
        @if($placementsWithoutOutline->isNotEmpty())
            <form method="POST" action="{{ route('workflow.report-outlines.store') }}" class="row g-3 mb-4">
                @csrf
                <div class="col-md-5"><label class="form-label" for="outline_placement">สถานที่ปฏิบัติงาน</label><select class="form-select" id="outline_placement" name="placement_id" required>@foreach($placementsWithoutOutline as $placement)<option value="{{ $placement->placement_id }}">{{ optional($placement->company)->company_name_th ?: optional($placement->company)->name }}</option>@endforeach</select></div>
                <div class="col-md-7"><label class="form-label" for="report_title_th">หัวข้อรายงานภาษาไทย</label><input class="form-control" id="report_title_th" name="report_title_th" required maxlength="255"></div>
                <div class="col-md-6"><label class="form-label" for="report_title_en">หัวข้อภาษาอังกฤษ</label><input class="form-control" id="report_title_en" name="report_title_en" maxlength="255"></div>
                <div class="col-md-6"><label class="form-label" for="report_type">ประเภท</label><select class="form-select" id="report_type" name="report_type"><option value="WORK_REPORT">รายงานการปฏิบัติงาน</option><option value="PROJECT">โครงงาน</option><option value="RESEARCH">วิจัย</option><option value="OTHER">อื่น ๆ</option></select></div>
                @foreach(['background' => 'ที่มาและความสำคัญ', 'objectives' => 'วัตถุประสงค์', 'scope_of_work' => 'ขอบเขตงาน', 'methodology' => 'วิธีดำเนินงาน', 'expected_benefits' => 'ประโยชน์ที่คาดว่าจะได้รับ'] as $field => $label)
                    <div class="col-md-6"><label class="form-label" for="{{ $field }}">{{ $label }}</label><textarea class="form-control" id="{{ $field }}" name="{{ $field }}" rows="2" maxlength="10000"></textarea></div>
                @endforeach
                <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกโครงร่าง</button></div>
            </form>
        @endif
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>หัวข้อรายงาน</th><th>สถานประกอบการ</th><th>สถานะ</th><th></th></tr></thead><tbody>
            @forelse($reportOutlines as $outline)
                <tr><td>{{ $outline->report_title_th }}</td><td>{{ optional(optional($outline->placement)->company)->company_name_th ?: optional(optional($outline->placement)->company)->name }}</td><td>{{ $outline->outline_status }}</td><td>@if(in_array($outline->outline_status, ['DRAFT', 'REVISION_REQUIRED'], true))<form method="POST" action="{{ route('workflow.report-outlines.submit', $outline->outline_id) }}">@csrf<button class="btn btn-sm btn-outline-primary" type="submit">ส่งพิจารณา</button></form>@endif</td></tr>
            @empty<tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีโครงร่างรายงาน</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>
@endif

@if($isAdvisor || $isAdmin || $isSupervisor || $isExecutive)
<section class="card mb-4" id="supervision">
    <div class="card-header bg-white"><h2 class="h6 mb-1">6. คำขอและผลการนิเทศ CWIE 06</h2><span class="small text-muted">กำหนดคณะนิเทศ สถานประกอบการ และวันนัดหมาย</span></div>
    <div class="card-body">
        @if($isAdvisor || $isAdmin || $isSupervisor)
        <form method="POST" action="{{ route('workflow.supervision-requests.store') }}" class="row g-3 mb-4">
            @csrf
            <div class="col-md-4"><label class="form-label" for="supervision_term">ภาคการศึกษา</label><select class="form-select" id="supervision_term" name="term_id" required>@foreach($terms as $term)<option value="{{ $term->term_id }}">ภาค {{ $term->semester_no }} / {{ $term->academic_year }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="supervision_faculty">คณะ</label>@if($isAdmin)<select class="form-select" id="supervision_faculty" name="faculty_id" required>@foreach($faculties as $faculty)<option value="{{ $faculty->id }}">{{ $faculty->name }}</option>@endforeach</select>@else<input type="hidden" name="faculty_id" value="{{ $facultyId }}"><input class="form-control" value="{{ optional(auth()->user()->staffProfile)->faculty_id ? 'คณะในความรับผิดชอบ' : 'ยังไม่ได้กำหนดคณะ' }}" readonly>@endif</div>
            <div class="col-md-4"><label class="form-label" for="supervision_purpose">วัตถุประสงค์</label><input class="form-control" id="supervision_purpose" name="purpose" maxlength="5000"></div>
            <div class="col-md-6"><label class="form-label" for="supervision_member">อาจารย์นิเทศ</label><select class="form-select" id="supervision_member" name="members[0][staff_id]" required>@foreach($staffMembers as $member)<option value="{{ $member->staff_id }}">{{ optional($member->user)->name ?: $member->staff_code }}</option>@endforeach</select><input type="hidden" name="members[0][member_role]" value="LEADER"></div>
            <div class="col-md-6"><label class="form-label" for="supervision_company">สถานประกอบการ</label><select class="form-select" id="supervision_company" name="schedules[0][company_id]" required>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->company_name_th ?: $company->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="supervision_date">วันที่นิเทศ</label><input class="form-control" id="supervision_date" name="schedules[0][supervision_date]" type="date" min="{{ now()->addDay()->toDateString() }}" required></div>
            <div class="col-md-4"><label class="form-label" for="supervision_start">เวลาเริ่ม</label><input class="form-control" id="supervision_start" name="schedules[0][start_time]" type="time"></div>
            <div class="col-md-4"><label class="form-label" for="supervision_end">เวลาสิ้นสุด</label><input class="form-control" id="supervision_end" name="schedules[0][end_time]" type="time"></div>
            <div class="col-12"><label class="form-label" for="appointment_detail">รายละเอียดนัดหมาย</label><textarea class="form-control" id="appointment_detail" name="schedules[0][appointment_detail]" rows="2" maxlength="5000"></textarea></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">ส่งคำขอนิเทศ</button></div>
        </form>
        @endif
        @foreach($supervisionRequests as $supervisionRequest)
            <article class="workflow-record"><div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ $supervisionRequest->request_no }}</strong><span class="badge text-bg-light">{{ $supervisionRequest->request_status }}</span></div><div class="small text-muted mt-1">{{ optional($supervisionRequest->requester)->name }} · {{ $supervisionRequest->schedules->count() }} กำหนดการ</div>
                <div class="table-responsive mt-2"><table class="table table-sm mb-0"><thead><tr><th>สถานประกอบการ</th><th>วัน/เวลา</th><th>สถานะ</th></tr></thead><tbody>@foreach($supervisionRequest->schedules as $schedule)<tr><td>{{ optional($schedule->company)->company_name_th ?: optional($schedule->company)->name }}</td><td>{{ optional($schedule->supervision_date)->format('d/m/Y') }} {{ $schedule->start_time }}</td><td>{{ $schedule->schedule_status }}</td></tr>@endforeach</tbody></table></div>
            </article>
        @endforeach
        @if(($isAdvisor || $isAdmin || $isSupervisor) && $supervisionSchedules->isNotEmpty())
            <hr><h3 class="h6 mb-3">บันทึกผลการนิเทศ</h3>
            @foreach($supervisionSchedules as $schedule)
                <form method="POST" action="{{ route('workflow.supervision-schedules.result', $schedule->schedule_id) }}" class="workflow-record row g-3">
                    @csrf
                    <div class="col-12"><strong>{{ optional($schedule->company)->company_name_th ?: optional($schedule->company)->name }}</strong><span class="small text-muted ms-2">{{ optional($schedule->supervision_date)->format('d/m/Y') }}</span></div>
                    <div class="col-md-4"><label class="form-label" for="visited_at_{{ $schedule->schedule_id }}">วันที่เข้าพบ</label><input class="form-control" id="visited_at_{{ $schedule->schedule_id }}" name="visited_at" type="date" required></div>
                    <div class="col-md-4"><label class="form-label" for="result_{{ $schedule->schedule_id }}">ผลการนิเทศ</label><select class="form-select" id="result_{{ $schedule->schedule_id }}" name="result"><option value="GOOD">ดี</option><option value="EXCELLENT">ดีมาก</option><option value="FAIR">พอใช้</option><option value="IMPROVEMENT_REQUIRED">ควรปรับปรุง</option></select></div>
                    <div class="col-md-4"><label class="form-label" for="performance_{{ $schedule->schedule_id }}">สรุปผลการปฏิบัติงาน</label><input class="form-control" id="performance_{{ $schedule->schedule_id }}" name="performance_summary" maxlength="10000"></div>
                    <div class="col-md-6"><label class="form-label" for="mentor_feedback_{{ $schedule->schedule_id }}">ความเห็นพี่เลี้ยง</label><textarea class="form-control" id="mentor_feedback_{{ $schedule->schedule_id }}" name="mentor_feedback" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label" for="student_feedback_{{ $schedule->schedule_id }}">ความเห็นนักศึกษา</label><textarea class="form-control" id="student_feedback_{{ $schedule->schedule_id }}" name="student_feedback" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label" for="problems_{{ $schedule->schedule_id }}">ปัญหาที่พบ</label><textarea class="form-control" id="problems_{{ $schedule->schedule_id }}" name="problems_found" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label" for="recommendations_{{ $schedule->schedule_id }}">ข้อเสนอแนะ</label><textarea class="form-control" id="recommendations_{{ $schedule->schedule_id }}" name="recommendations" rows="2"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">บันทึกผลนิเทศ</button></div>
                </form>
            @endforeach
        @endif
    </div>
</section>
@endif

<section class="card mb-4" id="approvals">
    <div class="card-header bg-white"><h2 class="h6 mb-1">รายการรอพิจารณา</h2><span class="small text-muted">แสดงรายการที่มอบหมายให้บัญชีนี้</span></div>
    <div class="card-body">
        @forelse($approvals as $approval)
            <form method="POST" action="{{ route('workflow.approvals.action', $approval->approval_id) }}" class="workflow-record row g-3 align-items-end">
                @csrf
                <div class="col-md-3"><span class="small text-muted">{{ $approval->document_type }}</span><div><strong>#{{ $approval->document_id }}</strong> · ขั้น {{ $approval->approval_step }}</div></div>
                <div class="col-md-3"><label class="form-label" for="approval_action_{{ $approval->approval_id }}">ผลพิจารณา</label><select class="form-select" id="approval_action_{{ $approval->approval_id }}" name="action"><option value="APPROVED">อนุมัติ</option><option value="REVISION_REQUIRED">ขอแก้ไข</option><option value="REJECTED">ปฏิเสธ</option></select></div>
                <div class="col-md-4"><label class="form-label" for="approval_comment_{{ $approval->approval_id }}">ความเห็น (ต้องระบุเมื่อขอแก้ไข/ปฏิเสธ)</label><input class="form-control" id="approval_comment_{{ $approval->approval_id }}" name="comment" maxlength="5000"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">บันทึกผล</button></div>
            </form>
        @empty<p class="text-muted mb-0">ไม่มีรายการที่รอพิจารณา</p>@endforelse
    </div>
</section>

<section class="card mb-4" id="documents">
    <div class="card-header bg-white"><h2 class="h6 mb-1">เอกสารประกอบ workflow</h2><span class="small text-muted">ไฟล์จัดเก็บบน private disk ตามกติกาเดิม</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('workflow.documents.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-3"><label class="form-label" for="related_type">ประเภทข้อมูล</label><select class="form-select" id="related_type" name="related_type" required><option value="APPLICATION">ใบสมัคร</option><option value="LETTER_REQUEST">คำขอหนังสือ</option><option value="PLACEMENT">สถานที่ปฏิบัติงาน</option><option value="WORK_PLAN">แผนปฏิบัติงาน</option><option value="REPORT_OUTLINE">โครงร่างรายงาน</option><option value="SUPERVISION_REQUEST">คำขอนิเทศ</option></select></div>
            <div class="col-md-3"><label class="form-label" for="related_id">รหัสรายการ</label><input class="form-control" id="related_id" name="related_id" type="number" min="1" required></div>
            <div class="col-md-3"><label class="form-label" for="document_name">ชื่อเอกสาร</label><input class="form-control" id="document_name" name="document_name" required maxlength="255"></div>
            <div class="col-md-3"><label class="form-label" for="workflow_file">ไฟล์</label><input class="form-control" id="workflow_file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required></div>
            <div class="col-md-6"><label class="form-label" for="document_category">หมวดเอกสาร</label><input class="form-control" id="document_category" name="document_category" maxlength="100"></div>
            <div class="col-12"><button class="btn btn-outline-primary" type="submit">อัปโหลดเอกสาร</button></div>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
    .workflow-nav{display:flex;flex-wrap:wrap;gap:8px}
    .workflow-record{padding:16px 0;border-bottom:1px solid var(--slate-200,#e2e8f0)}
    .workflow-record:last-child{border-bottom:0}
    .workflow-item{padding:14px;border:1px solid var(--slate-200,#e2e8f0);border-radius:6px;background:var(--slate-50,#f8fafc)}
    .workflow-item .form-label{font-size:.82rem}
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('workPlanItems');
        const addButton = document.getElementById('addWorkPlanItem');
        if (!list || !addButton) return;
        addButton.addEventListener('click', function () {
            const index = list.querySelectorAll('.workflow-item').length;
            const row = list.querySelector('.workflow-item').cloneNode(true);
            row.querySelectorAll('input').forEach(function (input) {
                input.value = '';
                input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
            });
            list.appendChild(row);
        });
    })();
</script>
@endpush