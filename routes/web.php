<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IdentityReviewController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PermitController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\TicketInboxController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Citizen\IdentityVerificationController;
use App\Http\Controllers\Citizen\NotificationController;
use App\Http\Controllers\Citizen\ProfileController;
use App\Http\Controllers\Citizen\ServiceRequestController;
use App\Http\Controllers\Citizen\TicketController;
use App\Http\Controllers\PermitVerificationController;
use App\Http\Controllers\TicketParticipantPhotoController;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('home');
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

Route::get('/tickets/{ticket}/participants/{participant}/photo', [TicketParticipantPhotoController::class, 'show'])
    ->middleware(['auth', 'role:citizen,admin'])
    ->name('tickets.participants.photo');

Route::get('/verify/permit/{serviceRequest}', [PermitVerificationController::class, 'show'])
    ->middleware('signed')
    ->name('permits.verify');

/*
|--------------------------------------------------------------------------
| Guest (login, register, password reset, Google)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| Email verification
|--------------------------------------------------------------------------
*/

Route::view('/email/verification/notice', 'auth.verify-email')
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify', [EmailVerificationController::class, 'show'])
    ->name('verification.otp.show');

Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('verification.otp.verify');

Route::post('/email/verification/resend', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:3,1')
    ->name('verification.otp.resend');

Route::post('/email/verification/cancel', [EmailVerificationController::class, 'cancel'])
    ->name('verification.cancel');

/*
|--------------------------------------------------------------------------
| Citizen
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:citizen'])
    ->prefix('citizen')
    ->name('citizen.')
    ->group(function () {
        Route::view('/dashboard', 'citizen.dashboard')->name('dashboard');

        Route::get('/services', function (Request $request) {
            $pendingServices = ServiceRequest::whereBelongsTo($request->user())
                ->where('status', ServiceRequest::PENDING)
                ->pluck('service_type')
                ->all();

            return view('citizen.services', compact('pendingServices'));
        })->name('services');

        // Documents, applications and service forms
        Route::controller(ServiceRequestController::class)->group(function () {
            Route::get('/documents', 'documents')->name('documents');

            Route::get('/applications', 'applications')->name('applications');
            Route::get('/applications/{serviceRequest}', 'showApplication')->name('applications.show');
            Route::get('/applications/{serviceRequest}/print', 'printApplication')->name('applications.print');
            Route::post('/applications/{serviceRequest}/cancel', 'cancelApplication')->name('applications.cancel');

            // Barangay Clearance
            Route::get('/barangay-clearance', 'clearance')->name('barangay-clearance');
            Route::post('/barangay-clearance/preview', 'previewClearance')->name('barangay-clearance.preview');
            Route::post('/barangay-clearance/modify', 'modifyClearance')->name('barangay-clearance.modify');
            Route::post('/barangay-clearance/proceed', 'proceedClearance')->name('barangay-clearance.proceed');

            // Certificate of Residency
            Route::get('/certificate-of-residency', 'residency')->name('certificate-of-residency');
            Route::post('/certificate-of-residency/preview', 'previewResidency')->name('certificate-of-residency.preview');
            Route::post('/certificate-of-residency/modify', 'modifyResidency')->name('certificate-of-residency.modify');
            Route::post('/certificate-of-residency/proceed', 'proceedResidency')->name('certificate-of-residency.proceed');

            // Certificate of Indigency
            Route::get('/certificate-of-indigency', 'indigency')->name('certificate-of-indigency');
            Route::post('/certificate-of-indigency/preview', 'previewIndigency')->name('certificate-of-indigency.preview');
            Route::post('/certificate-of-indigency/modify', 'modifyIndigency')->name('certificate-of-indigency.modify');
            Route::post('/certificate-of-indigency/proceed', 'proceedIndigency')->name('certificate-of-indigency.proceed');
        });

        // Tickets (messages about a request)
        Route::get('/applications/{serviceRequest}/ticket', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/applications/{serviceRequest}/ticket', [TicketController::class, 'reply'])
            ->middleware('throttle:20,1')
            ->name('tickets.reply');

        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::get('/profile/photo', [ProfileController::class, 'showPhoto'])->name('profile.photo.show');

        // Digital identity verification
        Route::controller(IdentityVerificationController::class)->group(function () {
            Route::get('/identity-verification', 'show')->name('identity-verification');
            Route::post('/identity-verification/details', 'storeDetails')->name('identity-verification.details');
            Route::post('/identity-verification/document', 'storeDocument')
                ->middleware('throttle:10,1')
                ->name('identity-verification.document');
            Route::get('/identity-verification/document/{verification}', 'document')->name('identity-verification.file');
        });

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

        // Only identity-verified citizens can edit their profile
        Route::middleware('identity.verified')->group(function () {
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::post('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
        });
    });

/*
|--------------------------------------------------------------------------
| Staff
|--------------------------------------------------------------------------
*/

Route::view('/staff/dashboard', 'staff.dashboard')
    ->middleware(['auth', 'role:staff'])
    ->name('staff.dashboard');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/photo', [AdminProfileController::class, 'showPhoto'])->name('profile.photo.show');
        Route::post('/profile/photo', [AdminProfileController::class, 'photo'])->name('profile.photo');

        // Users and roles
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Permits and clearances
        Route::controller(PermitController::class)->group(function () {
            Route::get('/permits', 'index')->name('permits.index');
            Route::get('/permits/{serviceRequest}', 'show')->name('permits.show');
            Route::post('/permits/{serviceRequest}/approve', 'approve')->name('permits.approve');
            Route::post('/permits/{serviceRequest}/reject', 'reject')->name('permits.reject');
            Route::patch('/permits/{serviceRequest}/print-limit', 'updatePrintLimit')->name('permits.print-limit.update');
        });

        // Citizen tickets (messages)
        Route::get('/tickets', [TicketInboxController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [TicketInboxController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}', [TicketInboxController::class, 'reply'])
            ->middleware('throttle:30,1')
            ->name('tickets.reply');

        Route::post('/notifications/read-all', [AdminNotificationController::class, 'readAll'])
            ->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [AdminNotificationController::class, 'read'])
            ->name('notifications.read');

        // Simple pages
        Route::view('/document-authentication', 'admin.document-authentication')->name('document-authentication.index');
        Route::view('/blockchain-ledger', 'admin.blockchain-ledger')->name('blockchain-ledger.index');
        Route::view('/notifications', 'admin.notifications')->name('notifications.index');
        Route::view('/reports', 'admin.reports')->name('reports.index');
        Route::view('/audit-logs', 'admin.audit-logs')->name('audit-logs.index');
    });

/*
|--------------------------------------------------------------------------
| Identity verification review (admin and staff)
|--------------------------------------------------------------------------
*/

foreach (['admin', 'staff'] as $role) {
    Route::middleware(['auth', 'role:'.$role])
        ->prefix($role.'/identity-verification')
        ->name($role.'.identity-verification.')
        ->controller(IdentityReviewController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{verification}', 'show')->name('show');
            Route::get('/{verification}/document', 'document')->name('document');
            Route::get('/{verification}/selfie', 'selfie')->name('selfie');
            Route::post('/{verification}/approve', 'approve')->name('approve');
            Route::post('/{verification}/reject', 'reject')->name('reject');
        });
}

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
