<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\QualificationReview;
use App\Models\Student;
use App\Notifications\CwieStatusNotification;
use App\Services\DataAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CooperativeEducationController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function dashboard(Request $request)
    {
        $applications = $this->visibleApplications($request->user());

        return response()->json([
            'applications' => [
                'total' => (clone $applications)->count(),
                'pending' => (clone $applications)->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(),
                'approved' => (clone $applications)->where('application_status', 'APPROVED')->count(),
                'rejected' => (clone $applications)->where('application_status', 'REJECTED')->count(),
            ],
            'placements' => [
                'total' => DB::table('placements')->whereIn(
                    'student_id',
                    $this->visibleStudents($request->user())->select('student_id')
                )->count(),
            ],
        ]);
    }

    public function students(Request $request)
    {
        $students = $this->visibleStudents($request->user())
            ->with(['user:id,name,email,advisor_id', 'program:program_id,program_name,faculty_id'])
            ->orderBy('student_code')
            ->paginate(25);

        $students->getCollection()->transform(function (Student $student) {
            return [
                'student_id' => $student->student_id,
                'student_code' => $student->student_code,
                'name' => optional($student->user)->name,
                'email' => optional($student->user)->email,
                'program' => optional($student->program)->program_name,
                'student_status' => $student->student_status,
            ];
        });

        return response()->json($students);
    }

    public function showStudent(Request $request, Student $student)
    {
        abort_unless($this->visibleStudents($request->user())
            ->where('student_id', $student->student_id)->exists(), 404);
        $student->load(['user:id,name,email', 'program:program_id,program_name']);
        $data = [
            'student_id' => $student->student_id,
            'student_code' => $student->student_code,
            'name' => optional($student->user)->name,
            'program' => optional($student->program)->program_name,
            'student_status' => $student->student_status,
        ];

        if ($request->user()->role === 'student') {
            $data['profile'] = $student->only([
                'title_name', 'first_name_en', 'last_name_en', 'gender', 'birth_date',
                'nationality', 'religion', 'current_address', 'emergency_contact_name',
                'emergency_contact_relation', 'emergency_contact_phone', 'cumulative_gpa',
            ]);
            $data['profile']['citizen_id'] = $student->citizen_id
                ? str_repeat('*', max(0, strlen($student->citizen_id) - 4)).substr($student->citizen_id, -4)
                : null;
        }

        return response()->json($data);
    }

    public function updateStudent(Request $request, Student $student)
    {
        abort_unless($request->user()->role === 'student', 403);
        abort_unless((int) $student->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'citizen_id' => 'nullable|string|max:20',
            'title_name' => 'nullable|string|max:30',
            'first_name_en' => 'nullable|string|max:100',
            'last_name_en' => 'nullable|string|max:100',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER,UNSPECIFIED',
            'birth_date' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'current_address' => 'nullable|string|max:5000',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'cumulative_gpa' => 'nullable|numeric|between:0,4',
        ]);

        $before = $student->only(array_keys($validated));
        $student->fill($validated)->save();
        $this->audit($request, 'STUDENT_PROFILE_UPDATED', 'students', $student->student_id, $before, $student->only(array_keys($validated)));

        return response()->json(['student_id' => $student->student_id, 'updated' => true]);
    }

    public function companies(Request $request)
    {
        $query = Company::query()->where(function ($filter) {
            $filter->whereIn('status', ['ACTIVE', 'active'])
                ->orWhereNull('status');
        });

        if ($request->user()->role === 'company') {
            abort_unless($request->user()->company_id, 403);
            $query->whereKey($request->user()->company_id);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(function ($filter) use ($search) {
                $filter->where('company_name_th', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('province', 'like', $search);
            });
        }

        $companies = $query->orderBy('name')->paginate(25);
        $companies->getCollection()->transform(function (Company $company) {
            return [
                'company_id' => $company->id,
                'name' => $company->company_name_th ?: $company->name,
                'business_type' => $company->business_type,
                'province' => $company->province,
                'phone' => $company->phone,
                'website' => $company->website,
                'latitude' => $company->latitude,
                'longitude' => $company->longitude,
            ];
        });

        return response()->json($companies);
    }

    public function applications(Request $request)
    {
        $applications = $this->visibleApplications($request->user())
            ->with(['student.user:id,name', 'term:term_id,semester_no,academic_year', 'company:id,name,company_name_th'])
            ->latest('application_id')
            ->paginate(25);

        $applications->getCollection()->transform(function (CoopApplication $application) {
            return [
                'application_id' => $application->application_id,
                'application_no' => $application->application_no,
                'application_status' => $application->application_status,
                'qualification_result' => $application->qualification_result,
                'work_start_date' => $application->work_start_date,
                'work_end_date' => $application->work_end_date,
                'student' => [
                    'student_code' => optional($application->student)->student_code,
                    'name' => optional(optional($application->student)->user)->name,
                ],
                'term' => $application->term,
                'company' => [
                    'company_id' => optional($application->company)->id,
                    'name' => optional($application->company)->company_name_th ?: optional($application->company)->name,
                ],
            ];
        });

        return response()->json($applications);
    }

    public function createApplication(Request $request)
    {
        abort_unless($request->user()->role === 'student', 403);
        $student = $request->user()->studentProfile;
        abort_unless($student, 422, 'ยังไม่มีข้อมูลประวัตินักศึกษาในระบบ');

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,term_id',
            'preferred_company_id' => 'required|exists:companies,id',
            'desired_position' => 'nullable|string|max:200',
            'expected_job_description' => 'nullable|string|max:10000',
            'work_start_date' => 'required|date',
            'work_end_date' => 'required|date|after_or_equal:work_start_date',
            'student_note' => 'nullable|string|max:5000',
        ]);

        $duplicate = CoopApplication::where('student_id', $student->student_id)
            ->where('term_id', $validated['term_id'])
            ->exists();
        if ($duplicate) {
            return response()->json(['message' => 'มีใบสมัครในภาคการศึกษานี้แล้ว'], 422);
        }

        $term = AcademicTerm::findOrFail($validated['term_id']);
        abort_unless(
            AcademicTerm::openForRegistration()->where('term_id', $term->term_id)->exists(),
            422,
            'ภาคการศึกษานี้ไม่ได้เปิดรับสมัครหรืออยู่นอกช่วงรับสมัคร'
        );
        $application = CoopApplication::create(array_merge($validated, [
            'application_no' => 'APP-'.$term->academic_year.'-'.Str::upper(Str::random(8)),
            'student_id' => $student->student_id,
            'application_date' => now()->toDateString(),
            'application_status' => 'DRAFT',
            'qualification_result' => 'PENDING',
        ]));

        $this->audit($request, 'APPLICATION_CREATED', 'coop_applications', $application->application_id, null, $application->only(['application_no', 'application_status']));

        return response()->json($application, 201);
    }

    public function submitApplication(Request $request, CoopApplication $application)
    {
        abort_unless($request->user()->role === 'student'
            && (int) optional($request->user()->studentProfile)->student_id === (int) $application->student_id, 403);
        $lastReview = $application->reviews()->latest('review_id')->value('result');
        $isRevision = $application->application_status === 'UNDER_REVIEW' && $lastReview === 'REVISION_REQUIRED';
        abort_unless($application->application_status === 'DRAFT' || $isRevision, 422, 'สถานะใบสมัครไม่สามารถส่งได้');

        $application->application_status = 'SUBMITTED';
        $application->submitted_at = now();
        $application->save();
        $this->audit($request, 'APPLICATION_SUBMITTED', 'coop_applications', $application->application_id, null, ['application_status' => 'SUBMITTED']);
        $advisor = $request->user()->advisor;
        if ($advisor) {
            $advisor->notify(new CwieStatusNotification(
                'มีใบสมัครรอตรวจสอบ',
                'มีใบสมัครสหกิจศึกษารายการใหม่รอพิจารณา',
                'APPLICATION',
                $application->application_id
            ));
        }

        return response()->json($application->fresh(), 200);
    }

    public function reviewApplication(Request $request, CoopApplication $application)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['advisor', 'admin'], true), 403);
        abort_unless($this->access->canReviewApplication($user, $application), 403);
        abort_unless(in_array($application->application_status, ['SUBMITTED', 'UNDER_REVIEW'], true), 422, 'ใบสมัครนี้ยังไม่พร้อมให้ตรวจ');

        $validated = $request->validate([
            'academic_result' => 'required|boolean',
            'behavior_result' => 'required|boolean',
            'activity_result' => 'required|boolean',
            'health_result' => 'required|boolean',
            'result' => 'required|in:QUALIFIED,NOT_QUALIFIED,REVISION_REQUIRED',
            'comment' => 'nullable|string|max:5000',
        ]);

        DB::transaction(function () use ($request, $user, $application, $validated) {
            QualificationReview::create(array_merge($validated, [
                'application_id' => $application->application_id,
                'reviewer_id' => $user->id,
                'reviewed_at' => now(),
            ]));

            $application->qualification_result = $validated['result'] === 'QUALIFIED'
                ? 'QUALIFIED'
                : ($validated['result'] === 'NOT_QUALIFIED' ? 'NOT_QUALIFIED' : 'PENDING');
            $application->application_status = [
                'QUALIFIED' => 'APPROVED',
                'NOT_QUALIFIED' => 'REJECTED',
                'REVISION_REQUIRED' => 'UNDER_REVIEW',
            ][$validated['result']];
            $application->save();

            Approval::create([
                'document_type' => 'APPLICATION',
                'document_id' => $application->application_id,
                'approval_step' => 1,
                'approver_id' => $user->id,
                'approver_role' => $user->role,
                'approval_status' => $validated['result'] === 'QUALIFIED'
                    ? 'APPROVED'
                    : ($validated['result'] === 'NOT_QUALIFIED' ? 'REJECTED' : 'REVISION_REQUIRED'),
                'comment' => $validated['comment'] ?? null,
                'action_at' => now(),
            ]);

            $this->audit($request, 'APPLICATION_REVIEWED', 'coop_applications', $application->application_id, null, [
                'result' => $validated['result'],
                'reviewer_id' => $user->id,
            ]);
        });

        $studentUser = $application->student->user;
        if ($studentUser) {
            $studentUser->notify(new CwieStatusNotification(
                'ผลการพิจารณาใบสมัคร',
                'ผลการตรวจคุณสมบัติของใบสมัครสหกิจศึกษาถูกบันทึกแล้ว',
                'APPLICATION',
                $application->application_id
            ));
        }

        return response()->json($application->fresh());
    }

    private function visibleApplications($user)
    {
        abort_unless(in_array($user->role, ['student', 'advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
        return $this->access->applications($user);
    }

    private function visibleStudents($user)
    {
        abort_unless(in_array($user->role, ['student', 'advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
        return $this->access->students($user);
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
