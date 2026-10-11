<?php

namespace App\Services;

use App\Models\DocumentRecord;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class CooperativeEducationReportService
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function availableReports(User $user)
    {
        $reports = [
            'application-status' => [
                'title' => 'สถานะใบสมัคร CWIE 01',
                'description' => 'สรุปจำนวนใบสมัครแยกตามสถานะ',
            ],
            'placements-by-company' => [
                'title' => 'จำนวนนักศึกษาตามสถานประกอบการ',
                'description' => 'ดูจำนวนการจัดวางนักศึกษาแยกตามสถานประกอบการ',
            ],
            'supervision-schedule' => [
                'title' => 'กำหนดการนิเทศ',
                'description' => 'ติดตามกำหนดการนิเทศในขอบเขตข้อมูลของคุณ',
            ],
            'pending-documents' => [
                'title' => 'เอกสารที่ต้องตรวจสอบ',
                'description' => 'ตรวจดูเอกสารที่รอตรวจหรือมีสถานะไม่ถูกต้อง',
            ],
        ];

        if ($user->role === 'student') {
            return array_intersect_key($reports, array_flip(['application-status', 'pending-documents']));
        }

        if (in_array($user->role, ['advisor', 'supervisor', 'officer', 'admin', 'executive'], true)) {
            return $reports;
        }

        return [];
    }

    public function getReport(User $user, $reportKey)
    {
        switch ($reportKey) {
            case 'application-status':
                abort_unless(in_array($user->role, ['student', 'advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
                $rows = $this->access->applications($user)
                    ->selectRaw('application_status AS status, COUNT(*) AS total')
                    ->groupBy('application_status')
                    ->orderBy('application_status')
                    ->get()
                    ->toArray();
                $title = 'สถานะใบสมัคร CWIE 01';
                break;

            case 'placements-by-company':
                $rows = $this->access->placements($user)->with('company')->get()
                    ->groupBy('company_id')->map(function ($placements) {
                        $company = $placements->first()->company;
                        return [
                            'company' => $company ? ($company->company_name_th ?: $company->name) : '',
                            'province' => $company ? $company->province : null,
                            'total' => $placements->count(),
                        ];
                    })->values()->toArray();
                $title = 'จำนวนนักศึกษาตามสถานประกอบการ';
                break;

            case 'supervision-schedule':
                abort_unless(in_array($user->role, ['advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
                $rows = $this->access->supervisionSchedules($user)
                    ->with(['request', 'company'])->orderBy('supervision_date')->orderBy('start_time')
                    ->get()->map(function ($schedule) {
                        return [
                            'date' => optional($schedule->supervision_date)->toDateString(),
                            'time' => $schedule->start_time,
                            'company' => $schedule->company ? ($schedule->company->company_name_th ?: $schedule->company->name) : '',
                            'province' => $schedule->province,
                            'status' => $schedule->schedule_status,
                        ];
                    })->toArray();
                $title = 'กำหนดการนิเทศ';
                break;

            case 'pending-documents':
                abort_unless(in_array($user->role, ['student', 'advisor', 'supervisor', 'officer', 'admin', 'executive'], true), 403);
                $rows = DocumentRecord::whereIn('verification_status', ['PENDING', 'INVALID'])
                    ->whereIn('owner_user_id', $this->access->users($user)->select('id'))
                    ->get(['document_name', 'document_category', 'verification_status', 'uploaded_at'])
                    ->map(function ($document) {
                        return [
                            'document' => $document->document_name,
                            'category' => $document->document_category,
                            'status' => $document->verification_status,
                            'uploaded_at' => $document->uploaded_at,
                        ];
                    })->toArray();
                $title = 'เอกสารที่ต้องตรวจสอบ';
                break;

            default:
                abort(404, 'ไม่พบรายงานที่ร้องขอ');
        }

        return ['title' => $title, 'data' => $rows];
    }

    public function export($format, array $report, $reportKey)
    {
        if ($format === 'csv') {
            return $this->csv($report['data'], $reportKey.'.csv');
        }
        if ($format === 'pdf') {
            return $this->pdf($report['title'], $report['data'], $reportKey.'.pdf');
        }

        abort(422, 'รูปแบบรายงานต้องเป็น json, csv หรือ pdf');
    }

    private function csv(array $rows, $filename)
    {
        return Response::streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            if ($rows) {
                fputcsv($output, array_keys((array) $rows[0]));
                foreach ($rows as $row) {
                    fputcsv($output, array_values((array) $row));
                }
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function pdf($title, array $rows, $filename)
    {
        $html = '<h2>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h2><table border="1" cellpadding="5" cellspacing="0" width="100%"><thead><tr>';
        foreach (array_keys((array) ($rows[0] ?? [])) as $heading) {
            $html .= '<th>'.htmlspecialchars((string) $heading, ENT_QUOTES, 'UTF-8').'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ((array) $row as $value) {
                $html .= '<td>'.htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8').'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $pdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'tempDir' => sys_get_temp_dir()]);
        $pdf->WriteHTML($html);
        $content = $pdf->Output('', 'S');

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
