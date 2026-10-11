<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AcademicTermController;
use App\Http\Controllers\Admin\SiteAppearanceController;
use App\Http\Controllers\Admin\SkFormPositionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyPortalController;
use App\Http\Controllers\CwieDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\Admin\RegistrationReviewController;
use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MentorPortalController;
use App\Http\Controllers\NotificationInboxController;
use App\Http\Controllers\PublicInformationController;
use App\Http\Controllers\ReportPageController;
use App\Http\Controllers\StaffDirectoryController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkflowWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnnouncementController::class, 'home'])->name('home');
Route::view('guide', 'landing.guide')->name('public.guide');
Route::get('about-coop', [PublicInformationController::class, 'about'])->name('public.about');
Route::get('forms', [PublicInformationController::class, 'forms'])->name('public.forms');
Route::get('forms/download/{file}', [PublicInformationController::class, 'downloadForm'])
    ->where('file', '[^/]+')->name('public.forms.download');
Route::get('selected-students', [PublicInformationController::class, 'selectedStudents'])->name('public.selected-students');
Route::get('activities', [PublicInformationController::class, 'activities'])->name('public.activities');
Route::get('board', [PublicInformationController::class, 'board'])->name('public.board');
Route::get('news-images/{announcement}', [AnnouncementController::class, 'image'])->whereNumber('announcement')->name('public.news-image');

Route::get('news', [AnnouncementController::class, 'index'])->name('news.index');
Route::get('news/{announcement}', [AnnouncementController::class, 'show'])->whereNumber('announcement')->name('news.show');
Route::get('homepage-background', [SiteAppearanceController::class, 'homepageBackground'])->name('homepage.background');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegistrationController::class, 'create'])->name('register');
    Route::post('register', [RegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');
});
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:student,advisor,officer,supervisor,executive,admin'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/', [ReportPageController::class, 'index'])->name('index');
        Route::get('/{reportKey}', [ReportPageController::class, 'show'])->name('show');
    });

Route::get('api/companies/locations', [MapController::class, 'locations'])
    ->middleware(['auth', 'role:student,advisor,supervisor,officer,admin'])
    ->name('api.companies.locations');

Route::middleware(['auth', 'role:student,advisor,officer,admin,supervisor,mentor,executive'])
    ->prefix('workflow')
    ->name('workflow.')
    ->group(function () {
        Route::get('/', [WorkflowWebController::class, 'index'])->name('index');
        Route::post('/applications', [WorkflowWebController::class, 'storeApplication'])->name('applications.store');
        Route::post('/applications/{application}/submit', [WorkflowWebController::class, 'submitApplication'])->name('applications.submit');
        Route::post('/applications/{application}/review', [WorkflowWebController::class, 'reviewApplication'])->name('applications.review');
        Route::post('/letter-requests', [WorkflowWebController::class, 'storeLetterRequest'])->name('letter-requests.store');
        Route::post('/letter-requests/{letterRequest}/check', [WorkflowWebController::class, 'checkLetterDocuments'])->name('letter-requests.check');
        Route::post('/placements', [WorkflowWebController::class, 'storePlacement'])->name('placements.store');
        Route::post('/placements/{placement}/acceptance', [WorkflowWebController::class, 'updatePlacementAcceptance'])->name('placements.acceptance');
        Route::post('/work-plans', [WorkflowWebController::class, 'storeWorkPlan'])->name('work-plans.store');
        Route::post('/work-plans/{workPlan}/submit', [WorkflowWebController::class, 'submitWorkPlan'])->name('work-plans.submit');
        Route::post('/report-outlines', [WorkflowWebController::class, 'storeReportOutline'])->name('report-outlines.store');
        Route::post('/report-outlines/{outline}/submit', [WorkflowWebController::class, 'submitReportOutline'])->name('report-outlines.submit');
        Route::post('/supervision-requests', [WorkflowWebController::class, 'storeSupervisionRequest'])->name('supervision-requests.store');
        Route::post('/supervision-schedules/{schedule}/result', [WorkflowWebController::class, 'storeSupervisionResult'])->name('supervision-schedules.result');
        Route::post('/approvals/{approval}/action', [WorkflowWebController::class, 'actOnApproval'])->name('approvals.action');
        Route::post('/documents', [WorkflowWebController::class, 'uploadDocument'])->name('documents.store');
    });

