<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicTermController extends Controller
{
    public function index()
    {
        $terms = AcademicTerm::orderByDesc('academic_year')
            ->orderByDesc('semester_no')
            ->paginate(25);

        return view('admin.academic_terms.index', compact('terms'));
    }

    public function create()
    {
        return view('admin.academic_terms.form');
    }

    public function store(Request $request)
    {
        AcademicTerm::create($this->validatedTerm($request));

        return redirect()->route('admin.academic-terms.index')->with('success', 'เพิ่มภาคการศึกษาเรียบร้อยแล้ว');
    }

    public function edit(AcademicTerm $academicTerm)
    {
        return view('admin.academic_terms.form', ['term' => $academicTerm]);
    }

    public function update(Request $request, AcademicTerm $academicTerm)
    {
        $academicTerm->update($this->validatedTerm($request, $academicTerm->term_id));

        return redirect()->route('admin.academic-terms.index')->with('success', 'ปรับปรุงภาคการศึกษาเรียบร้อยแล้ว');
    }

    private function validatedTerm(Request $request, $termId = null)
    {
        return $request->validate([
            'semester_no' => [
                'required',
                'integer',
                'between:1,3',
                Rule::unique('academic_terms', 'semester_no')
                    ->where(function ($query) use ($request) {
                        return $query->where('academic_year', $request->input('academic_year'));
                    })
                    ->ignore($termId, 'term_id'),
            ],
            'academic_year' => 'required|integer|between:1900,2200',
            'start_date' => 'nullable|required_with:end_date|date',
            'end_date' => 'nullable|required_with:start_date|date|after_or_equal:start_date',
            'registration_start_date' => 'nullable|required_with:registration_end_date|date',
            'registration_end_date' => 'nullable|required_with:registration_start_date|date|after_or_equal:registration_start_date',
            'status' => 'required|in:PLANNED,OPEN,CLOSED',
        ]);
    }
}
