<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\CoopApplication;
use App\Models\LetterRequest;
use App\Models\Placement;
use App\Models\ReportOutline;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SupervisionRequest;
use App\Models\SupervisionResult;
use App\Models\SupervisionSchedule;
use App\Models\User;
use App\Models\WorkPlan;

class DataAccessService
{
    public function facultyId(User $user)
    {
        $staff = $user->staffProfile;
        if ($staff && $staff->faculty_id) {
            return (int) $staff->faculty_id;
        }

        return $user->faculty_id ? (int) $user->faculty_id : null;
    }

    public function programId(User $user)
    {
        $staff = $user->staffProfile;
        return $staff && $staff->program_id ? (int) $staff->program_id : null;
    }

    public function companyId(User $user)
    {
        return in_array($user->role, ['company', 'mentor'], true) && $user->company_id
            ? (int) $user->company_id
            : null;
    }

    public function isGlobalOperator(User $user)
    {
        return in_array($user->role, ['admin', 'executive'], true)
            || ($user->role === 'officer' && !$this->facultyId($user));
    }

    public function students(User $user)
    {
        $query = Student::query();
        if ($user->role === 'student') {
            return $query->where('user_id', $user->id);
        }
        if ($user->role === 'company') {
            $companyId = $this->companyId($user);
            return $companyId
                ? $query->whereIn('student_id', Placement::where('company_id', $companyId)->select('student_id'))
                : $query->whereRaw('1 = 0');
        }
        if ($user->role === 'mentor') {
            $contactId = optional($user->companyContact)->contact_id;
            return $contactId
                ? $query->whereIn('student_id', Placement::where('mentor_contact_id', $contactId)->select('student_id'))
                : $query->whereRaw('1 = 0');
        }
        if ($this->isGlobalOperator($user)) {
            return $query;
        }
        if (in_array($user->role, ['advisor', 'supervisor', 'officer'], true)) {
            $facultyId = $this->facultyId($user);
            if (!$facultyId) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas('program', function ($programs) use ($facultyId) {
                $programs->where('faculty_id', $facultyId);
            });
            $programId = $this->programId($user);
            if ($programId) {
                $query->where('program_id', $programId);
            }

            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public function applications(User $user)
    {
        if ($user->role === 'mentor') {
            $contactId = optional($user->companyContact)->contact_id;
            return $contactId
                ? CoopApplication::whereIn('application_id', Placement::where('mentor_contact_id', $contactId)->select('application_id'))
                : CoopApplication::whereRaw('1 = 0');
        }
        if ($user->role === 'company') {
            $companyId = $this->companyId($user);
            return $companyId
                ? CoopApplication::whereIn('application_id', Placement::where('company_id', $companyId)->select('application_id'))
                : CoopApplication::whereRaw('1 = 0');
        }

        return CoopApplication::whereIn('student_id', $this->students($user)->select('student_id'));
    }

    public function placements(User $user)
    {
        if ($user->role === 'mentor') {
            $contactId = optional($user->companyContact)->contact_id;
            return $contactId
                ? Placement::where('mentor_contact_id', $contactId)
                : Placement::whereRaw('1 = 0');
        }
        if ($user->role === 'company') {
            $companyId = $this->companyId($user);
            return $companyId
                ? Placement::where('company_id', $companyId)
                : Placement::whereRaw('1 = 0');
        }

        return Placement::whereIn('student_id', $this->students($user)->select('student_id'));
    }

    public function letterRequests(User $user)
    {
        $query = LetterRequest::query();
        if ($user->role === 'student') {
            return $query->whereHas('students', function ($students) use ($user) {
                $students->where('students.user_id', $user->id);
            });
        }
        if ($this->isGlobalOperator($user)) {
            return $query;
        }
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true)) {
            return $query->whereRaw('1 = 0');
        }
        $studentIds = $this->students($user)->select('student_id');

        return $query->whereHas('students', function ($students) use ($studentIds) {
            $students->whereIn('students.student_id', $studentIds);
        })->whereDoesntHave('students', function ($students) use ($studentIds) {
            $students->whereNotIn('students.student_id', $studentIds);
        });
    }