Route::middleware(['auth', 'portal:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'student'])->name('dashboard');
    Route::get('map', [MapController::class, 'index'])->name('map');
    Route::get('cwie', [CwieDocumentController::class, 'index'])->name('cwie.index');
    $studentFormType = '(?:cwie-0[1-5]|sk-(?:0[1-9]|1[0-5]|12\\.1))';
    Route::get('cwie/{type}/create', [CwieDocumentController::class, 'create'])->where('type', $studentFormType)->name('cwie.create');
    Route::post('cwie/{type}', [CwieDocumentController::class, 'store'])->where('type', $studentFormType)->name('cwie.store');
    Route::get('cwie/{type}/{document}/edit', [CwieDocumentController::class, 'edit'])->where('type', $studentFormType)->name('cwie.edit');
    Route::put('cwie/{type}/{document}', [CwieDocumentController::class, 'update'])->where('type', $studentFormType)->name('cwie.update');
    Route::delete('cwie/{type}/{document}', [CwieDocumentController::class, 'destroy'])->where('type', $studentFormType)->name('cwie.destroy');
    Route::get('cwie/{type}/{document}/pdf', [CwieDocumentController::class, 'pdf'])->where('type', $studentFormType)->name('cwie.pdf');
    Route::get('cwie/{type}/{document}', [CwieDocumentController::class, 'show'])->where('type', $studentFormType)->name('cwie.show');

    Route::get('approvals', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
    Route::get('approvals/{type}/{document}', [ApprovalController::class, 'review'])->where('type', 'cwie-0[1-5]')->name('approvals.review');
    Route::post('approvals/{type}/{document}/submit', [ApprovalController::class, 'submitForApproval'])->where('type', 'cwie-0[1-5]')->name('approvals.submit');
});

Route::middleware(['auth', 'role:advisor,officer', 'portal:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'staff'])->name('dashboard');
    Route::get('map', [MapController::class, 'index'])->name('map');
    Route::get('approvals', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
    Route::get('approvals/{type}/{document}', [ApprovalController::class, 'review'])->where('type', 'cwie-0[1-5]')->name('approvals.review');
});

Route::middleware(['auth', 'role:supervisor', 'portal:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'supervisor'])->name('dashboard');
    Route::get('map', [MapController::class, 'index'])->name('map');
    Route::get('companies', [StaffDirectoryController::class, 'companies'])->name('companies');
});

Route::middleware(['auth', 'role:mentor', 'portal:mentor'])->prefix('mentor')->name('mentor.')->group(function () {
    Route::get('dashboard', [MentorPortalController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:executive', 'portal:executive'])->prefix('executive')->name('executive.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'executive'])->name('dashboard');
    Route::get('directory', [DirectoryController::class, 'index'])->name('directory');
});

Route::middleware(['auth', 'role:company', 'portal:company'])->prefix('company')->name('company.')->group(function () {
    Route::get('dashboard', [CompanyPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('placements/{placement}', [CompanyPortalController::class, 'showPlacement'])->name('placements.show');
    Route::post('placements/{placement}/acceptance', [CompanyPortalController::class, 'updateAcceptance'])->name('placements.acceptance');
});

Route::middleware(['auth', 'role:advisor', 'portal:staff'])->prefix('advisor')->group(function () {
    Route::get('approvals', [ApprovalController::class, 'dashboard'])->name('advisor.approvals.dashboard');
    Route::get('approvals/{type}/{document}', [ApprovalController::class, 'review'])->where('type', 'cwie-0[1-5]')->name('advisor.approvals.review');
    Route::post('approvals/{type}/{document}/approve', [ApprovalController::class, 'approveByAdvisor'])->where('type', 'cwie-0[1-5]')->name('advisor.approvals.approve');
    Route::post('approvals/{type}/{document}/revision', [ApprovalController::class, 'requestRevisionByAdvisor'])->where('type', 'cwie-0[1-5]')->name('advisor.approvals.revision');
    Route::post('approvals/{type}/{document}/reject', [ApprovalController::class, 'rejectByAdvisor'])->where('type', 'cwie-0[1-5]')->name('advisor.approvals.reject');
    Route::get('students', [UserManagementController::class, 'advisees'])->name('advisor.students');
    Route::get('companies', [StaffDirectoryController::class, 'companies'])->name('advisor.companies');
    Route::get('cwie/{type}/{document}/pdf', [CwieDocumentController::class, 'pdf'])->where('type', 'cwie-0[1-5]')->name('advisor.cwie.pdf');
    Route::get('cwie/06/create', [CwieDocumentController::class, 'createAdvisorCwie06'])->name('advisor.cwie06.create');
    Route::post('cwie/06', [CwieDocumentController::class, 'storeAdvisorCwie06'])->name('advisor.cwie06.store');
    Route::get('cwie/06/{document}', [CwieDocumentController::class, 'showAdvisorCwie06'])->name('advisor.cwie06.show');
    Route::get('cwie/06/{document}/pdf', [CwieDocumentController::class, 'pdfAdvisorCwie06'])->name('advisor.cwie06.pdf');
});

Route::middleware(['auth', 'role:officer', 'portal:staff'])->prefix('officer')->group(function () {
    Route::get('approvals', [ApprovalController::class, 'dashboard'])->name('officer.approvals.dashboard');
    Route::get('documents', [CwieDocumentController::class, 'officerDocuments'])->name('officer.documents');
    Route::get('approvals/{type}/{document}', [ApprovalController::class, 'review'])->where('type', 'cwie-0[1-6]')->name('officer.approvals.review');
    Route::post('approvals/{type}/{document}/approve', [ApprovalController::class, 'approveByOfficer'])->where('type', 'cwie-0[1-6]')->name('officer.approvals.approve');
    Route::post('approvals/{type}/{document}/revision', [ApprovalController::class, 'requestRevisionByOfficer'])->where('type', 'cwie-0[1-6]')->name('officer.approvals.revision');
    Route::post('approvals/{type}/{document}/reject', [ApprovalController::class, 'rejectByOfficer'])->where('type', 'cwie-0[1-6]')->name('officer.approvals.reject');
    Route::get('cwie/{type}/{document}/pdf', [CwieDocumentController::class, 'pdf'])->where('type', 'cwie-0[1-6]')->name('officer.cwie.pdf');
    Route::resource('companies', CompanyController::class)->names([
        'index' => 'officer.companies.index',
        'create' => 'officer.companies.create',
        'store' => 'officer.companies.store',
        'show' => 'officer.companies.show',
        'edit' => 'officer.companies.edit',
        'update' => 'officer.companies.update',
        'destroy' => 'officer.companies.destroy',
    ]);
    Route::get('users', [UserManagementController::class, 'index'])->name('officer.users.index');
    Route::get('users/create', [UserManagementController::class, 'create'])->name('officer.users.create');
    Route::post('users', [UserManagementController::class, 'store'])->name('officer.users.store');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('officer.users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('officer.users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('officer.users.destroy');
    Route::get('assign-advisors', [UserManagementController::class, 'assignAdvisor'])->name('officer.users.assign-advisor');
    Route::post('assign-advisors', [UserManagementController::class, 'saveAdvisorAssignments'])->name('officer.users.save-advisor');
});

Route::middleware(['auth', 'role:admin', 'portal:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('directory', [DirectoryController::class, 'index'])->name('directory');
    Route::get('registrations', [RegistrationReviewController::class, 'index'])->name('registrations.index');
    Route::post('registrations/{user}/approve', [RegistrationReviewController::class, 'approve'])->name('registrations.approve');
    Route::post('registrations/{user}/reject', [RegistrationReviewController::class, 'reject'])->name('registrations.reject');
    Route::get('academic-terms', [AcademicTermController::class, 'index'])->name('academic-terms.index');
    Route::get('academic-terms/create', [AcademicTermController::class, 'create'])->name('academic-terms.create');
    Route::post('academic-terms', [AcademicTermController::class, 'store'])->name('academic-terms.store');
    Route::get('academic-terms/{academicTerm}/edit', [AcademicTermController::class, 'edit'])->name('academic-terms.edit');
    Route::put('academic-terms/{academicTerm}', [AcademicTermController::class, 'update'])->name('academic-terms.update');
    Route::get('map', [MapController::class, 'index'])->name('map');
    Route::get('approvals', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
    Route::get('approvals/{type}/{document}', [ApprovalController::class, 'review'])->where('type', 'cwie-0[1-6]')->name('approvals.review');
    Route::post('approvals/{type}/{document}/advisor/approve', [ApprovalController::class, 'approveByAdvisor'])->where('type', 'cwie-0[1-6]')->name('approvals.advisor.approve');
    Route::post('approvals/{type}/{document}/advisor/revision', [ApprovalController::class, 'requestRevisionByAdvisor'])->where('type', 'cwie-0[1-6]')->name('approvals.advisor.revision');
    Route::post('approvals/{type}/{document}/advisor/reject', [ApprovalController::class, 'rejectByAdvisor'])->where('type', 'cwie-0[1-6]')->name('approvals.advisor.reject');
    Route::post('approvals/{type}/{document}/officer/approve', [ApprovalController::class, 'approveByOfficer'])->where('type', 'cwie-0[1-6]')->name('approvals.officer.approve');
    Route::post('approvals/{type}/{document}/officer/revision', [ApprovalController::class, 'requestRevisionByOfficer'])->where('type', 'cwie-0[1-6]')->name('approvals.officer.revision');
    Route::post('approvals/{type}/{document}/officer/reject', [ApprovalController::class, 'rejectByOfficer'])->where('type', 'cwie-0[1-6]')->name('approvals.officer.reject');
    Route::get('cwie/{type}/{document}/pdf', [CwieDocumentController::class, 'pdf'])->where('type', 'cwie-0[1-6]')->name('cwie.pdf');
    Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserManagementController::class)->except(['show']);
    Route::get('assign-advisors', [UserManagementController::class, 'assignAdvisor'])->name('users.assign-advisor');
    Route::post('assign-advisors', [UserManagementController::class, 'saveAdvisorAssignments'])->name('users.save-advisor');
    Route::get('audit-logs', [UserManagementController::class, 'auditLogs'])->name('audit-logs');
    Route::resource('news', AdminAnnouncementController::class)->except(['show']);
    Route::get('appearance', [SiteAppearanceController::class, 'edit'])->name('appearance.edit');
    Route::put('appearance/background', [SiteAppearanceController::class, 'updateBackground'])->name('appearance.background.update');
    Route::delete('appearance/background', [SiteAppearanceController::class, 'removeBackground'])->name('appearance.background.destroy');
    Route::put('appearance/youtube', [SiteAppearanceController::class, 'updateYoutubeVideo'])->name('appearance.youtube.update');
    Route::get('sk-positions', [SkFormPositionController::class, 'index'])->name('sk-positions.index');
    Route::get('sk-positions/{type}', [SkFormPositionController::class, 'edit'])->where('type', 'sk-(?:0[1-9]|1[0-5]|12\\.1)')->name('sk-positions.edit');
    Route::put('sk-positions/{type}', [SkFormPositionController::class, 'update'])->where('type', 'sk-(?:0[1-9]|1[0-5]|12\\.1)')->name('sk-positions.update');
    Route::delete('sk-positions/{type}', [SkFormPositionController::class, 'reset'])->where('type', 'sk-(?:0[1-9]|1[0-5]|12\\.1)')->name('sk-positions.reset');
    Route::get('sk01-positions', [SkFormPositionController::class, 'edit'])->defaults('type', 'sk-01')->name('sk01-positions.edit');
    Route::put('sk01-positions', [SkFormPositionController::class, 'update'])->defaults('type', 'sk-01')->name('sk01-positions.update');
    Route::delete('sk01-positions', [SkFormPositionController::class, 'reset'])->defaults('type', 'sk-01')->name('sk01-positions.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('account/settings', [AccountSettingsController::class, 'show'])->name('account.settings');
    Route::put('account/password', [AccountSettingsController::class, 'updatePassword'])->name('account.password.update');
    Route::post('account/line/bind-code', [AccountSettingsController::class, 'createLineBindingCode'])->name('account.line.bind-code');
    Route::delete('account/line', [AccountSettingsController::class, 'disconnectLine'])->name('account.line.disconnect');
    Route::get('notifications', [NotificationInboxController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationInboxController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationInboxController::class, 'markAsRead'])->name('notifications.read');

    Route::get('approvals', function (Request $request) {
        $routes = ['student' => 'student.approvals.dashboard', 'advisor' => 'advisor.approvals.dashboard', 'officer' => 'officer.approvals.dashboard', 'admin' => 'admin.approvals.dashboard'];
        return redirect()->route($routes[$request->user()->role] ?? 'login');
    })->name('approvals.legacy');

    Route::get('cwie', function (Request $request) {
        $routes = ['student' => 'student.cwie.index', 'advisor' => 'advisor.approvals.dashboard', 'officer' => 'officer.approvals.dashboard', 'admin' => 'admin.approvals.dashboard'];
        return redirect()->route($routes[$request->user()->role] ?? 'login');
    })->name('cwie.legacy');

    Route::get('companies', function (Request $request) {
        abort_unless($request->user()->role === 'officer', 403);
        return redirect()->route('officer.companies.index');
    })->name('companies.legacy');
});

Route::get('cwie', function (Request $request) {
    if (!$request->user()) {
        return redirect()->route('login');
    }

    $routes = [
        'student' => 'student.cwie.index',
        'advisor' => 'advisor.approvals.dashboard',
        'officer' => 'officer.approvals.dashboard',
        'admin' => 'admin.approvals.dashboard',
    ];

    return redirect()->route($routes[$request->user()->role] ?? 'login');
})->name('cwie.index');

Route::middleware(['auth', 'role:officer,admin'])->group(function () {
    Route::resource('internship-applications', InternshipApplicationController::class);
    Route::get('internship-applications/{internship_application}/pdf', [InternshipApplicationController::class, 'pdf'])->name('internship-applications.pdf');
});
