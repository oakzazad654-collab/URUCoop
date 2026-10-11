<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\CoopApplication;
use App\Models\DocumentRecord;
use App\Models\LetterRequest;
use App\Models\Placement;
use App\Models\ReportOutline;
use App\Models\Student;
use App\Models\SupervisionRequest;
use App\Models\SupervisionResult;
use App\Models\SupervisionSchedule;
use App\Models\User;
use App\Models\WorkPlan;
use App\Notifications\CwieStatusNotification;
use App\Services\DataAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CwieWorkflowController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function letterRequests(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
        $query = $this->access->letterRequests($user)->with([
            'company',
            'students' => function ($students) use ($user) {
                if ($user->role === 'student') {
                    $students->where('students.user_id', $user->id);
                }
                $students->with('user:id,name');
            },
        ]);

        return response()->json($query->latest('request_id')->paginate(25));
    }

    public function createLetterRequest(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'advisor', 'officer', 'admin', 'executive'], true), 403);
        $validated = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'required|integer|distinct|exists:coop_applications,application_id',
            'receiver_name' => 'nullable|string|max:200',
            'receiver_position' => 'nullable|string|max:200',
        ]);

        $student = $request->user()->studentProfile;
        $applications = CoopApplication::whereIn('application_id', $validated['application_ids'])
            ->with('student.user')
            ->where('application_status', 'APPROVED')
            ->where('qualification_result', 'QUALIFIED')
            ->get();
        abort_unless($applications->count() === count($validated['application_ids']), 422, 'CWIE 02 ต้องอ้างอิงใบสมัครที่ผ่านคุณสมบัติแล้ว');
        if ($user->role === 'student') {
            abort_unless($student, 422, 'ยังไม่มีข้อมูลประวัตินักศึกษาในระบบ');
            abort_unless($applications->every(function ($application) use ($student) {
                return (int) $application->student_id === (int) $student->student_id;
            }), 403);
        } elseif (!$this->access->isGlobalOperator($user)) {
            abort_unless($applications->every(function ($application) use ($user) {
                return $this->access->canReviewApplication($user, $application);
            }), 403);
        }

        $first = $applications->first();
        abort_unless($applications->every(function ($application) use ($first) {
            return (int) $application->preferred_company_id === (int) $first->preferred_company_id
                && (int) $application->term_id === (int) $first->term_id;
        }), 422, 'คำขอหนึ่งรายการต้องเป็นสถานประกอบการและภาคการศึกษาเดียวกัน');
        $coordinatorId = optional($first->student->user)->advisor_id;
        abort_unless($coordinatorId, 422, 'ไม่พบอาจารย์ผู้ประสานงานของนักศึกษา');
        abort_unless($applications->every(function ($application) use ($coordinatorId) {
            return (int) optional($application->student->user)->advisor_id === (int) $coordinatorId;
        }), 422, 'นักศึกษาที่รวมในคำขอต้องอยู่ภายใต้ผู้ประสานงานคนเดียวกัน');

        $letterRequest = DB::transaction(function () use ($request, $validated, $applications, $first, $coordinatorId) {
            $term = $first->term;
            $letterRequest = LetterRequest::create([
                'request_no' => 'LTR-'.$term->academic_year.'-'.Str::upper(Str::random(8)),
                'company_id' => $first->preferred_company_id,
                'term_id' => $first->term_id,
                'receiver_name' => $validated['receiver_name'] ?? null,
                'receiver_position' => $validated['receiver_position'] ?? null,
                'request_date' => now()->toDateString(),
                'work_start_date' => $applications->min('work_start_date'),
                'work_end_date' => $applications->max('work_end_date'),
                'request_status' => 'SUBMITTED',
                'created_by' => $request->user()->id,
            ]);

            foreach ($applications as $application) {
                $letterRequest->students()->attach($application->student_id, [
                    'application_id' => $application->application_id,
                ]);
            }
            Approval::create([
                'document_type' => 'LETTER_REQUEST',
                'document_id' => $letterRequest->request_id,
                'approval_step' => 1,
                'approver_id' => $coordinatorId,
                'approver_role' => 'COORDINATOR',
                'approval_status' => 'PENDING',
            ]);
            $this->audit($request, 'LETTER_REQUEST_SUBMITTED', 'letter_requests', $letterRequest->request_id, null, ['request_no' => $letterRequest->request_no]);

            return $letterRequest;
        });
        $coordinator = User::find($coordinatorId);
        if ($coordinator) {
            $coordinator->notify(new CwieStatusNotification(
                'มีคำขอหนังสือรอพิจารณา',
                'มีคำขอ CWIE 02 รายการใหม่รอพิจารณา',
                'LETTER_REQUEST',
                $letterRequest->request_id
            ));
        }

        return response()->json($letterRequest->load([
            'company',
            'students' => function ($students) use ($user) {
                if ($user->role === 'student') {
                    $students->where('students.user_id', $user->id);
                }
            },
        ]), 201);
    }

    public function checkLetterDocuments(Request $request, LetterRequest $letterRequest)
    {
        abort_unless(in_array($request->user()->role, ['officer', 'admin'], true), 403);
        abort_unless($this->access->canAccessLetterRequest($request->user(), $letterRequest), 403);
        $validated = $request->validate([
            'document_check_result' => 'required|in:COMPLETE,INCOMPLETE',
            'comment' => 'nullable|string|max:5000',
        ]);

        $letterRequest->document_check_result = $validated['document_check_result'];
        $letterRequest->request_status = $validated['document_check_result'] === 'COMPLETE'
            && $letterRequest->coordinator_result === 'APPROVED'
            ? 'LETTER_ISSUED'
            : 'CHECKING';
        $letterRequest->save();
        Approval::create([
            'document_type' => 'LETTER_REQUEST',
            'document_id' => $letterRequest->request_id,
            'approval_step' => 2,
            'approver_id' => $request->user()->id,
            'approver_role' => $request->user()->role,
            'approval_status' => $validated['document_check_result'] === 'COMPLETE' ? 'APPROVED' : 'REVISION_REQUIRED',
            'comment' => $validated['comment'] ?? null,
            'action_at' => now(),
        ]);
        $this->audit($request, 'LETTER_DOCUMENTS_CHECKED', 'letter_requests', $letterRequest->request_id, null, ['document_check_result' => $letterRequest->document_check_result]);
        foreach ($letterRequest->students()->with('user')->get() as $student) {
            if ($student->user) {
                $student->user->notify(new CwieStatusNotification(
                    'ตรวจเอกสารคำขอหนังสือแล้ว',
                    $letterRequest->request_status === 'LETTER_ISSUED'
                        ? 'เอกสารครบถ้วนและออกหนังสือเรียบร้อยแล้ว'
                        : 'มีการบันทึกผลตรวจเอกสารคำขอ CWIE 02',
                    'LETTER_REQUEST',
                    $letterRequest->request_id
                ));
            }
        }

        return response()->json($letterRequest->fresh());
    }

    public function placements(Request $request)
    {
        return response()->json($this->access->placements($request->user())
            ->with(['student.user:id,name', 'company:id,name,company_name_th', 'application'])
            ->latest('placement_id')->paginate(25));
    }

    public function createPlacement(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'application_id' => 'required|exists:coop_applications,application_id',
            'job_position' => 'required|string|max:200',
            'job_description' => 'required|string|max:10000',
            'department_name' => 'nullable|string|max:200',
            'coordinator_contact_id' => 'nullable|exists:company_contacts,contact_id',
            'mentor_contact_id' => 'nullable|exists:company_contacts,contact_id',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_address' => 'nullable|string|max:5000',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_email' => 'nullable|email|max:150',
        ]);

        $application = CoopApplication::findOrFail($validated['application_id']);
        if ($user->role === 'student') {
            abort_unless((int) optional($user->studentProfile)->student_id === (int) $application->student_id, 403);
        } else {
            abort_unless(in_array($user->role, ['officer', 'admin'], true), 403);
            abort_unless($this->access->canReviewApplication($user, $application), 403);
        }
        foreach (['coordinator_contact_id', 'mentor_contact_id'] as $contactField) {
            if (!empty($validated[$contactField])) {
                $contactQuery = DB::table('company_contacts')
                    ->where('contact_id', $validated[$contactField])
                    ->where('company_id', $application->preferred_company_id)
                    ->where('status', 'ACTIVE');
                if ($contactField === 'mentor_contact_id') {
                    $contactQuery->where('contact_type', 'MENTOR');
                }
                abort_unless($contactQuery->exists(), 422, 'ผู้ติดต่อที่เลือกไม่ได้สังกัดสถานประกอบการของใบสมัคร');
            }
        }
        abort_unless($application->application_status === 'APPROVED'
            && $application->qualification_result === 'QUALIFIED', 422, 'ต้องผ่าน CWIE 01 ก่อนบันทึก CWIE 03');
        abort_if(Placement::where('application_id', $application->application_id)->exists(), 422, 'มีข้อมูล CWIE 03 สำหรับใบสมัครนี้แล้ว');

        $placement = Placement::create(array_merge($validated, [
            'placement_no' => 'PLC-'.$application->term->academic_year.'-'.Str::upper(Str::random(8)),
            'student_id' => $application->student_id,
            'company_id' => $application->preferred_company_id,
            'term_id' => $application->term_id,
            'work_start_date' => $application->work_start_date,
            'work_end_date' => $application->work_end_date,
            'acceptance_status' => 'PENDING',
            'placement_status' => 'PREPARING',
        ]));
        $this->audit($request, 'PLACEMENT_CREATED', 'placements', $placement->placement_id, null, ['placement_no' => $placement->placement_no]);

        return response()->json($placement, 201);
    }

    public function updatePlacementAcceptance(Request $request, Placement $placement)
    {
        abort_unless(in_array($request->user()->role, ['company', 'officer', 'admin'], true), 403);
        abort_unless($this->access->canAccessPlacement($request->user(), $placement), 403);
        if ($request->user()->role === 'company') {
            abort_unless($placement->acceptance_status === 'PENDING', 422, 'บันทึกผลตอบรับรายการนี้แล้ว');
        }
        $validated = $request->validate(['acceptance_status' => 'required|in:ACCEPTED,REJECTED']);
        $placement->acceptance_status = $validated['acceptance_status'];
        $placement->placement_status = $validated['acceptance_status'] === 'ACCEPTED' ? 'WORKING' : 'PREPARING';
        $placement->confirmed_at = now();
        $placement->save();
        $this->audit($request, 'PLACEMENT_ACCEPTANCE_UPDATED', 'placements', $placement->placement_id, null, $validated);
        $studentUser = $placement->student->user;
        if ($studentUser) {
            $studentUser->notify(new CwieStatusNotification(
                'ผลการตอบรับสถานประกอบการ',
                $placement->acceptance_status === 'ACCEPTED' ? 'สถานประกอบการตอบรับการปฏิบัติงานแล้ว' : 'สถานประกอบการปฏิเสธการรับนักศึกษา',
                'PLACEMENT',
                $placement->placement_id
            ));
        }

        return response()->json($placement->fresh());
    }

    public function workPlans(Request $request)
    {
        $placementIds = $this->access->placements($request->user())->select('placement_id');
        return response()->json(WorkPlan::with('items')->whereIn('placement_id', $placementIds)->paginate(25));
    }

    public function createWorkPlan(Request $request)
    {
        $validated = $request->validate([
            'placement_id' => 'required|exists:placements,placement_id',
            'plan_title' => 'required|string|max:255',
            'objective' => 'nullable|string|max:10000',
            'expected_result' => 'nullable|string|max:10000',
            'items' => 'required|array|min:1',
            'items.*.activity_name' => 'required|string|max:255',
            'items.*.activity_description' => 'nullable|string|max:5000',
            'items.*.start_date' => 'required|date',
            'items.*.end_date' => 'required|date',
            'items.*.expected_output' => 'nullable|string|max:5000',
            'items.*.weight_percent' => 'nullable|numeric|between:0,100',
        ]);

        $placement = Placement::findOrFail($validated['placement_id']);
        abort_unless($request->user()->role === 'student'
            && (int) optional($request->user()->studentProfile)->student_id === (int) $placement->student_id, 403);
        abort_unless($placement->acceptance_status === 'ACCEPTED', 422, 'CWIE 04 ต้องผูกกับสถานประกอบการที่ตอบรับแล้ว');
        abort_if(WorkPlan::where('placement_id', $placement->placement_id)->exists(), 422, 'มีแผนปฏิบัติงานสำหรับ placement นี้แล้ว');
        $hasWeights = collect($validated['items'])->contains(function ($item) {
            return isset($item['weight_percent']);
        });
        if ($hasWeights && abs((float) collect($validated['items'])->sum('weight_percent') - 100) > 0.01) {
            return response()->json(['message' => 'ผลรวมน้ำหนักกิจกรรมต้องเท่ากับ 100%'], 422);
        }

        $workPlan = DB::transaction(function () use ($request, $validated, $placement) {
            $workPlan = WorkPlan::create([
                'placement_id' => $placement->placement_id,
                'plan_title' => $validated['plan_title'],
                'objective' => $validated['objective'] ?? null,
                'expected_result' => $validated['expected_result'] ?? null,
                'plan_status' => 'DRAFT',
            ]);
            foreach ($validated['items'] as $index => $item) {
                abort_unless($item['end_date'] >= $item['start_date'], 422, 'วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มต้น');
                abort_unless($item['start_date'] >= $placement->work_start_date->toDateString()
                    && $item['end_date'] <= $placement->work_end_date->toDateString(), 422, 'กิจกรรมต้องอยู่ภายในช่วงปฏิบัติงาน');
                $workPlan->items()->create(array_merge($item, ['sequence_no' => $index + 1]));
            }
            $this->audit($request, 'WORK_PLAN_CREATED', 'work_plans', $workPlan->work_plan_id, null, ['plan_status' => 'DRAFT']);
            return $workPlan;
        });

        return response()->json($workPlan->load('items'), 201);
    }

    public function submitWorkPlan(Request $request, WorkPlan $workPlan)
    {
        $user = $request->user();
        $placement = $workPlan->placement;
        abort_unless($user->role === 'student'
            && (int) optional($user->studentProfile)->student_id === (int) optional($placement)->student_id, 403);
        abort_unless(in_array($workPlan->plan_status, ['DRAFT', 'REVISION_REQUIRED'], true), 422, 'สถานะแผนงานไม่สามารถส่งได้');
        $advisorId = $user->advisor_id;
        abort_unless($advisorId, 422, 'ไม่พบอาจารย์ที่ปรึกษาสำหรับส่งแผนงาน');
        $items = $workPlan->items;
        abort_unless($items->isNotEmpty(), 422, 'แผนปฏิบัติงานต้องมีกิจกรรมอย่างน้อยหนึ่งรายการ');
        $weights = $items->pluck('weight_percent')->filter(function ($weight) {
            return $weight !== null;
        });
        if ($weights->isNotEmpty() && abs((float) $weights->sum() - 100) > 0.01) {
            return response()->json(['message' => 'ผลรวมน้ำหนักกิจกรรมต้องเท่ากับ 100%'], 422);
        }

        $mentorId = $placement && $placement->mentor_contact_id
            ? DB::table('company_contacts')->where('contact_id', $placement->mentor_contact_id)
                ->where('status', 'ACTIVE')->value('user_id')
            : null;
        $approverId = $mentorId ?: $advisorId;
        $workPlan->plan_status = $mentorId ? 'PENDING_MENTOR' : 'SUBMITTED';
        $workPlan->student_confirmed_at = now();
        $workPlan->save();
        $approval = Approval::create([
            'document_type' => 'WORK_PLAN',
            'document_id' => $workPlan->work_plan_id,
            'approval_step' => 1,
            'approver_id' => $approverId,
            'approver_role' => $mentorId ? 'MENTOR' : 'ADVISOR',
            'approval_status' => 'PENDING',
        ]);
        $approver = User::find($approverId);
        if ($approver) {
            $approver->notify(new CwieStatusNotification(
                'มีแผนปฏิบัติงานรอตรวจ',
                $mentorId ? 'นักศึกษาส่งแผนปฏิบัติงานให้ผู้นิเทศงานตรวจสอบ' : 'นักศึกษาส่งแผนปฏิบัติงาน CWIE 04 ให้พิจารณา',
                'WORK_PLAN',
                $workPlan->work_plan_id
            ));
        }
        $this->audit($request, 'WORK_PLAN_SUBMITTED', 'work_plans', $workPlan->work_plan_id, null, ['approval_id' => $approval->approval_id]);

        return response()->json($workPlan->fresh());
    }

    public function reportOutlines(Request $request)
    {
        $placementIds = $this->access->placements($request->user())->select('placement_id');
        return response()->json(ReportOutline::whereIn('placement_id', $placementIds)->paginate(25));
    }

    public function createReportOutline(Request $request)
    {
        $validated = $request->validate([
            'placement_id' => 'required|exists:placements,placement_id',
            'report_title_th' => 'required|string|max:255',
            'report_title_en' => 'nullable|string|max:255',
            'report_type' => 'nullable|in:PROJECT,RESEARCH,WORK_REPORT,OTHER',
            'background' => 'nullable|string|max:10000',
            'objectives' => 'nullable|string|max:10000',
            'scope_of_work' => 'nullable|string|max:10000',
            'methodology' => 'nullable|string|max:10000',
            'expected_benefits' => 'nullable|string|max:10000',
        ]);
        $placement = Placement::findOrFail($validated['placement_id']);
        abort_unless($request->user()->role === 'student'
            && (int) optional($request->user()->studentProfile)->student_id === (int) $placement->student_id, 403);
        abort_unless($placement->acceptance_status === 'ACCEPTED', 422, 'CWIE 05 ต้องผูกกับสถานประกอบการที่ตอบรับแล้ว');
        abort_if(ReportOutline::where('placement_id', $placement->placement_id)->exists(), 422, 'มีโครงร่างรายงานสำหรับ placement นี้แล้ว');

        $outline = ReportOutline::create(array_merge($validated, [
            'report_type' => $validated['report_type'] ?? 'WORK_REPORT',
            'outline_status' => 'DRAFT',
        ]));
        $this->audit($request, 'REPORT_OUTLINE_CREATED', 'report_outlines', $outline->outline_id, null, ['outline_status' => 'DRAFT']);

        return response()->json($outline, 201);
    }

    public function submitReportOutline(Request $request, ReportOutline $outline)
    {
        $user = $request->user();
        $placement = $outline->placement;
        abort_unless($user->role === 'student'
            && (int) optional($user->studentProfile)->student_id === (int) optional($placement)->student_id, 403);
        abort_unless(in_array($outline->outline_status, ['DRAFT', 'REVISION_REQUIRED'], true), 422, 'สถานะโครงร่างรายงานไม่สามารถส่งได้');
        $advisorId = $outline->advisor_id ?: $user->advisor_id;
        abort_unless($advisorId, 422, 'ไม่พบอาจารย์ที่ปรึกษาสำหรับส่งโครงร่างรายงาน');

        $outline->outline_status = 'SUBMITTED';
        $outline->student_confirmed_at = now();
        $outline->advisor_id = $advisorId;
        $outline->save();
        $approval = Approval::create([
            'document_type' => 'REPORT_OUTLINE',
            'document_id' => $outline->outline_id,
            'approval_step' => 1,
            'approver_id' => $advisorId,
            'approver_role' => 'ADVISOR',
            'approval_status' => 'PENDING',
        ]);
        $advisor = User::find($advisorId);
        if ($advisor) {
            $advisor->notify(new CwieStatusNotification(
                'มีโครงร่างรายงานรอตรวจ',
                'นักศึกษาส่งโครงร่างรายงาน CWIE 05 ให้พิจารณา',
                'REPORT_OUTLINE',
                $outline->outline_id
            ));
        }
        $this->audit($request, 'REPORT_OUTLINE_SUBMITTED', 'report_outlines', $outline->outline_id, null, ['approval_id' => $approval->approval_id]);

        return response()->json($outline->fresh());
    }

    public function supervisionRequests(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
        $query = $this->access->supervisionRequests($user)->with(['schedules.company', 'schedules.placement']);
        return response()->json($query->latest('supervision_request_id')->paginate(25));
    }

    public function createSupervisionRequest(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['advisor', 'supervisor', 'admin'], true), 403);
        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,term_id',
            'faculty_id' => 'required|exists:faculties,id',
            'purpose' => 'nullable|string|max:5000',
            'members' => 'required|array|min:1',
            'members.*.staff_id' => 'required|integer|distinct|exists:staff,staff_id',
            'members.*.member_role' => 'nullable|in:LEADER,SUPERVISOR,MEMBER',
            'schedules' => 'required|array|min:1',
            'schedules.*.company_id' => 'required|exists:companies,id',
            'schedules.*.placement_id' => 'nullable|exists:placements,placement_id',
            'schedules.*.supervision_date' => 'required|date|after_or_equal:today',
            'schedules.*.start_time' => 'nullable|date_format:H:i',
            'schedules.*.end_time' => 'nullable|date_format:H:i',
            'schedules.*.appointment_detail' => 'nullable|string|max:5000',
            'schedules.*.province' => 'nullable|string|max:100',
        ]);
        if ($user->role !== 'admin') {
            abort_unless((int) $this->access->facultyId($user) === (int) $validated['faculty_id'], 403);
        }
        $staffCount = DB::table('staff')->whereIn('staff_id', collect($validated['members'])->pluck('staff_id'))
            ->where('faculty_id', $validated['faculty_id'])->count();
        abort_unless($staffCount === count($validated['members']), 422, 'คณะผู้นิเทศต้องอยู่ในคณะที่ระบุ');
        foreach ($validated['schedules'] as $schedule) {
            if ($schedule['start_time'] && $schedule['end_time']) {
                abort_unless($schedule['end_time'] > $schedule['start_time'], 422, 'เวลาสิ้นสุดต้องอยู่หลังเวลาเริ่มต้น');
            }
            if (!empty($schedule['placement_id'])) {
                abort_unless(Placement::where('placement_id', $schedule['placement_id'])
                    ->where('company_id', $schedule['company_id'])->exists(), 422, 'สถานประกอบการไม่ตรงกับ placement');
            }
        }

        $today = now()->startOfDay();
        $warnings = collect($validated['schedules'])->filter(function ($schedule) use ($today) {
            return $today->diffInDays($schedule['supervision_date'], false) < 7;
        })->count();
        $approver = User::where('role', 'executive')->where('id', '<>', $user->id)->first()
            ?: User::where('role', 'admin')->where('id', '<>', $user->id)->first();
        abort_unless($approver, 422, 'ไม่พบผู้บริหารหรือผู้ดูแลระบบที่รับคำขอนิเทศได้');

        $supervisionRequest = DB::transaction(function () use ($request, $validated, $approver) {
            $supervisionRequest = SupervisionRequest::create([
                'request_no' => 'SUP-'.date('Y').'-'.Str::upper(Str::random(8)),
                'term_id' => $validated['term_id'],
                'requester_id' => $request->user()->id,
                'faculty_id' => $validated['faculty_id'],
                'request_date' => now()->toDateString(),
                'purpose' => $validated['purpose'] ?? null,
                'request_status' => 'SUBMITTED',
            ]);
            foreach ($validated['members'] as $member) {
                DB::table('supervision_members')->insert([
                    'supervision_request_id' => $supervisionRequest->supervision_request_id,
                    'staff_id' => $member['staff_id'],
                    'member_role' => $member['member_role'] ?? 'MEMBER',
                ]);
            }
            foreach ($validated['schedules'] as $schedule) {
                SupervisionSchedule::create(array_merge($schedule, [
                    'supervision_request_id' => $supervisionRequest->supervision_request_id,
                    'schedule_status' => 'PLANNED',
                ]));
            }
            Approval::create([
                'document_type' => 'SUPERVISION_REQUEST',
                'document_id' => $supervisionRequest->supervision_request_id,
                'approval_step' => 1,
                'approver_id' => $approver->id,
                'approver_role' => $approver->role,
                'approval_status' => 'PENDING',
            ]);
            $this->audit($request, 'SUPERVISION_REQUEST_SUBMITTED', 'supervision_requests', $supervisionRequest->supervision_request_id, null, ['request_no' => $supervisionRequest->request_no]);
            return $supervisionRequest;
        });
        $approver->notify(new CwieStatusNotification(
            'มีคำขอนิเทศรออนุมัติ',
            'มีคำขอออกนิเทศ CWIE 06 รายการใหม่รออนุมัติ',
            'SUPERVISION_REQUEST',
            $supervisionRequest->supervision_request_id
        ));
        $memberUserIds = DB::table('supervision_members')
            ->join('staff', 'staff.staff_id', '=', 'supervision_members.staff_id')
            ->where('supervision_request_id', $supervisionRequest->supervision_request_id)
            ->pluck('staff.user_id');
        foreach (User::whereIn('id', $memberUserIds)->get() as $member) {
            $member->notify(new CwieStatusNotification(
                'มีคำขอออกนิเทศ',
                'คุณถูกเพิ่มเป็นสมาชิกคณะนิเทศในคำขอรายการใหม่',
                'SUPERVISION_REQUEST',
                $supervisionRequest->supervision_request_id
            ));
        }

        return response()->json([
            'data' => $supervisionRequest->load('schedules'),
            'warnings' => $warnings ? ['กำหนดการบางรายการส่งคำขอล่วงหน้าน้อยกว่า 7 วัน'] : [],
        ], 201);
    }

    public function createSupervisionResult(Request $request, SupervisionSchedule $schedule)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['advisor', 'supervisor', 'admin'], true), 403);
        $supervisionRequest = SupervisionRequest::findOrFail($schedule->supervision_request_id);
        abort_unless($this->access->canAccessSupervisionRequest($user, $supervisionRequest), 403);
        if ($user->role !== 'admin') {
            $isMember = DB::table('supervision_members')
                ->join('staff', 'staff.staff_id', '=', 'supervision_members.staff_id')
                ->where('supervision_members.supervision_request_id', $schedule->supervision_request_id)
                ->where('staff.user_id', $user->id)
                ->exists();
            abort_unless($isMember, 403);
        }
        abort_if($schedule->result()->exists(), 422, 'ผลการนิเทศถูกบันทึกแล้ว');
        $validated = $request->validate([
            'performance_summary' => 'nullable|string|max:10000',
            'mentor_feedback' => 'nullable|string|max:10000',
            'student_feedback' => 'nullable|string|max:10000',
            'problems_found' => 'nullable|string|max:10000',
            'recommendations' => 'nullable|string|max:10000',
            'result' => 'nullable|in:EXCELLENT,GOOD,FAIR,IMPROVEMENT_REQUIRED',
            'visited_at' => 'required|date',
        ]);
        $result = $schedule->result()->create(array_merge($validated, ['supervisor_id' => $user->id]));
        $schedule->schedule_status = 'COMPLETED';
        $schedule->save();
        $this->audit($request, 'SUPERVISION_RESULT_RECORDED', 'supervision_results', $result->supervision_result_id, null, ['result' => $result->result]);
        $studentUser = optional(optional($schedule->placement)->student)->user;
        if ($studentUser) {
            $studentUser->notify(new CwieStatusNotification(
                'ผลการนิเทศบันทึกแล้ว',
                'อาจารย์บันทึกผลการนิเทศและข้อเสนอแนะเรียบร้อยแล้ว',
                'SUPERVISION_RESULT',
                $result->supervision_result_id
            ));
        }

        return response()->json($result, 201);
    }

    public function actOnApproval(Request $request, Approval $approval)
    {
        $user = $request->user();
        abort_unless((int) $approval->approver_id === (int) $user->id || $user->role === 'admin', 403);
        abort_unless($this->access->canAccessApproval($user, $approval), 403);
        abort_unless($approval->approval_status === 'PENDING', 422, 'รายการอนุมัตินี้ถูกดำเนินการแล้ว');
        $validated = $request->validate([
            'action' => 'required|in:APPROVED,REJECTED,REVISION_REQUIRED',
            'comment' => 'required_unless:action,APPROVED|string|max:5000',
        ]);

        DB::transaction(function () use ($request, $approval, $validated) {
            $approval->approval_status = $validated['action'];
            $approval->comment = $validated['comment'] ?? null;
            $approval->action_at = now();
            $approval->save();

            $decision = $validated['action'];
            if ($approval->document_type === 'APPLICATION') {
                $application = CoopApplication::findOrFail($approval->document_id);
                $application->qualification_result = $decision === 'APPROVED' ? 'QUALIFIED'
                    : ($decision === 'REJECTED' ? 'NOT_QUALIFIED' : 'PENDING');
                $application->application_status = $decision === 'APPROVED' ? 'APPROVED'
                    : ($decision === 'REJECTED' ? 'REJECTED' : 'UNDER_REVIEW');
                $application->save();
            } elseif ($approval->document_type === 'LETTER_REQUEST' && (int) $approval->approval_step === 1) {
                $letterRequest = LetterRequest::findOrFail($approval->document_id);
                $letterRequest->coordinator_result = $decision;
                $letterRequest->request_status = $decision === 'APPROVED'
                    ? ($letterRequest->document_check_result === 'COMPLETE' ? 'LETTER_ISSUED' : 'CHECKING')
                    : ($decision === 'REJECTED' ? 'REJECTED' : 'SUBMITTED');
                $letterRequest->save();
            } elseif ($approval->document_type === 'LETTER_REQUEST') {
                $letterRequest = LetterRequest::findOrFail($approval->document_id);
                $documentResult = $decision === 'APPROVED' ? 'COMPLETE' : 'INCOMPLETE';
                $letterRequest->document_check_result = $documentResult;
                $letterRequest->request_status = $decision === 'APPROVED'
                    && $letterRequest->coordinator_result === 'APPROVED' ? 'LETTER_ISSUED' : 'CHECKING';
                $letterRequest->save();
            } elseif ($approval->document_type === 'PLACEMENT') {
                $placement = Placement::findOrFail($approval->document_id);
                $placement->acceptance_status = $decision === 'APPROVED' ? 'ACCEPTED'
                    : ($decision === 'REJECTED' ? 'REJECTED' : 'PENDING');
                $placement->placement_status = $decision === 'APPROVED' ? 'WORKING' : 'PREPARING';
                $placement->confirmed_at = now();
                $placement->save();
            } elseif ($approval->document_type === 'WORK_PLAN' && $approval->approver_role === 'MENTOR') {
                $workPlan = WorkPlan::with('placement.student.user')->findOrFail($approval->document_id);
                if ($decision === 'APPROVED') {
                    $advisorId = optional(optional($workPlan->placement->student)->user)->advisor_id;
                    abort_unless($advisorId, 422, 'ไม่พบอาจารย์ที่ปรึกษาสำหรับตรวจแผนงานต่อ');
                    $workPlan->mentor_confirmed_at = now();
                    $workPlan->plan_status = 'PENDING_ADVISOR';
                    $workPlan->save();
                    $advisor = User::findOrFail($advisorId);
                    $nextApproval = Approval::create([
                        'document_type' => 'WORK_PLAN',
                        'document_id' => $workPlan->work_plan_id,
                        'approval_step' => 2,
                        'approver_id' => $advisor->id,
                        'approver_role' => 'ADVISOR',
                        'approval_status' => 'PENDING',
                    ]);
                    $advisor->notify(new CwieStatusNotification(
                        'มีแผนปฏิบัติงานรอตรวจ',
                        'ผู้นิเทศงานตรวจแผนปฏิบัติงานแล้ว กรุณาพิจารณาต่อ',
                        'WORK_PLAN',
                        $workPlan->work_plan_id
                    ));
                } else {
                    $workPlan->plan_status = 'REVISION_REQUIRED';
                    $workPlan->reviewer_comment = $validated['comment'];
                    $workPlan->save();
                }
            } else {
                $targetStatus = $decision === 'APPROVED' ? 'APPROVED'
                    : ($approval->document_type === 'SUPERVISION_REQUEST' && $decision === 'REJECTED' ? 'REJECTED' : 'REVISION_REQUIRED');
                $tables = [
                    'WORK_PLAN' => ['work_plans', 'work_plan_id', 'plan_status'],
                    'REPORT_OUTLINE' => ['report_outlines', 'outline_id', 'outline_status'],
                    'SUPERVISION_REQUEST' => ['supervision_requests', 'supervision_request_id', 'request_status'],
                ];
                if (isset($tables[$approval->document_type])) {
                    [$table, $key, $column] = $tables[$approval->document_type];
                    DB::table($table)->where($key, $approval->document_id)->update([$column => $targetStatus]);
                }
            }
            $this->audit($request, 'APPROVAL_ACTION', 'approvals', $approval->approval_id, null, ['status' => $decision]);
        });
        $this->notifyApprovalOwner($approval, $validated['action']);

        return response()->json($approval->fresh());
    }

    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'related_type' => 'required|in:APPLICATION,LETTER_REQUEST,PLACEMENT,WORK_PLAN,REPORT_OUTLINE,SUPERVISION_REQUEST,SUPERVISION_RESULT,OTHER',
            'related_id' => 'required|integer|min:1',
            'document_name' => 'required|string|max:255',
            'document_category' => 'nullable|string|max:100',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ]);
        abort_unless($validated['related_type'] === 'OTHER'
            || $this->access->canAccessRelated($request->user(), $validated['related_type'], $validated['related_id']), 403);

        $file = $request->file('file');
        $path = $file->store('documents/'.$request->user()->id, 'local');
        $document = DocumentRecord::create([
            'owner_user_id' => $request->user()->id,
            'related_type' => $validated['related_type'],
            'related_id' => $validated['related_id'],
            'document_name' => $validated['document_name'],
            'document_category' => $validated['document_category'] ?? null,
            'file_name' => basename($path),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
        $this->audit($request, 'DOCUMENT_UPLOADED', 'documents', $document->document_id, null, ['related_type' => $document->related_type]);

        return response()->json($document, 201);
    }

    public function downloadDocument(Request $request, DocumentRecord $document)
    {
        $canReview = $this->access->isGlobalOperator($request->user());
        $canAccessRelated = $document->related_type !== 'OTHER'
            && $this->access->canAccessRelated($request->user(), $document->related_type, $document->related_id);
        abort_unless((int) $document->owner_user_id === (int) $request->user()->id
            || $canReview || $canAccessRelated, 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        $this->audit($request, 'DOCUMENT_DOWNLOADED', 'documents', $document->document_id, null, null);

        return Storage::disk('local')->download($document->file_path, $document->file_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function notifyApprovalOwner(Approval $approval, $decision)
    {
        $recipients = collect();
        if ($approval->document_type === 'APPLICATION') {
            $application = CoopApplication::with('student.user')->find($approval->document_id);
            if ($application && $application->student && $application->student->user) {
                $recipients->push($application->student->user);
            }
        } elseif ($approval->document_type === 'LETTER_REQUEST') {
            $letterRequest = LetterRequest::find($approval->document_id);
            if ($letterRequest) {
                $recipients = $letterRequest->students()->with('user')->get()->pluck('user')->filter();
            }
        } elseif ($approval->document_type === 'PLACEMENT') {
            $placement = Placement::with('student.user')->find($approval->document_id);
            if ($placement && $placement->student && $placement->student->user) {
                $recipients->push($placement->student->user);
            }
        } elseif ($approval->document_type === 'WORK_PLAN') {
            $workPlan = WorkPlan::with('placement.student.user')->find($approval->document_id);
            if ($workPlan && $workPlan->placement && $workPlan->placement->student && $workPlan->placement->student->user) {
                $recipients->push($workPlan->placement->student->user);
            }
        } elseif ($approval->document_type === 'REPORT_OUTLINE') {
            $outline = ReportOutline::with('placement.student.user')->find($approval->document_id);
            if ($outline && $outline->placement && $outline->placement->student && $outline->placement->student->user) {
                $recipients->push($outline->placement->student->user);
            }
        } elseif ($approval->document_type === 'SUPERVISION_REQUEST') {
            $supervisionRequest = SupervisionRequest::with('requester')->find($approval->document_id);
            if ($supervisionRequest && $supervisionRequest->requester) {
                $recipients->push($supervisionRequest->requester);
            }
        }

        foreach ($recipients->unique('id') as $recipient) {
            $recipient->notify(new CwieStatusNotification(
                'มีผลการพิจารณาใหม่',
                'รายการ '.$approval->document_type.' มีผลการพิจารณา: '.$decision,
                $approval->document_type,
                $approval->document_id
            ));
        }
    }

    private function audit(Request $request, $action, $table, $recordId, $oldData, $newData)
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action_name' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => $request->ip(),
        ]);
    }
}
