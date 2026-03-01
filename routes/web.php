<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

// Public
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AccreditationController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentPlatformController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\ApplicationController;

// Admin
use App\Http\Controllers\Admin\StudentAdminController;
use App\Http\Controllers\Admin\DoctorAdminController;
use App\Http\Controllers\Admin\CollegeSubjectController;
use App\Http\Controllers\Admin\EnrollmentCycleController;
use App\Http\Controllers\Admin\SemesterSectionController;

// Student
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\StudentRegistrationController;
use App\Http\Controllers\Student\InvoiceController;
use App\Http\Controllers\Student\ScheduleController;

// Breeze
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollegeScheduleController;

/*
|--------------------------------------------------------------------------
| Language Switch
|--------------------------------------------------------------------------
*/
Route::get('/lang/{locale}', function ($locale) {
    if (!in_array($locale, ['ar', 'en'])) {
        $locale = 'ar';
    }

    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/virtual_university/virtual-university', [HomeController::class, 'virtualUniversity'])
    ->name('virtual');

/*
|--------------------------------------------------------------------------
| Programs
|--------------------------------------------------------------------------
*/
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [ProgramController::class, 'show'])->name('programs.show');

/*
|--------------------------------------------------------------------------
| Accreditations
|--------------------------------------------------------------------------
*/
Route::get('/accreditations', [AccreditationController::class, 'index'])
    ->name('accreditations.index');

/*
|--------------------------------------------------------------------------
| CMS Pages (Voyager)
|--------------------------------------------------------------------------
*/
Route::get('/page/{slug}', [PageController::class, 'show'])
    ->name('page.show');

/*
|--------------------------------------------------------------------------
| Voyager Admin (⚠️ يجب أن يبقى وحده)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Voyager::routes();
});

/*
|--------------------------------------------------------------------------
| Admin – Custom Management (Voyager users فقط)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['web', 'admin.user'])
    ->group(function () {

        // Students
        Route::get('/students/management', [StudentAdminController::class, 'index'])
            ->name('admin.students.management');

        Route::post('/students/{student}/toggle', [StudentAdminController::class, 'toggle'])
            ->name('admin.students.toggle');

        // Colleges & Subjects (الربط الصحيح)
        Route::get('/colleges-management',
            [CollegeSubjectController::class, 'colleges']
        )->name('admin.colleges.index');

        Route::get('/colleges/{college}/subjects',
            [CollegeSubjectController::class, 'subjects']
        )->name('admin.colleges.subjects');

        Route::post('/colleges/{college}/subjects',
            [CollegeSubjectController::class, 'store']
        )->name('admin.subjects.store');

        Route::put('/subjects/{subject}',
            [CollegeSubjectController::class, 'update']
        )->name('admin.subjects.update');

        Route::delete('/subjects/{subject}',
            [CollegeSubjectController::class, 'destroy']
        )->name('admin.subjects.destroy');

        // Enrollment Cycles
        Route::get('/enrollment-cycles', [EnrollmentCycleController::class, 'index'])
            ->name('admin.enrollment_cycles.index');
        Route::post('/enrollment-cycles', [EnrollmentCycleController::class, 'store'])
            ->name('admin.enrollment_cycles.store');
        Route::get('/enrollment-cycles/{cycle}', [EnrollmentCycleController::class, 'show'])
            ->name('admin.enrollment_cycles.show');
        Route::put('/enrollment-cycles/{cycle}', [EnrollmentCycleController::class, 'update'])
            ->name('admin.enrollment_cycles.update');
        Route::post('/enrollment-cycles/{cycle}/subjects', [EnrollmentCycleController::class, 'updateSubjects'])
            ->name('admin.enrollment_cycles.subjects');
        Route::post('/enrollment-cycles/{cycle}/open', [EnrollmentCycleController::class, 'open'])
            ->name('admin.enrollment_cycles.open');
        Route::post('/enrollment-cycles/{cycle}/close', [EnrollmentCycleController::class, 'close'])
            ->name('admin.enrollment_cycles.close');
        Route::post('/enrollment-cycles/{cycle}/approve', [EnrollmentCycleController::class, 'approve'])
            ->name('admin.enrollment_cycles.approve');
        Route::post('/enrollment-cycles/{cycle}/start-semester', [EnrollmentCycleController::class, 'startSemester'])
            ->name('admin.enrollment_cycles.start_semester');
        Route::post('/enrollment-cycles/{cycle}/registrations/{registration}/status',
            [EnrollmentCycleController::class, 'updateRegistrationStatus']
        )->name('admin.enrollment_cycles.registrations.status');
        Route::post('/enrollment-cycles/{cycle}/registrations/bulk-status',
            [EnrollmentCycleController::class, 'bulkUpdateRegistrationStatus']
        )->name('admin.enrollment_cycles.registrations.bulk_status');

        Route::get('/semesters/{semester}/sections', [SemesterSectionController::class, 'index'])
            ->name('admin.semesters.sections.index');
        Route::post('/semesters/{semester}/sections', [SemesterSectionController::class, 'store'])
            ->name('admin.semesters.sections.store');
        Route::put('/sections/{section}', [SemesterSectionController::class, 'update'])
            ->name('admin.sections.update');
        Route::delete('/sections/{section}', [SemesterSectionController::class, 'destroy'])
            ->name('admin.sections.destroy');
        Route::get('/sections/{section}/meetings', [SemesterSectionController::class, 'meetings'])
            ->name('admin.sections.meetings.index');
        Route::post('/sections/{section}/meetings', [SemesterSectionController::class, 'storeMeeting'])
            ->name('admin.sections.meetings.store');
        Route::put('/meetings/{meeting}', [SemesterSectionController::class, 'updateMeeting'])
            ->name('admin.meetings.update');
        Route::delete('/meetings/{meeting}', [SemesterSectionController::class, 'destroyMeeting'])
            ->name('admin.meetings.destroy');
    });

/*
|--------------------------------------------------------------------------
| Admin – Doctors (Breeze Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['web', 'auth'])
    ->group(function () {

        Route::get('/doctors', [DoctorAdminController::class, 'index'])
            ->name('admin.doctors.index');

        Route::post('/doctors', [DoctorAdminController::class, 'store'])
            ->name('admin.doctors.store');

        Route::post('/doctors/{doctor}/toggle', [DoctorAdminController::class, 'toggle'])
            ->name('admin.doctors.toggle');
    });

/*
|--------------------------------------------------------------------------
| Colleges (Public)
|--------------------------------------------------------------------------
*/
Route::get('/colleges/{college}/schedule', [CollegeScheduleController::class, 'show'])
    ->name('colleges.schedule');
Route::get('/colleges', [CollegeController::class, 'index'])->name('colleges.index');
Route::get('/colleges/{slug}', [CollegeController::class, 'show'])->name('colleges.show');

/*
|--------------------------------------------------------------------------
| Student Platform
|--------------------------------------------------------------------------
*/
Route::get('/student-platform', [StudentPlatformController::class, 'index'])
    ->name('student-platform.index');

Route::get('/student-platform/{slug}', [StudentPlatformController::class, 'show'])
    ->name('student-platform.show');

/*
|--------------------------------------------------------------------------
| Training Programs
|--------------------------------------------------------------------------
*/
Route::get('/training-programs', [TrainingProgramController::class, 'index'])
    ->name('training.index');

Route::get('/training-programs/{trainingProgram}', [TrainingProgramController::class, 'show'])
    ->name('training.show');

/*
|--------------------------------------------------------------------------
| Applications
|--------------------------------------------------------------------------
*/
Route::get('/apply/{type}/{slug}', [ApplicationController::class, 'create'])
    ->where('type', 'program|training')
    ->name('applications.create');

Route::post('/apply', [ApplicationController::class, 'store'])
    ->name('applications.store');

/*
|--------------------------------------------------------------------------
| Student Auth + Dashboard + Registration
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->group(function () {

    // Auth
    Route::get('login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [StudentAuthController::class, 'login'])->name('login.submit');

    Route::get('register', [StudentAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [StudentAuthController::class, 'register'])->name('register.submit');

    Route::post('logout', [StudentAuthController::class, 'logout'])->name('logout');

    // Protected
    Route::middleware('auth:student')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('registration', [StudentRegistrationController::class, 'create'])
            ->name('registration.create');

        Route::post('registration', [StudentRegistrationController::class, 'store'])
            ->name('registration.store');

        Route::get('registrations', [StudentRegistrationController::class, 'index'])
            ->name('registrations.index');

        Route::get('invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::get('schedule', [ScheduleController::class, 'index'])
            ->name('schedule.index');
    });
});

/*
|--------------------------------------------------------------------------
| Breeze – Admin Profile Only
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Breeze Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
