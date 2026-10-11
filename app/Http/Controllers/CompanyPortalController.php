<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\CwieWorkflowController as CwieWorkflowApi;
use App\Models\Placement;
use App\Models\CompanyContact;
use Illuminate\Http\Request;

class CompanyPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $companyId = $request->user()->company_id;
        abort_unless($companyId, 403);

        $placements = Placement::with(['student.user', 'application.term'])
            ->where('company_id', $companyId)
            ->latest('placement_id')
            ->get();
        $mentors = CompanyContact::where('company_id', $companyId)
            ->where('contact_type', 'MENTOR')->where('status', 'ACTIVE')
            ->orderBy('first_name')->get();

        return view('company.dashboard', compact('placements', 'mentors'));
    }

    public function showPlacement(Request $request, $placement)
    {
        $companyId = $request->user()->company_id;
        abort_unless($companyId, 403);

        $placement = Placement::with([
            'student.user',
            'student.program.faculty',
            'application.term',
            'workPlan.items',
            'reportOutline',
        ])->where('company_id', $companyId)
            ->where('placement_id', $placement)
            ->firstOrFail();

        return view('company.placements.show', compact('placement'));
    }

    public function updateAcceptance(Request $request, Placement $placement, CwieWorkflowApi $api)
    {
        $response = $api->updatePlacementAcceptance($request, $placement);
        if ($response->getStatusCode() >= 400) {
            $payload = $response->getData(true);
            return back()->withErrors([
                'company_portal' => $payload['message'] ?? 'ดำเนินการไม่สำเร็จ กรุณาลองอีกครั้ง',
            ])->withInput();
        }

        return redirect()->route('company.dashboard')->with('success', 'บันทึกผลตอบรับนักศึกษาแล้ว');
    }
}
