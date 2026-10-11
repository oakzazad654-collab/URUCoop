<?php

namespace App\Http\Controllers;

use App\Models\ApprovalHistory;
use App\Models\Company;
use App\Models\CwieDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CwieNotificationService;
use App\Services\DataAccessService;

class ApprovalController extends Controller
{
    private $access;

    public function __construct(DataAccessService $access)
    {
        $this->access = $access;
    }

    private function checkType($type, CwieDocument $document)
    {
        abort_unless(preg_match('/^cwie-0[1-6]$/', $type) && $document->type === $type, 404);
    }

    private function canReview($user, CwieDocument $document)
    {
        if ($user->role === 'admin') return in_array($document->status, ['pending_advisor', 'pending_officer'], true);
        if ($user->role === 'advisor') {
            return $document->status === 'pending_advisor' && $document->owner
                && (int) $document->owner->advisor_id === (int) $user->id
                && $this->access->canAccessLegacyStudent($user, $document->owner)
                && (!$document->advisor_id || (int) $document->advisor_id === (int) $user->id);
        }
        if ($user->role === 'officer') {
            return $document->status === 'pending_officer' && $document->owner
                && $this->access->canAccessLegacyStudent($user, $document->owner)
                && (!$document->officer_id || (int) $document->officer_id === (int) $user->id);
        }
        return false;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $query = CwieDocument::with('owner')->latest();
        $scope = CwieDocument::query();

        if ($user->role === 'student') {
            $query->where('user_id', $user->id);
            $scope->where('user_id', $user->id);
        } elseif ($user->role === 'advisor') {
            $visibleStudentIds = $this->access->legacyStudents($user)->select('id');
            $query->where('status', 'pending_advisor')->whereHas('owner', function ($query) use ($user, $visibleStudentIds) {
                $query->where('advisor_id', $user->id)->whereIn('users.id', $visibleStudentIds);
            })->where(function ($query) use ($user) {
                $query->whereNull('advisor_id')->orWhere('advisor_id', $user->id);
            });
            $scope->whereHas('owner', function ($query) use ($user, $visibleStudentIds) {
                $query->where('advisor_id', $user->id)->whereIn('users.id', $visibleStudentIds);
            });
        } elseif ($user->role === 'officer') {
            $visibleStudentIds = $this->access->legacyStudents($user)->select('id');
            $query->where('status', 'pending_officer')->where(function ($query) use ($user) {
                $query->whereNull('officer_id')->orWhere('officer_id', $user->id);
            })->whereIn('user_id', $visibleStudentIds);
            $scope->whereIn('user_id', $visibleStudentIds);
        } else {
            $query->whereIn('status', ['pending_advisor', 'pending_officer']);
        }

        $documents = $query->get();
        $stats = [
            'total' => (clone $scope)->count(),
            'pending' => (clone $scope)->whereIn('status', ['pending_advisor', 'pending_officer'])->count(),
            'approved' => (clone $scope)->where('status', 'approved')->count(),
            'revision' => (clone $scope)->whereIn('status', ['revision_requested', 'rejected'])->count(),
            'companies' => $this->access->isGlobalOperator($user) ? Company::count() : null,
        ];
        return view('approvals.dashboard', compact('documents', 'stats'));
    }

    public function review(Request $request, $type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        $user = $request->user();
        if ($user->role === 'student') {
            abort_unless((int) $document->user_id === (int) $user->id, 403);
        } else {
            abort_unless($this->canReview($user, $document), 403, 'เอกสารนี้ไม่ได้อยู่ในคิวพิจารณาของคุณ');
        }

        $definition = app(CwieDocumentController::class)->definitionForApproval($type);
        $document->load(['owner', 'advisor', 'officer']);
        $histories = ApprovalHistory::with('actor')
            ->where('document_type', $type)
            ->where('document_id', $document->id)
            ->orderBy('created_at')
            ->get();

        return view('approvals.review', compact('type', 'document', 'definition', 'histories'));
    }

    public function submitForApproval(Request $request, $type, CwieDocument $document)
    {
        $this->checkType($type, $document);
        abort_unless((int) $document->user_id === (int) $request->user()->id, 403);

        $owner = $document->owner;
        abort_unless($owner, 422, 'ไม่พบเจ้าของเอกสาร');
        if ($type !== 'cwie-06') abort_unless($owner->advisor_id, 422, 'กรุณาติดต่อเจ้าหน้าที่เพื่อกำหนดอาจารย์ที่ปรึกษาก่อนส่งเอกสาร');

        $updatedDocument = DB::transaction(function () use ($request, $type, $document) {
            $locked = CwieDocument::whereKey($document->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['draft', 'revision_requested'], true), 422, 'สถานะเอกสารไม่สามารถส่งพิจารณาได้');

            $previousStatus = $locked->status;
            $locked->status = 'pending_advisor';
            $locked->save();
            $this->record($locked, $request->user()->id, $previousStatus === 'draft' ? 'submitted' : 'resubmitted', null);
            return $locked->fresh(['owner.advisor']);
        });

