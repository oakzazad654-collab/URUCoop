<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CooperativeEducationReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reports;

    public function __construct(CooperativeEducationReportService $reports)
    {
        $this->reports = $reports;
    }

    public function show(Request $request, $reportKey)
    {
        $report = $this->reports->getReport($request->user(), $reportKey);

        $format = $request->query('format', 'json');
        if ($format !== 'json') {
            return $this->reports->export($format, $report, $reportKey);
        }

        return response()->json($report);
    }
}
