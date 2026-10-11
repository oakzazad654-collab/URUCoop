<?php

namespace App\Http\Controllers;

use App\Models\ApprovalHistory;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Placement;
use App\Models\Staff;
use App\Models\User;
use App\Services\DataAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    private function indexRoute(Request $request)
    {
        return $request->user()->role === 'officer' ? 'officer.users.index' : 'admin.users.index';
    }

    private function assignmentRoute(Request $request)
    {
        return $request->user()->role === 'officer' ? 'officer.users.assign-advisor' : 'admin.users.assign-advisor';
    }

    private function roleOptions(Request $request)
    {
        return $request->user()->role === 'admin'
            ? ['student', 'advisor', 'supervisor', 'mentor', 'officer', 'executive', 'company', 'admin']
            : ['student', 'advisor', 'officer'];
    }

    private function syncSupervisorStaffProfile(User $user)
    {
        if ($user->role !== 'supervisor') {
            return;
        }

        $staff = $user->staffProfile ?: new Staff();
        $facultyChanged = $staff->exists && (int) $staff->faculty_id !== (int) $user->faculty_id;
        $staff->user_id = $user->id;
        $staff->staff_code = $staff->staff_code ?: 'SUP-'.$user->id;
        $staff->faculty_id = $user->faculty_id;
        if ($facultyChanged) {
            $staff->program_id = null;
        }
        $staff->save();
    }

    private function assertCanManage(Request $request, User $target = null)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'officer'], true), 403);
        if ($request->user()->role === 'officer') {
            abort_unless(!$target || $target->role !== 'admin', 403);
            $facultyId = $this->access->facultyId($request->user());
            if ($target && $facultyId && !$this->access->isGlobalOperator($request->user())) {
                if ($target->role === 'student') {
                    abort_unless($this->access->canAccessLegacyStudent($request->user(), $target), 403);
                } else {
                    $targetFacultyId = $target->faculty_id ?: $this->access->facultyId($target);
                    abort_unless((int) $targetFacultyId === $facultyId, 403);
                }
            }
        }
    }

    private function validateUser(Request $request, User $user = null)
    {
        $roleOptions = implode(',', $this->roleOptions($request));
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email'.($user ? ','.$user->id : ''),
            'role' => 'required|in:'.$roleOptions,
            'password' => ($user ? 'nullable' : 'required').'|string|min:6',
            'student_number' => 'nullable|string|max:50|unique:users,student_number'.($user ? ','.$user->id : ''),
            'advisor_id' => 'nullable|integer|exists:users,id',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'company_id' => 'nullable|required_if:role,company|required_if:role,mentor|integer|exists:companies,id',
            'position_name' => 'nullable|required_if:role,mentor|string|max:150',
            'phone' => 'nullable|string|max:30',
        ]);

        $operator = $request->user();
        $operatorFacultyId = $this->access->facultyId($operator);
        if ($operator->role === 'officer' && $operatorFacultyId) {
            if (in_array($validated['role'], ['student', 'advisor'], true)) {
                $validated['faculty_id'] = $validated['faculty_id'] ?? $operatorFacultyId;
                abort_unless((int) $validated['faculty_id'] === $operatorFacultyId, 403);
            }
            if (!empty($validated['advisor_id'])) {
                abort_unless((int) User::whereKey($validated['advisor_id'])->value('faculty_id') === $operatorFacultyId, 403);
            }
        }

        if (!empty($validated['advisor_id'])) {
            abort_unless(User::whereKey($validated['advisor_id'])->where('role', 'advisor')->exists(), 422, 'บัญชีที่เลือกไม่ใช่อาจารย์ที่ปรึกษา');
            abort_unless(($validated['role'] ?? $user->role ?? null) === 'student', 422, 'กำหนดอาจารย์ที่ปรึกษาได้เฉพาะบัญชีนักศึกษา');
        }
        if (!empty($validated['department_id']) && !empty($validated['faculty_id'])) {
            abort_unless(Department::whereKey($validated['department_id'])->where('faculty_id', $validated['faculty_id'])->exists(), 422, 'สาขาวิชาไม่อยู่ในคณะที่เลือก');
        }
        if ($validated['role'] === 'supervisor') {
            abort_unless(!empty($validated['faculty_id']), 422, 'กรุณาระบุคณะสำหรับอาจารย์นิเทศ');
        }
        if ($validated['role'] === 'mentor') {
            abort_unless(Company::whereKey($validated['company_id'])
                ->where(function ($query) {
                    $query->whereIn('status', ['ACTIVE', 'active'])->orWhereNull('status');
                })->exists(), 422, 'เลือกได้เฉพาะสถานประกอบการที่ได้รับอนุมัติแล้ว');
        }
        if (!in_array($validated['role'], ['company', 'mentor'], true)) {
            $validated['company_id'] = null;
        }

        return $validated;
    }

    private function syncMentorCompanyContact(User $user, $positionName)
    {
        if ($user->role !== 'mentor') {
            return;
        }

        $contact = CompanyContact::where('user_id', $user->id)->first();
        if ($contact && (int) $contact->company_id !== (int) $user->company_id) {
            abort_unless(!Placement::where('mentor_contact_id', $contact->contact_id)->exists(), 422, 'ไม่สามารถเปลี่ยนสถานประกอบการของผู้นิเทศที่มีนักศึกษาผูกอยู่');
        }

        $nameParts = preg_split('/\s+/', trim($user->name), 2);
        $attributes = [
            'company_id' => $user->company_id,
            'contact_type' => 'MENTOR',
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'position_name' => $positionName,
            'phone' => $user->phone,
            'email' => $user->email,
            'status' => $user->status ?: 'ACTIVE',
            'user_id' => $user->id,
        ];

        if ($contact) {
            $contact->update($attributes);
        } else {
            CompanyContact::create($attributes);
        }
    }

    private function formData()
    {
        $user = request()->user();
        $facultyId = $this->access->facultyId($user);
        $advisors = User::where('role', 'advisor')->orderBy('name');
        $faculties = Faculty::with('departments')->orderBy('name');
        if ($user->role === 'officer' && $facultyId) {
            $advisors->where('faculty_id', $facultyId);
            $faculties->whereKey($facultyId);
        }

        return [
            'advisors' => $advisors->get(),
            'faculties' => $faculties->get(),
            'companies' => Company::where(function ($query) {
                $query->whereIn('status', ['ACTIVE', 'active'])->orWhereNull('status');
            })->orderBy('name')->get(),
            'roles' => $this->roleOptions(request()),
        ];
    }

    public function index(Request $request)
    {
        $this->assertCanManage($request);
        $query = User::withTrashed()->with(['advisor', 'faculty', 'department', 'company'])->orderBy('name');
        if ($request->user()->role === 'officer') {
            $query->where('role', '!=', 'admin');
            $facultyId = $this->access->facultyId($request->user());
            if ($facultyId && !$this->access->isGlobalOperator($request->user())) {
                $visibleStudents = $this->access->legacyStudents($request->user())->select('id');
                $query->where(function ($users) use ($facultyId, $visibleStudents) {
                    $users->whereIn('id', $visibleStudents)
                        ->orWhere('faculty_id', $facultyId)
                        ->orWhereHas('department', function ($departments) use ($facultyId) {
                            $departments->where('faculty_id', $facultyId);
                        });
                });
            }
        }
        if ($request->filled('role') && in_array($request->role, $this->roleOptions($request), true)) $query->where('role', $request->role);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(10)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $this->assertCanManage($request);
        $user = new User(['role' => 'student']);
        return view('admin.users.form', array_merge($this->formData(), compact('user')));
    }

    public function store(Request $request)
    {
        $this->assertCanManage($request);
        $validated = $this->validateUser($request);
        $positionName = $validated['position_name'] ?? null;
        unset($validated['position_name']);
        $validated['password'] = Hash::make($validated['password']);
        $user = DB::transaction(function () use ($validated, $positionName) {
            $user = User::create($validated);
            $this->syncSupervisorStaffProfile($user);
            $this->syncMentorCompanyContact($user, $positionName);
            return $user;
        });
        return redirect()->route($this->indexRoute($request))->with('success', 'เพิ่มบัญชีผู้ใช้แล้ว');
    }

    public function edit(Request $request, User $user)
    {
        $this->assertCanManage($request, $user);
        return view('admin.users.form', array_merge($this->formData(), compact('user')));
    }

    public function update(Request $request, User $user)
    {
        $this->assertCanManage($request, $user);
        $validated = $this->validateUser($request, $user);
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            abort_unless(User::where('role', 'admin')->count() > 1, 422, 'ต้องมีผู้ดูแลระบบอย่างน้อยหนึ่งบัญชี');
        }
        if ($user->role === 'mentor' && $validated['role'] !== 'mentor') {
            abort(422, 'กรุณาคงบทบาทผู้นิเทศงานไว้เพื่อรักษาการเชื่อมโยงกับสถานประกอบการ');
        }
        if ((int) $request->user()->id === (int) $user->id && $validated['role'] !== $user->role) {
            abort(422, 'ไม่สามารถเปลี่ยนบทบาทของบัญชีที่กำลังใช้งานได้');
        }
        if (empty($validated['password'])) unset($validated['password']);
        else $validated['password'] = Hash::make($validated['password']);
        $positionName = $validated['position_name'] ?? null;
        unset($validated['position_name']);
        if ($validated['role'] !== 'student') {
            $validated['student_number'] = null;
            $validated['advisor_id'] = null;
        }
        DB::transaction(function () use ($user, $validated) {
            $user->update($validated);
            $this->syncSupervisorStaffProfile($user);
            $this->syncMentorCompanyContact($user, $positionName);
        });
        return redirect()->route($this->indexRoute($request))->with('success', 'อัปเดตบัญชีผู้ใช้แล้ว');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->assertCanManage($request, $user);
        abort_if($user->trashed(), 422, 'ไม่สามารถรีเซ็ตรหัสผ่านบัญชีที่ถูกปิดใช้งานแล้ว');

        $user->update(['password' => Hash::make(env('URU_COOP_DEMO_PASSWORD', 'password'))]);

        return redirect()->route($this->indexRoute($request))->with('success', 'รีเซ็ตรหัสผ่านเป็นค่าเริ่มต้นแล้ว');
    }

    public function destroy(Request $request, User $user)
    {
        $this->assertCanManage($request, $user);
        abort_unless((int) $request->user()->id !== (int) $user->id, 422, 'ไม่สามารถลบบัญชีที่กำลังใช้งานได้');
        if ($user->role === 'admin') abort_unless(User::where('role', 'admin')->count() > 1, 422, 'ต้องมีผู้ดูแลระบบอย่างน้อยหนึ่งบัญชี');
        $user->delete();
        return redirect()->route($this->indexRoute($request))->with('success', 'ลบบัญชีผู้ใช้แล้ว');
    }

    public function assignAdvisor(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'officer'], true), 403);
        $students = $this->access->legacyStudents($request->user())
            ->with(['advisor', 'faculty', 'department'])->orderBy('name')->get();
        $advisors = User::where('role', 'advisor')->orderBy('name');
        $faculties = Faculty::orderBy('name');
        $facultyId = $this->access->facultyId($request->user());
        if ($request->user()->role === 'officer' && $facultyId && !$this->access->isGlobalOperator($request->user())) {
            $advisors->where('faculty_id', $facultyId);
            $faculties->whereKey($facultyId);
        }
        $advisors = $advisors->get();
        $faculties = $faculties->get();
        return view('admin.users.assign-advisor', compact('students', 'advisors', 'faculties'));
    }

    public function saveAdvisorAssignments(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'officer'], true), 403);
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:users,id',
            'advisor_id' => 'nullable|integer|exists:users,id',
        ]);
        $studentIds = $this->access->legacyStudents($request->user())
            ->whereIn('id', $validated['student_ids'])->pluck('id');
        abort_unless($studentIds->count() === count($validated['student_ids']), 422, 'เลือกได้เฉพาะบัญชีนักศึกษา');
        if (!empty($validated['advisor_id'])) {
            $advisor = User::whereKey($validated['advisor_id'])->where('role', 'advisor')->first();
            abort_unless($advisor, 422, 'บัญชีที่เลือกไม่ใช่อาจารย์ที่ปรึกษา');
            $facultyId = $this->access->facultyId($request->user());
            if ($request->user()->role === 'officer' && $facultyId && !$this->access->isGlobalOperator($request->user())) {
                abort_unless((int) $advisor->faculty_id === $facultyId, 403);
            }
        }

        DB::transaction(function () use ($studentIds, $validated) {
            User::whereIn('id', $studentIds)->update(['advisor_id' => $validated['advisor_id'] ?? null]);
        });
        return redirect()->route($this->assignmentRoute($request))->with('success', 'บันทึกการจับคู่อาจารย์ที่ปรึกษาแล้ว');
    }

    public function advisees(Request $request)
    {
        $students = $this->access->legacyStudents($request->user())
            ->where('advisor_id', $request->user()->id)
            ->with(['faculty', 'department'])->orderBy('name')->get();
        return view('advisor.advisees', compact('students'));
    }

    public function auditLogs()
    {
        $histories = ApprovalHistory::with('actor')->latest()->paginate(50);
        return view('admin.audit-logs', compact('histories'));
    }
}