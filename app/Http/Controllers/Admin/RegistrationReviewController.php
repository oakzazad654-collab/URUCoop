<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationReviewController extends Controller
{
    public function index()
    {
        $users = User::with(['company', 'faculty', 'department'])
            ->where('status', 'PENDING')->orderBy('created_at')->paginate(25);

        return view('admin.registrations.index', compact('users'));
    }

    public function approve(User $user)
    {
        abort_unless($user->status === 'PENDING'
            && in_array($user->role, ['advisor', 'supervisor', 'mentor', 'company'], true), 422, 'บัญชีนี้ไม่อยู่ระหว่างรออนุมัติ');

        DB::transaction(function () use ($user) {
            $user->status = 'ACTIVE';
            $user->save();

            if ($user->role === 'company' && $user->company_id) {
                Company::whereKey($user->company_id)->update(['status' => 'active']);
            }
            if ($user->role === 'mentor') {
                CompanyContact::where('user_id', $user->id)->update(['status' => 'ACTIVE']);
            }
        });

        return redirect()->route('admin.registrations.index')->with('success', 'อนุมัติบัญชีและเปิดใช้งานแล้ว');
    }

    public function reject(User $user)
    {
        abort_unless($user->status === 'PENDING'
            && in_array($user->role, ['advisor', 'supervisor', 'mentor', 'company'], true), 422, 'บัญชีนี้ไม่อยู่ระหว่างรออนุมัติ');

        DB::transaction(function () use ($user) {
            $user->status = 'REJECTED';
            $user->save();

            if ($user->role === 'company' && $user->company_id) {
                Company::whereKey($user->company_id)->update(['status' => 'rejected']);
            }
            if ($user->role === 'mentor') {
                CompanyContact::where('user_id', $user->id)->update(['status' => 'REJECTED']);
            }
        });

        return redirect()->route('admin.registrations.index')->with('success', 'ปฏิเสธคำขอสมัครแล้ว');
    }
}
