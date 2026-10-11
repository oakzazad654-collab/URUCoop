<?php

namespace App\Http\Controllers;

use App\Models\InternshipApplication;
use App\Models\Faculty;
use App\Services\DataAccessService;
use Illuminate\Http\Request;

class InternshipApplicationController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    private function scopedApplications(Request $request)
    {
        $query = InternshipApplication::query();
        $user = $request->user();

        if ($this->access->isGlobalOperator($user)) {
            return $query;
        }

        if ($user->role !== 'officer') {
            return $query->whereRaw('1 = 0');
        }

        $facultyId = $this->access->facultyId($user);
        $faculty = $facultyId ? Faculty::find($facultyId) : null;
        $facultyNames = $faculty
            ? collect([$faculty->faculty_name, $faculty->name])->filter()->unique()->values()->all()
            : [];

        return $facultyNames
            ? $query->whereIn('faculty', $facultyNames)
            : $query->whereRaw('1 = 0');
    }

    private function facultyValueFor(Request $request, $submittedValue)
    {
        $user = $request->user();
        if ($this->access->isGlobalOperator($user)) {
            return $submittedValue;
        }

        $facultyId = $this->access->facultyId($user);
        $faculty = $facultyId ? Faculty::find($facultyId) : null;
        abort_unless($faculty, 403);

        return $faculty->faculty_name ?: $faculty->name;
    }

    private function parseListValue($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), function ($item) {
                return $item !== '';
            }));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(';', $value)), function ($item) {
                return $item !== '';
            }));
        }

        return [];
    }

    private function parseLanguageSkills($value): array
    {
        $items = $this->parseListValue($value);
        $result = [];

        foreach ($items as $item) {
            $parts = explode(':', $item, 2);
            $result[] = [
                'language' => trim($parts[0] ?? ''),
                'level' => isset($parts[1]) ? trim($parts[1]) : '',
            ];
        }

        return array_values(array_filter($result, function ($item) {
            return $item['language'] !== '';
        }));
    }

    public function index(Request $request)
    {
        $applications = $this->scopedApplications($request)->latest()->get();

        return view('internship_applications.index', compact('applications'));
    }

    public function create()
    {
        return view('internship_applications.form', ['application' => new InternshipApplication()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'work_duration_months' => 'nullable|integer|min:1',
            'student_title_th' => 'nullable|string|max:20',
            'student_first_name_th' => 'required|string|max:100',
            'student_last_name_th' => 'required|string|max:100',
            'student_first_name_en' => 'nullable|string|max:100',
            'student_last_name_en' => 'nullable|string|max:100',
            'student_id' => 'nullable|string|max:50',
            'faculty' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:20',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'gpax' => 'nullable|numeric|min:0|max:4',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'home_address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'height' => 'nullable|string|max:20',
            'weight' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:50',
            'education_history' => 'nullable|array',
            'language_skills' => 'nullable|array',
        ]);

        $data['education_history'] = $this->parseListValue($request->input('education_history', []));
        $data['language_skills'] = $this->parseLanguageSkills($request->input('language_skills', []));
        $data['faculty'] = $this->facultyValueFor($request, $data['faculty'] ?? null);

        $application = InternshipApplication::create($data);

        return redirect()->route('internship-applications.show', $application)
            ->with('success', 'บันทึกข้อมูลใบสมัครสำเร็จแล้ว');
    }

    public function show(Request $request, InternshipApplication $application)
    {
        $application = $this->scopedApplications($request)->whereKey($application->getKey())->firstOrFail();
        return view('internship_applications.show', compact('application'));
    }

    public function edit(Request $request, InternshipApplication $application)
    {
        $application = $this->scopedApplications($request)->whereKey($application->getKey())->firstOrFail();
        return view('internship_applications.form', compact('application'));
    }

    public function update(Request $request, InternshipApplication $application)
    {
        $application = $this->scopedApplications($request)->whereKey($application->getKey())->firstOrFail();
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'work_duration_months' => 'nullable|integer|min:1',
            'student_title_th' => 'nullable|string|max:20',
            'student_first_name_th' => 'required|string|max:100',
            'student_last_name_th' => 'required|string|max:100',
            'student_first_name_en' => 'nullable|string|max:100',
            'student_last_name_en' => 'nullable|string|max:100',
            'student_id' => 'nullable|string|max:50',
            'faculty' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:20',
            'gpa' => 'nullable|numeric|min:0|max:4',
            'gpax' => 'nullable|numeric|min:0|max:4',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'home_address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'height' => 'nullable|string|max:20',
            'weight' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:50',
            'education_history' => 'nullable|array',
            'language_skills' => 'nullable|array',
        ]);

        $data['education_history'] = $this->parseListValue($request->input('education_history', []));
        $data['language_skills'] = $this->parseLanguageSkills($request->input('language_skills', []));
        $data['faculty'] = $this->facultyValueFor($request, $data['faculty'] ?? null);

        $application->update($data);

        return redirect()->route('internship-applications.show', $application)
            ->with('success', 'อัปเดตข้อมูลใบสมัครสำเร็จแล้ว');
    }

    public function destroy(Request $request, InternshipApplication $application)
    {
        $application = $this->scopedApplications($request)->whereKey($application->getKey())->firstOrFail();
        $application->delete();

        return redirect()->route('internship-applications.index')
            ->with('success', 'ลบข้อมูลใบสมัครสำเร็จแล้ว');
    }

    public function pdf(Request $request, InternshipApplication $internshipApplication)
    {
        $internshipApplication = $this->scopedApplications($request)->whereKey($internshipApplication->getKey())->firstOrFail();
        $html = view('internship_applications.pdf', ['application' => $internshipApplication])->render();

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => sys_get_temp_dir(),
            'autoVietnamese' => true,
            'autoArabic' => true,
        ];

        if (file_exists(public_path('fonts/THSarabunNew.ttf'))) {
            $fontDir = public_path('fonts');
            $config['fontDir'] = [$fontDir];
            $config['default_font'] = 'thsarabunnew';
            $config['fontdata'] = [
                'thsarabunnew' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew Bold.ttf',
                    'I' => 'THSarabunNew Italic.ttf',
                    'BI' => 'THSarabunNew BoldItalic.ttf',
                ],
            ];
        }

        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);

        return $mpdf->Output(($internshipApplication->student_id ?: 'internship-app') . '-sg03.pdf', 'D');
    }
}
