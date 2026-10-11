<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\CooperativeEducationController as CooperativeEducationApi;
use App\Http\Controllers\Api\CwieWorkflowController as CwieWorkflowApi;
use App\Models\AcademicTerm;
use App\Models\Approval;
use App\Models\Company;
use App\Models\CoopApplication;
use App\Models\Faculty;
use App\Models\LetterRequest;
use App\Models\Placement;
use App\Models\ReportOutline;
use App\Models\Staff;
use App\Models\SupervisionSchedule;
use App\Models\SupervisionRequest;
use App\Models\WorkPlan;
use App\Services\DataAccessService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class WorkflowWebController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $applications = $this->access->applications($user)
            ->with(['student.user', 'term', 'company', 'reviews'])
            ->latest('application_id')
            ->get();
        $placements = $this->access->placements($user)
            ->with(['student.user', 'company', 'application', 'workPlan.items', 'reportOutline'])
            ->latest('placement_id')
            ->get();
        $placementIds = $placements->pluck('placement_id');
        $letterRequests = $this->access->letterRequests($user)
            ->with(['company', 'students.user'])
            ->latest('request_id')
            ->get();
        $workPlans = WorkPlan::with(['placement.company', 'items'])
            ->whereIn('placement_id', $placementIds)
            ->latest('work_plan_id')
            ->get();
        $reportOutlines = ReportOutline::with('placement.company')
            ->whereIn('placement_id', $placementIds)
            ->latest('outline_id')
            ->get();
        $supervisionRequests = $this->access->supervisionRequests($user)
            ->with(['schedules.company', 'schedules.placement', 'requester'])
            ->latest('supervision_request_id')
            ->get();
        $supervisionSchedules = $this->access->supervisionSchedules($user)
            ->with(['company', 'placement', 'request'])
            ->whereDoesntHave('result')
            ->orderBy('supervision_date')
            ->get();

        $approvalQuery = Approval::where('approval_status', 'PENDING')->latest('approval_id');
        if ($user->role !== 'admin') {
            $approvalQuery->where('approver_id', $user->id);
        }
        $approvals = $approvalQuery->get()->filter(function (Approval $approval) use ($user) {
            return $this->access->canAccessApproval($user, $approval);
        })->values();

        $terms = AcademicTerm::openForRegistration()
            ->orderByDesc('academic_year')->orderByDesc('semester_no')->get();
        $companies = Company::where(function ($query) {
            $query->whereIn('status', ['ACTIVE', 'active'])->orWhereNull('status');
        })->orderBy('name')->get();
        $facultyId = $this->access->facultyId($user);
        $staffQuery = Staff::with('user')->orderBy('staff_code');
        if ($facultyId) {
            $staffQuery->where('faculty_id', $facultyId);
        } elseif ($user->role !== 'admin') {
            $staffQuery->whereRaw('1 = 0');
        }
        $staffMembers = $staffQuery->get();
        $faculties = $user->role === 'admin' ? Faculty::orderBy('name')->get() : collect();

        return view('workflows.index', compact(
            'applications', 'placements', 'letterRequests', 'workPlans', 'reportOutlines',
            'supervisionRequests', 'supervisionSchedules', 'approvals', 'terms', 'companies',
            'staffMembers', 'faculties', 'facultyId'
        ));
    }

    public function storeApplication(Request $request, CooperativeEducationApi $api)
    {
        return $this->forward($api->createApplication($request), 'สร้างใบสมัครสหกิจแล้ว');
    }

    public function submitApplication(Request $request, CoopApplication $application, CooperativeEducationApi $api)
    {
        return $this->forward($api->submitApplication($request, $application), 'ส่งใบสมัครให้พิจารณาแล้ว');
    }

    public function reviewApplication(Request $request, CoopApplication $application, CooperativeEducationApi $api)
    {
        return $this->forward($api->reviewApplication($request, $application), 'บันทึกผลตรวจคุณสมบัติแล้ว');
    }

    public function storeLetterRequest(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->createLetterRequest($request), 'ส่งคำขอหนังสือแล้ว');
    }

    public function checkLetterDocuments(Request $request, LetterRequest $letterRequest, CwieWorkflowApi $api)
    {
        return $this->forward($api->checkLetterDocuments($request, $letterRequest), 'บันทึกผลตรวจเอกสารแล้ว');
    }

    public function storePlacement(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->createPlacement($request), 'บันทึกข้อมูลสถานที่ปฏิบัติงานแล้ว');
    }

    public function updatePlacementAcceptance(Request $request, Placement $placement, CwieWorkflowApi $api)
    {
        return $this->forward($api->updatePlacementAcceptance($request, $placement), 'บันทึกผลตอบรับสถานประกอบการแล้ว');
    }

    public function storeWorkPlan(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->createWorkPlan($request), 'สร้างแผนปฏิบัติงานแล้ว');
    }

    public function submitWorkPlan(Request $request, WorkPlan $workPlan, CwieWorkflowApi $api)
    {
        return $this->forward($api->submitWorkPlan($request, $workPlan), 'ส่งแผนปฏิบัติงานให้พิจารณาแล้ว');
    }

    public function storeReportOutline(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->createReportOutline($request), 'สร้างโครงร่างรายงานแล้ว');
    }

    public function submitReportOutline(Request $request, ReportOutline $outline, CwieWorkflowApi $api)
    {
        return $this->forward($api->submitReportOutline($request, $outline), 'ส่งโครงร่างรายงานให้พิจารณาแล้ว');
    }

    public function storeSupervisionRequest(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->createSupervisionRequest($request), 'ส่งคำขอนิเทศงานแล้ว');
    }

    public function storeSupervisionResult(Request $request, SupervisionSchedule $schedule, CwieWorkflowApi $api)
    {
        return $this->forward($api->createSupervisionResult($request, $schedule), 'บันทึกผลการนิเทศแล้ว');
    }

    public function actOnApproval(Request $request, Approval $approval, CwieWorkflowApi $api)
    {
        return $this->forward($api->actOnApproval($request, $approval), 'บันทึกผลการพิจารณาแล้ว');
    }

    public function uploadDocument(Request $request, CwieWorkflowApi $api)
    {
        return $this->forward($api->uploadDocument($request), 'อัปโหลดเอกสารแล้ว');
    }

    private function forward($response, $successMessage)
    {
        if ($response->getStatusCode() >= 400) {
            $payload = $response->getData(true);
            return back()->withErrors([
                'workflow' => $payload['message'] ?? 'ดำเนินการไม่สำเร็จ กรุณาตรวจสอบข้อมูลแล้วลองอีกครั้ง',
            ])->withInput();
        }

        return redirect()->route('workflow.index')->with('success', $successMessage);
    }
}