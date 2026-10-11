<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CwieDocument;
use App\Services\DataAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MapController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    public function index()
    {
        return view('staff.map');
    }

    public function locations(Request $request)
    {
        $studentUserIds = $this->access->legacyStudents($request->user())->select('id');
        $studentDocuments = CwieDocument::with('owner.advisor')
            ->where('type', 'cwie-01')
            ->where('status', 'approved')
            ->whereIn('user_id', $studentUserIds)
            ->latest()
            ->get();

        $studentsByCompany = [];
        foreach ($studentDocuments as $document) {
            $companyName = $this->normalizeName(data_get($document->data, 'company_name'));
            if ($companyName === '') continue;

            $identity = $document->student_id ?: 'user:'.$document->user_id;
            $studentsByCompany[$companyName][$identity] = [
                'name' => data_get($document->data, 'student_name') ?: optional($document->owner)->name ?: 'ไม่ระบุชื่อ',
                'student_id' => $document->student_id ?: data_get($document->data, 'student_id') ?: optional($document->owner)->student_number,
                'major' => data_get($document->data, 'major') ?: data_get($document->data, 'student_major'),
                'advisor' => optional(optional($document->owner)->advisor)->name,
                'status' => $document->status,
            ];
        }

        $placements = $this->access->placements($request->user())
            ->with(['student.user', 'student.program', 'company'])
            ->where('acceptance_status', 'ACCEPTED')->get();
        foreach ($placements as $placement) {
            if (!$placement->company || !$placement->student) {
                continue;
            }
            $identity = 'student:'.$placement->student_id;
            $studentsByCompany['company:'.$placement->company_id][$identity] = [
                'name' => optional($placement->student->user)->name ?: $placement->student->student_code,
                'student_id' => $placement->student->student_code,
                'major' => optional($placement->student->program)->program_name,
                'advisor' => optional(optional($placement->student->user)->advisor)->name,
                'status' => $placement->placement_status,
            ];
        }

        $locations = Company::query()
            ->whereIn('status', ['ACTIVE', 'active'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get()
            ->map(function (Company $company) use ($studentsByCompany) {
                $students = array_values(array_merge(
                    $studentsByCompany[$this->normalizeName($company->name)] ?? [],
                    $studentsByCompany['company:'.$company->id] ?? []
                ));
                $address = $company->address ?: collect([
                    $company->subdistrict,
                    $company->district,
                    $company->province,
                    $company->zipcode,
                ])->filter()->implode(', ');

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'address' => $address,
                    'latitude' => (float) $company->latitude,
                    'longitude' => (float) $company->longitude,
                    'status' => $company->status,
                    'province' => $company->province,
                    'google_maps_url' => $company->google_maps_url,
                    'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($company->latitude.','.$company->longitude),
                    'student_count' => count($students),
                    'students' => $students,
                ];
            })
            ->values();

        return response()->json($locations);
    }

    private function normalizeName($name)
    {
        return Str::lower(preg_replace('/\s+/u', ' ', trim((string) $name)));
    }
}
