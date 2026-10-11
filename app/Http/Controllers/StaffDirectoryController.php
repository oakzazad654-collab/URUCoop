<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CoopApplication;
use App\Services\DataAccessService;
use Illuminate\Http\Request;

class StaffDirectoryController extends Controller
{
    public function companies(Request $request, DataAccessService $access)
    {
        $studentIds = $access->students($request->user())->select('student_id');
        $applicationCompanyIds = CoopApplication::whereIn('student_id', $studentIds)
            ->whereIn('application_status', ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED'])
            ->select('preferred_company_id');
        $companies = Company::whereIn('id', $applicationCompanyIds)
            ->orderBy('name')->paginate(20);

        return view('staff.companies', compact('companies'));
    }
}
