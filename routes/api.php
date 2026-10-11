<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CooperativeEducationController;
use App\Http\Controllers\Api\CwieWorkflowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\LineWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api.token')->get('/user', function (Request $request) {
    return response()->json($request->user()->only(['id', 'name', 'email', 'role', 'faculty_id']));
});

Route::post('/line/webhook', LineWebhookController::class)->middleware('throttle:120,1')->name('line.webhook');

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('api.token')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/summary', [CooperativeEducationController::class, 'dashboard']);
    Route::get('/students', [CooperativeEducationController::class, 'students']);
    Route::get('/students/{student}', [CooperativeEducationController::class, 'showStudent']);
    Route::patch('/students/{student}', [CooperativeEducationController::class, 'updateStudent']);
    Route::get('/companies', [CooperativeEducationController::class, 'companies']);
    Route::get('/applications', [CooperativeEducationController::class, 'applications']);
    Route::post('/applications', [CooperativeEducationController::class, 'createApplication']);
    Route::post('/applications/{application}/submit', [CooperativeEducationController::class, 'submitApplication']);
    Route::post('/applications/{application}/review', [CooperativeEducationController::class, 'reviewApplication']);

    Route::get('/letter-requests', [CwieWorkflowController::class, 'letterRequests']);
    Route::post('/letter-requests', [CwieWorkflowController::class, 'createLetterRequest']);
    Route::post('/letter-requests/{letterRequest}/check-documents', [CwieWorkflowController::class, 'checkLetterDocuments']);
    Route::get('/placements', [CwieWorkflowController::class, 'placements']);
    Route::post('/placements', [CwieWorkflowController::class, 'createPlacement']);
    Route::post('/placements/{placement}/acceptance', [CwieWorkflowController::class, 'updatePlacementAcceptance']);
    Route::get('/work-plans', [CwieWorkflowController::class, 'workPlans']);
    Route::post('/work-plans', [CwieWorkflowController::class, 'createWorkPlan']);
    Route::post('/work-plans/{workPlan}/submit', [CwieWorkflowController::class, 'submitWorkPlan']);
    Route::get('/report-outlines', [CwieWorkflowController::class, 'reportOutlines']);
    Route::post('/report-outlines', [CwieWorkflowController::class, 'createReportOutline']);
    Route::post('/report-outlines/{outline}/submit', [CwieWorkflowController::class, 'submitReportOutline']);
    Route::get('/supervision-requests', [CwieWorkflowController::class, 'supervisionRequests']);
    Route::post('/supervision-requests', [CwieWorkflowController::class, 'createSupervisionRequest']);
    Route::post('/supervision-schedules/{schedule}/result', [CwieWorkflowController::class, 'createSupervisionResult']);
    Route::post('/approvals/{approval}/action', [CwieWorkflowController::class, 'actOnApproval']);
    Route::post('/documents/upload', [CwieWorkflowController::class, 'uploadDocument']);
    Route::get('/documents/{document}/download', [CwieWorkflowController::class, 'downloadDocument']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('/reports/{reportKey}', [ReportController::class, 'show']);
});
