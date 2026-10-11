<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\CoopApplication;
use App\Models\CwieDocument;
use App\Models\Placement;
use App\Models\Student;
use App\Models\SupervisionRequest;
use App\Services\DataAccessService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function student(Request $request)
    {
        $studentId = optional($request->user()->studentProfile)->student_id;
        $applications = $this->access->applications($request->user());
        $placements = $this->access->placements($request->user());

        return view('student.dashboard', [
            'stats' => [
                ['label' => 'ใบสมัครรอตรวจ', 'value' => (clone $applications)->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'ใบสมัครอนุมัติ', 'value' => (clone $applications)->where('application_status', 'APPROVED')->count(), 'icon' => 'badge-check'],
                ['label' => 'สถานที่ปฏิบัติงาน', 'value' => $placements->count(), 'icon' => 'building-2'],
                ['label' => 'แบบฟอร์ม CWIE เดิม', 'value' => CwieDocument::where('user_id', $request->user()->id)->count(), 'icon' => 'files'],
            ],
        ]);
    }

    public function staff(Request $request)
    {
        $user = $request->user();
        $applications = $this->access->applications($user);
        $students = $this->access->students($user);
        $legacyDocuments = CwieDocument::where('status', 'pending_'.($user->role === 'advisor' ? 'advisor' : 'officer'))
            ->whereIn('user_id', $this->access->legacyStudents($user)->select('id'));

        return view('staff.dashboard', [
            'stats' => [
                ['label' => 'ใบสมัครรอตรวจ', 'value' => (clone $applications)->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'นักศึกษาในขอบเขต', 'value' => $students->count(), 'icon' => 'users-round'],
                ['label' => 'การอนุมัติของฉัน', 'value' => Approval::where('approver_id', $user->id)->where('approval_status', 'PENDING')->count(), 'icon' => 'list-checks'],
                ['label' => 'เอกสาร CWIE เดิมรอตรวจ', 'value' => $legacyDocuments->count(), 'icon' => 'files'],
            ],
        ]);
    }

    public function admin()
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'นักศึกษา', 'value' => Student::count(), 'icon' => 'users-round'],
                ['label' => 'บัญชีรออนุมัติ', 'value' => \App\Models\User::where('status', 'PENDING')->count(), 'icon' => 'user-round-check'],
                ['label' => 'ใบสมัครรอตรวจ', 'value' => CoopApplication::whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'สถานที่ปฏิบัติงาน', 'value' => Placement::count(), 'icon' => 'building-2'],
                ['label' => 'การอนุมัติรอดำเนินการ', 'value' => Approval::where('approval_status', 'PENDING')->count(), 'icon' => 'list-checks'],
            ],
        ]);
    }

    public function supervisor(Request $request)
    {
        $user = $request->user();
        $applications = $this->access->applications($user);
        $students = $this->access->students($user);
        $placements = $this->access->placements($user);
        $supervisionRequests = $this->access->supervisionRequests($user);

        return view('supervisor.dashboard', [
            'stats' => [
                ['label' => 'นักศึกษาในคณะที่รับผิดชอบ', 'value' => $students->count(), 'icon' => 'users-round'],
                ['label' => 'ใบสมัครรอตรวจ', 'value' => (clone $applications)->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'สถานที่ปฏิบัติงาน', 'value' => $placements->count(), 'icon' => 'building-2'],
                ['label' => 'คำขอนิเทศในคณะ', 'value' => $supervisionRequests->count(), 'icon' => 'map-pinned'],
            ],
        ]);
    }

    public function executive(Request $request)
    {
        $user = $request->user();
        $applications = $this->access->applications($user);
        $students = $this->access->students($user);
        $supervisionRequests = $this->access->supervisionRequests($user);

        return view('executive.dashboard', [
            'stats' => [
                ['label' => 'นักศึกษาทั้งระบบ', 'value' => $students->count(), 'icon' => 'users-round'],
                ['label' => 'ใบสมัครรอตรวจ', 'value' => (clone $applications)->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'คำขอนิเทศทั้งระบบ', 'value' => $supervisionRequests->count(), 'icon' => 'map-pinned'],
                ['label' => 'อนุมัติที่มอบหมาย', 'value' => Approval::where('approver_id', $user->id)->where('approval_status', 'PENDING')->count(), 'icon' => 'list-checks'],
            ],
        ]);
    }
}