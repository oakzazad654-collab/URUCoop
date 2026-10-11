<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register', [
            'faculties' => Faculty::with('departments')->orderBy('name')->get(),
            'programs' => Program::where('status', 'ACTIVE')->orderBy('program_name')->get(),
            'companies' => Company::whereIn('status', ['ACTIVE', 'active'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $role = $request->input('role');
        abort_unless(in_array($role, ['student', 'advisor', 'supervisor', 'mentor', 'company'], true), 422, 'เลือกประเภทบัญชีไม่ถูกต้อง');

        $rules = [
            'role' => 'required|in:student,advisor,supervisor,mentor,company',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:10|confirmed',
        ];

        if ($role === 'student') {
            $rules += [
                'student_number' => 'required|string|max:50|unique:users,student_number',
                'faculty_id' => 'required|integer|exists:faculties,id',
                'department_id' => 'required|integer|exists:departments,id',
                'program_id' => 'required|integer|exists:programs,program_id',
                'title_name' => 'nullable|string|max:30',
                'gender' => 'nullable|in:MALE,FEMALE,OTHER,UNSPECIFIED',
                'birth_date' => 'nullable|date|before:today',
                'nationality' => 'nullable|string|max:100',
                'religion' => 'nullable|string|max:100',
                'current_address' => 'nullable|string|max:5000',
                'emergency_contact_name' => 'nullable|string|max:200',
                'emergency_contact_relation' => 'nullable|string|max:100',
                'emergency_contact_phone' => 'nullable|string|max:30',
                'cumulative_gpa' => 'nullable|numeric|between:0,4',
            ];
        } elseif (in_array($role, ['advisor', 'supervisor'], true)) {
            $rules += [
                'faculty_id' => 'required|integer|exists:faculties,id',
                'department_id' => 'required|integer|exists:departments,id',
                'program_id' => 'required|integer|exists:programs,program_id',
                'staff_code' => 'required|string|max:30|unique:staff,staff_code',
                'position_name' => 'nullable|string|max:150',
            ];
        } elseif ($role === 'company') {
            $rules += [
                'company_name' => 'required|string|max:255',
                'business_type' => 'required|string|max:200',
                'address' => 'required|string|max:2000',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'contact_person' => 'required|string|max:255',
                'tax_id' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'province' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
            ];
        } else {
            $rules += [
                'company_id' => 'required|integer|exists:companies,id',
                'position_name' => 'required|string|max:150',
            ];
        }

        $validated = $request->validate($rules);
        if (isset($validated['department_id'], $validated['faculty_id'])) {
            abort_unless(Department::whereKey($validated['department_id'])
                ->where('faculty_id', $validated['faculty_id'])->exists(), 422, 'สาขาไม่อยู่ในคณะที่เลือก');
        }
        if (in_array($role, ['student', 'advisor', 'supervisor'], true)) {
            abort_unless(Program::where('program_id', $validated['program_id'])
                ->where('faculty_id', $validated['faculty_id'])
                ->where('status', 'ACTIVE')->exists(), 422, 'หลักสูตรไม่อยู่ในคณะที่เลือกหรือไม่เปิดใช้งาน');
        }
        if ($role === 'mentor') {
            abort_unless(Company::whereKey($validated['company_id'])
                ->whereIn('status', ['ACTIVE', 'active'])->exists(), 422, 'เลือกได้เฉพาะสถานประกอบการที่ได้รับอนุมัติแล้ว');
        }

        $user = DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'status' => $role === 'student' ? 'ACTIVE' : 'PENDING',
                'first_name' => $validated['name'],
                'last_name' => '',
                'student_number' => $role === 'student' ? $validated['student_number'] : null,
                'faculty_id' => $validated['faculty_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'company_id' => $role === 'mentor' ? $validated['company_id'] : null,
            ]);

            if ($role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_code' => $validated['student_number'],
                    'program_id' => $validated['program_id'],
                    'title_name' => $validated['title_name'] ?? null,
                    'gender' => $validated['gender'] ?? 'UNSPECIFIED',
                    'birth_date' => $validated['birth_date'] ?? null,
                    'nationality' => $validated['nationality'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'current_address' => $validated['current_address'] ?? null,
                    'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                    'emergency_contact_relation' => $validated['emergency_contact_relation'] ?? null,
                    'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                    'cumulative_gpa' => $validated['cumulative_gpa'] ?? null,
                    'student_status' => 'STUDYING',
                ]);
            } elseif (in_array($role, ['advisor', 'supervisor'], true)) {
                Staff::create([
                    'user_id' => $user->id,
                    'staff_code' => $validated['staff_code'],
                    'faculty_id' => $validated['faculty_id'],
                    'program_id' => $validated['program_id'],
                    'position_name' => $validated['position_name'] ?? null,
                ]);
            } elseif ($role === 'company') {
                $company = Company::create([
                    'name' => $validated['company_name'],
                    'company_name_th' => $validated['company_name'],
                    'business_type' => $validated['business_type'],
                    'address' => $validated['address'],
                    'tax_id' => $validated['tax_id'] ?? null,
                    'website' => $validated['website'] ?? null,
                    'province' => $validated['province'] ?? null,
                    'postal_code' => $validated['postal_code'] ?? null,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'contact_person' => $validated['contact_person'],
                    'created_by_id' => $user->id,
                    'status' => 'pending',
                ]);
                $user->company_id = $company->id;
                $user->save();
            } elseif ($role === 'mentor') {
                $nameParts = preg_split('/\s+/', trim($validated['name']), 2);
                CompanyContact::create([
                    'company_id' => $validated['company_id'],
                    'contact_type' => 'MENTOR',
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'position_name' => $validated['position_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'status' => 'PENDING',
                    'user_id' => $user->id,
                ]);
            }

            return $user;
        });

        if ($role === 'student') {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('student.dashboard')->with('success', 'สมัครบัญชีนักศึกษาเรียบร้อยแล้ว');
        }

        return redirect()->route('login')->with('status', 'ส่งคำขอสมัครแล้ว บัญชีจะเข้าใช้งานได้หลัง Admin ตรวจสอบและอนุมัติ');
    }
}
