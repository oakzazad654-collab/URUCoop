<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\CompanyContact;
use App\Models\Placement;
use App\Models\WorkPlan;
use Illuminate\Http\Request;

class MentorPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $contact = CompanyContact::where('user_id', $request->user()->id)
            ->where('status', 'ACTIVE')->firstOrFail();
        $placements = Placement::with([
            'student.user', 'student.program', 'application.term', 'workPlan.items', 'reportOutline',
        ])->where('mentor_contact_id', $contact->contact_id)
            ->where('company_id', $contact->company_id)
            ->latest('placement_id')->get();
        $approvals = Approval::with('approver')
            ->where('approver_id', $request->user()->id)
            ->where('document_type', 'WORK_PLAN')
            ->where('approval_status', 'PENDING')
            ->orderBy('approval_id')->get();
        $workPlans = WorkPlan::with(['placement.student.user', 'placement.company', 'items'])
            ->whereIn('work_plan_id', $approvals->pluck('document_id'))->get()->keyBy('work_plan_id');

        return view('mentor.dashboard', compact('contact', 'placements', 'approvals', 'workPlans'));
    }
}