        app(CwieNotificationService::class)->documentSubmitted($updatedDocument);
        return redirect()->route($this->dashboardRoute($request->user()))->with('success', 'ส่งเอกสารให้อาจารย์ที่ปรึกษาพิจารณาแล้ว');
    }

    public function approveByAdvisor(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'advisor', 'approve');
    }

    public function rejectByAdvisor(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'advisor', 'reject');
    }

    public function requestRevisionByAdvisor(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'advisor', 'revision');
    }

    public function approveByOfficer(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'officer', 'approve');
    }

    public function rejectByOfficer(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'officer', 'reject');
    }

    public function requestRevisionByOfficer(Request $request, $type, CwieDocument $document)
    {
        return $this->decide($request, $type, $document, 'officer', 'revision');
    }

    private function decide(Request $request, $type, CwieDocument $document, $stage, $decision)
    {
        $this->checkType($type, $document);
        $request->validate([
            'comment' => $decision === 'approve' ? 'nullable|string|max:5000' : 'required|string|max:5000',
        ]);

        $updatedDocument = DB::transaction(function () use ($request, $type, $document, $stage, $decision) {
            $locked = CwieDocument::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $user = $request->user();
            $expectedStatus = $stage === 'advisor' ? 'pending_advisor' : 'pending_officer';
            abort_unless($locked->status === $expectedStatus, 422, 'เอกสารถูกดำเนินการหรือเปลี่ยนสถานะไปแล้ว');
            abort_unless($user->role === 'admin' || $user->role === $stage, 403);

            if ($stage === 'advisor' && $user->role === 'advisor') {
                $owner = $locked->owner;
                abort_unless($owner && (int) $owner->advisor_id === (int) $user->id
                    && $this->access->canAccessLegacyStudent($user, $owner), 403, 'นักศึกษารายนี้ไม่ได้อยู่ในขอบเขตของคุณ');
            }
            if ($stage === 'officer' && $user->role === 'officer') {
                abort_unless($locked->owner && $this->access->canAccessLegacyStudent($user, $locked->owner), 403);
            }

            $assignedReviewer = $stage === 'advisor' ? $locked->advisor_id : $locked->officer_id;
            abort_unless(!$assignedReviewer || (int) $assignedReviewer === (int) $user->id || $user->role === 'admin', 403, 'เอกสารนี้มอบหมายให้ผู้พิจารณาท่านอื่น');

            $now = now();
            $comment = $request->input('comment');
            if ($stage === 'advisor') {
                $locked->advisor_id = $user->id;
                $locked->advisor_comment = $comment;
                if ($decision === 'approve') {
                    $locked->advisor_approved_at = $now;
                    $locked->status = 'pending_officer';
                } elseif ($decision === 'reject') {
                    $locked->advisor_rejected_at = $now;
                    $locked->status = 'rejected';
                } else {
                    $locked->status = 'revision_requested';
                }
            } else {
                $locked->officer_id = $user->id;
                $locked->officer_comment = $comment;
                if ($decision === 'approve') {
                    $locked->officer_approved_at = $now;
                    $locked->status = 'approved';
                } elseif ($decision === 'reject') {
                    $locked->officer_rejected_at = $now;
                    $locked->status = 'rejected';
                } else {
                    $locked->status = 'revision_requested';
                }
            }

            $locked->save();
            $action = $stage.'_'.$decision;
            $this->record($locked, $user->id, $action, $comment);
            return $locked->fresh(['owner.advisor']);
        });

        $notifications = app(CwieNotificationService::class);
        if ($decision === 'approve') $notifications->documentApproved($updatedDocument, $stage);
        elseif ($decision === 'revision') $notifications->documentRevisionRequested($updatedDocument, $request->input('comment'), $stage);
        else $notifications->documentRejected($updatedDocument, $request->input('comment'), $stage);

        return redirect()->route($this->dashboardRoute($request->user()))->with('success', 'บันทึกผลการพิจารณาแล้ว');
    }

    private function dashboardRoute($user)
    {
        return [
            'student' => 'student.approvals.dashboard',
            'advisor' => 'advisor.approvals.dashboard',
            'officer' => 'officer.approvals.dashboard',
            'admin' => 'admin.approvals.dashboard',
        ][$user->role] ?? 'login';
    }

    private function record(CwieDocument $document, $userId, $action, $comment)
    {
        ApprovalHistory::create([
            'document_type' => $document->type,
            'document_id' => $document->id,
            'action_by' => $userId,
            'action_type' => $action,
            'comment' => $comment,
        ]);
    }
}