    public function supervisionRequests(User $user)
    {
        $query = SupervisionRequest::query();
        if ($this->isGlobalOperator($user)) {
            return $query;
        }
        $facultyId = $this->facultyId($user);
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true) || !$facultyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('faculty_id', $facultyId);
    }

    public function supervisionSchedules(User $user)
    {
        return SupervisionSchedule::whereIn(
            'supervision_request_id',
            $this->supervisionRequests($user)->select('supervision_request_id')
        );
    }

    public function users(User $user)
    {
        $query = User::query()->where('role', 'student');
        if ($user->role === 'student') {
            return $query->whereKey($user->id);
        }
        if ($this->isGlobalOperator($user)) {
            return $query;
        }
        $facultyId = $this->facultyId($user);
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true) || !$facultyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($students) use ($facultyId) {
            $students->where('faculty_id', $facultyId)
                ->orWhere(function ($unassignedFaculty) use ($facultyId) {
                    $unassignedFaculty->whereNull('faculty_id')
                        ->whereHas('department', function ($departments) use ($facultyId) {
                            $departments->where('faculty_id', $facultyId);
                        });
                });
        });
    }

    public function canAccessStudent(User $user, Student $student)
    {
        return $this->students($user)->where('student_id', $student->student_id)->exists();
    }

    public function canReviewApplication(User $user, CoopApplication $application)
    {
        if ($this->isGlobalOperator($user)) {
            return true;
        }

        return $this->applications($user)->where('application_id', $application->application_id)->exists();
    }

    public function canAccessPlacement(User $user, Placement $placement)
    {
        if ($user->role === 'company') {
            return $this->companyId($user)
                && (int) $placement->company_id === $this->companyId($user);
        }
        if ($user->role === 'mentor') {
            return $this->placements($user)->where('placement_id', $placement->placement_id)->exists();
        }

        return $this->placements($user)->where('placement_id', $placement->placement_id)->exists();
    }

    public function canAccessLetterRequest(User $user, LetterRequest $letterRequest)
    {
        if ($this->isGlobalOperator($user)) {
            return true;
        }
        if ($user->role === 'student') {
            return $letterRequest->students()->where('students.user_id', $user->id)->exists();
        }
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true)) {
            return false;
        }
        $studentIds = $this->students($user)->select('student_id');

        return $letterRequest->students()->exists()
            && !$letterRequest->students()->whereNotIn('students.student_id', $studentIds)->exists();
    }

    public function canAccessSupervisionRequest(User $user, SupervisionRequest $request)
    {
        if ($this->isGlobalOperator($user)) {
            return true;
        }
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true)) {
            return false;
        }
        $facultyId = $this->facultyId($user);

        return $facultyId && (int) $request->faculty_id === $facultyId;
    }

    public function canAccessApproval(User $user, Approval $approval)
    {
        if ($this->isGlobalOperator($user)) {
            return true;
        }

        switch ($approval->document_type) {
            case 'APPLICATION':
                $application = CoopApplication::find($approval->document_id);
                return $application && $this->canReviewApplication($user, $application);
            case 'LETTER_REQUEST':
                $letterRequest = LetterRequest::find($approval->document_id);
                return $letterRequest && $this->canAccessLetterRequest($user, $letterRequest);
            case 'PLACEMENT':
                $placement = Placement::find($approval->document_id);
                return $placement && $this->canAccessPlacement($user, $placement);
            case 'WORK_PLAN':
                $workPlan = WorkPlan::find($approval->document_id);
                return $workPlan && $this->placements($user)->where('placement_id', $workPlan->placement_id)->exists();
            case 'REPORT_OUTLINE':
                $outline = ReportOutline::find($approval->document_id);
                return $outline && $this->placements($user)->where('placement_id', $outline->placement_id)->exists();
            case 'SUPERVISION_REQUEST':
                $request = SupervisionRequest::find($approval->document_id);
                return $request && $this->canAccessSupervisionRequest($user, $request);
            default:
                return false;
        }
    }

    public function canAccessRelated(User $user, $type, $id)
    {
        if ($type === 'OTHER') {
            return false;
        }
        if ($this->isGlobalOperator($user)) {
            return true;
        }

        switch ($type) {
            case 'APPLICATION':
                $application = CoopApplication::find($id);
                return $application && $this->applications($user)->where('application_id', $application->application_id)->exists();
            case 'LETTER_REQUEST':
                $request = LetterRequest::find($id);
                return $request && $this->canAccessLetterRequest($user, $request);
            case 'PLACEMENT':
                $placement = Placement::find($id);
                return $placement && $this->canAccessPlacement($user, $placement);
            case 'WORK_PLAN':
                $workPlan = WorkPlan::find($id);
                return $workPlan && $this->placements($user)->where('placement_id', $workPlan->placement_id)->exists();
            case 'REPORT_OUTLINE':
                $outline = ReportOutline::find($id);
                return $outline && $this->placements($user)->where('placement_id', $outline->placement_id)->exists();
            case 'SUPERVISION_REQUEST':
                $request = SupervisionRequest::find($id);
                return $request && $this->canAccessSupervisionRequest($user, $request);
            case 'SUPERVISION_RESULT':
                $result = SupervisionResult::with('schedule.request')->find($id);
                return $result && $result->schedule && $result->schedule->request
                    && $this->canAccessSupervisionRequest($user, $result->schedule->request);
            default:
                return false;
        }
    }

    public function canAccessLegacyStudent(User $user, User $student)
    {
        if ($this->isGlobalOperator($user)) {
            return true;
        }
        if ($user->role === 'student') {
            return (int) $user->id === (int) $student->id;
        }
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true)) {
            return false;
        }
        $facultyId = $this->facultyId($user);
        if (!$facultyId) {
            return false;
        }
        if ($student->faculty_id) {
            return (int) $student->faculty_id === $facultyId;
        }
        return $student->department && (int) $student->department->faculty_id === $facultyId;
    }

    public function legacyStudents(User $user)
    {
        $query = User::query()->where('role', 'student');
        if ($user->role === 'student') {
            return $query->whereKey($user->id);
        }
        if ($this->isGlobalOperator($user)) {
            return $query;
        }
        $facultyId = $this->facultyId($user);
        if (!in_array($user->role, ['advisor', 'supervisor', 'officer'], true) || !$facultyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($students) use ($facultyId) {
            $students->where('faculty_id', $facultyId)
                ->orWhere(function ($unassignedFaculty) use ($facultyId) {
                    $unassignedFaculty->whereNull('faculty_id')
                        ->whereHas('department', function ($departments) use ($facultyId) {
                            $departments->where('faculty_id', $facultyId);
                        });
                });
        });
    }
}