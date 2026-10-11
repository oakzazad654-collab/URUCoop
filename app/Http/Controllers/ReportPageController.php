<?php

namespace App\Http\Controllers;

use App\Services\CooperativeEducationReportService;
use Illuminate\Http\Request;

class ReportPageController extends Controller
{
    public function index(Request $request, CooperativeEducationReportService $reports)
    {
        return view('reports.index', [
            'reports' => $reports->availableReports($request->user()),
        ]);
    }

    public function show(Request $request, $reportKey, CooperativeEducationReportService $reports)
    {
        $availableReports = $reports->availableReports($request->user());
        abort_unless(array_key_exists($reportKey, $availableReports), 403);

        $report = $reports->getReport($request->user(), $reportKey);
        $format = $request->query('format');
        if ($format !== null) {
            return $reports->export($format, $report, $reportKey);
        }

        return view('reports.show', [
            'reportKey' => $reportKey,
            'report' => $report,
        ]);
    }
}
