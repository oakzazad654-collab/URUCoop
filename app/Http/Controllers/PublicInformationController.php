<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Support\Facades\File;

class PublicInformationController extends Controller
{
    public function about()
    {
        $responsibleStaff = User::with(['staffProfile', 'faculty', 'department'])
            ->whereIn('role', ['advisor', 'officer', 'supervisor'])
            ->where('status', 'ACTIVE')
            ->orderBy('role')->orderBy('name')->get();

        return view('landing.about', compact('responsibleStaff'));
    }

    public function forms()
    {
        $documentDirectory = base_path('docs'.DIRECTORY_SEPARATOR.'คู่มือต่างๆ');
        $documentFiles = collect(File::files($documentDirectory))
            ->filter(function ($file) {
                return strtolower($file->getExtension()) === 'pdf';
            })
            ->sort(function ($left, $right) {
                return strnatcasecmp($left->getFilename(), $right->getFilename());
            })
            ->map(function ($file) {
                $filename = $file->getFilename();
                preg_match('/\Aสก(\d{2})\.pdf\z/u', $filename, $matches);

                return [
                    'name' => isset($matches[1]) ? 'สก. '.$matches[1] : ($filename === 'สก.pdf' ? 'สก. 01' : ($filename === 'SK-12.1.pdf' ? 'สก. 12.1' : pathinfo($filename, PATHINFO_FILENAME))),
                    'file' => $filename,
                    'url' => route('public.forms.download', ['file' => $filename]),
                ];
            })->values();
        if (!$documentFiles->contains('file', 'SK-12.1.pdf') && File::isFile(public_path('forms'.DIRECTORY_SEPARATOR.'SK-12.1.pdf'))) {
            $documentFiles->push([
                'name' => 'สก. 12.1',
                'file' => 'SK-12.1.pdf',
                'url' => route('public.forms.download', ['file' => 'SK-12.1.pdf']),
            ]);
        }

        $forms = [
            ['title' => 'แบบฟอร์ม CWIE 01–06', 'description' => 'แบบฟอร์มหลักสำหรับการสมัครและดำเนินงานสหกิจศึกษา', 'files' => [
                ['name' => 'CWIE 01', 'file' => 'CWIE-01.pdf'], ['name' => 'CWIE 02', 'file' => 'CWIE-02.pdf'],
                ['name' => 'CWIE 03', 'file' => 'CWIE-03.pdf'], ['name' => 'CWIE 04', 'file' => 'CWIE-04.pdf'],
                ['name' => 'CWIE 05', 'file' => 'CWIE-05.pdf'], ['name' => 'CWIE 06', 'file' => 'CWIE-06.pdf'],
            ]],
            ['title' => 'แบบฟอร์ม สก.', 'description' => 'แบบฟอร์ม PDF ต้นฉบับจากโฟลเดอร์ docs/คู่มือต่างๆ', 'files' => $documentFiles],
            ['title' => 'คู่มือและเอกสารประกอบ', 'description' => 'คู่มือนักศึกษา คู่มือสถานประกอบการ และเกณฑ์ประเมิน', 'files' => [
                ['name' => 'คู่มือสำหรับนักศึกษา', 'file' => 'student-forms-guide.doc'],
                ['name' => 'คู่มือสำหรับอาจารย์', 'file' => 'advisor-forms.doc'],
                ['name' => 'คู่มือสำหรับสถานประกอบการ', 'file' => 'employer-forms.doc'],
                ['name' => 'เกณฑ์การประเมิน', 'file' => 'assessment-criteria.docx'],
                ['name' => 'แบบประเมิน/แบบบันทึกคะแนน', 'file' => 'assessment-score-sheet.docx'],
                ['name' => 'คู่มือสหกิจศึกษา (บทที่ 1–6)', 'file' => 'handbook-chapters-1-6.docx'],
                ['name' => 'ภาคผนวกคู่มือสหกิจศึกษา', 'file' => 'handbook-appendix-a.doc'],
            ]],
        ];

        return view('landing.forms', compact('forms'));
    }

    public function downloadForm($file)
    {
        abort_unless(is_string($file) && basename($file) === $file, 404);

        $documentDirectory = base_path('docs'.DIRECTORY_SEPARATOR.'คู่มือต่างๆ');
        foreach (File::files($documentDirectory) as $document) {
            if ($document->getFilename() === $file && strtolower($document->getExtension()) === 'pdf') {
                if (request()->boolean('inline')) {
                    return response()->file($document->getPathname());
                }

                return response()->download($document->getPathname(), $document->getFilename());
            }
        }

        $additionalSkForm = public_path('forms'.DIRECTORY_SEPARATOR.'SK-12.1.pdf');
        if ($file === 'SK-12.1.pdf' && File::isFile($additionalSkForm)) {
            if (request()->boolean('inline')) {
                return response()->file($additionalSkForm);
            }

            return response()->download($additionalSkForm, $file);
        }

        abort(404);
    }

    public function selectedStudents()
    {
        $placements = Placement::with([
            'student.user', 'student.program.faculty', 'company', 'application.term',
        ])->where('acceptance_status', 'ACCEPTED')
            ->whereHas('application', function ($applications) {
                $applications->where('application_status', 'APPROVED');
            })
            ->latest('placement_id')->paginate(20);

        return view('landing.selected-students', compact('placements'));
    }

    public function activities()
    {
        $announcements = $this->publishedAnnouncements()
            ->whereNotNull('image_path')->latest('published_at')->paginate(12);
        $videoId = \App\Models\WebsiteSetting::where('key', 'homepage_youtube_video_id')->value('value');
        if (!is_string($videoId) || !preg_match('/\A[A-Za-z0-9_-]{11}\z/', $videoId)) {
            $videoId = null;
        }

        return view('landing.activities', compact('announcements', 'videoId'));
    }

    public function board()
    {
        $announcements = $this->publishedAnnouncements()->latest('published_at')->paginate(15);

        return view('landing.board', compact('announcements'));
    }

    private function publishedAnnouncements()
    {
        return Announcement::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
