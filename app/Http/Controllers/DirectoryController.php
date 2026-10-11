<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Staff;
use App\Models\Student;

class DirectoryController extends Controller
{
    public function index()
    {
        return view('directories.cooperative-education', [
            'students' => Student::with(['user', 'program.faculty'])
                ->orderBy('student_code')->paginate(15, ['*'], 'students_page'),
            'companies' => Company::orderBy('name')->paginate(15, ['*'], 'companies_page'),
            'staffMembers' => Staff::with(['user', 'faculty', 'program'])
                ->whereHas('user', function ($users) {
                    $users->whereIn('role', ['advisor', 'supervisor', 'officer']);
                })->orderBy('staff_code')->paginate(15, ['*'], 'staff_page'),
        ]);
    }
}
