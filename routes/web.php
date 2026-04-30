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
use App\Http\Controllers\Admin\EmployeeAdminController;
use App\Http\Controllers\Admin\CollegeSubjectController;
use App\Http\Controllers\Admin\EnrollmentCycleController;
use App\Http\Controllers\Admin\RegistrationSeasonController;
use App\Http\Controllers\Admin\RegistrableController;
use App\Http\Controllers\Admin\RegistrableRegistrationController;
use App\Http\Controllers\Admin\SemesterSectionController;
use App\Http\Controllers\Admin\StudyStructureController;
use App\Http\Controllers\Doctor\AuthController as DoctorAuthController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\LiveSessionController as DoctorLiveSessionController;
use App\Http\Controllers\Doctor\MaterialController as DoctorMaterialController;
use App\Http\Controllers\Doctor\SectionController as DoctorSectionController;
use App\Http\Controllers\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;

// Student
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\StudentRegistrationController;
use App\Http\Controllers\Student\InvoiceController;
use App\Http\Controllers\Student\LiveSessionController as StudentLiveSessionController;
use App\Http\Controllers\Student\MaterialController as StudentMaterialController;
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
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');
Route::get('/programs/{program}/{branch}', [ProgramController::class, 'showBranch'])->name('programs.branches.show');

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

        Route::post('/colleges-management',
            [CollegeSubjectController::class, 'storeCollege']
        )->name('admin.colleges.store');

        Route::put('/colleges-management/{college}',
            [CollegeSubjectController::class, 'updateCollege']
        )->name('admin.colleges.update');

        Route::delete('/colleges-management/{college}',
            [CollegeSubjectController::class, 'destroyCollege']
        )->name('admin.colleges.destroy');

        Route::get('/colleges/{college}/subjects',
            [CollegeSubjectController::class, 'subjects']
        )->name('admin.colleges.subjects');
        Route::get('/colleges/{college}/years', [StudyStructureController::class, 'collegeYears'])
            ->name('admin.colleges.years');

        Route::post('/registrables/{entity}/years', [StudyStructureController::class, 'storeYear'])
            ->name('admin.study_years.store');
        Route::put('/study-years/{studyYear}', [StudyStructureController::class, 'updateYear'])
            ->name('admin.study_years.update');
        Route::delete('/study-years/{studyYear}', [StudyStructureController::class, 'destroyYear'])
            ->name('admin.study_years.destroy');
        Route::get('/registrables/{entity}/years', [StudyStructureController::class, 'registrableYears'])
            ->name('admin.registrables.years');
        Route::get('/study-years/{studyYear}/terms', [StudyStructureController::class, 'terms'])
            ->name('admin.study_years.terms');
        Route::post('/study-years/{studyYear}/terms', [StudyStructureController::class, 'storeTerm'])
            ->name('admin.study_terms.store');
        Route::put('/study-terms/{studyTerm}', [StudyStructureController::class, 'updateTerm'])
            ->name('admin.study_terms.update');
        Route::delete('/study-terms/{studyTerm}', [StudyStructureController::class, 'destroyTerm'])
            ->name('admin.study_terms.destroy');
        Route::get('/study-terms/{studyTerm}/subjects', [StudyStructureController::class, 'subjects'])
            ->name('admin.study_terms.subjects');

        Route::post('/colleges/{college}/subjects',
            [CollegeSubjectController::class, 'store']
        )->name('admin.subjects.store');

        Route::put('/subjects/{subject}',
            [CollegeSubjectController::class, 'update']
        )->name('admin.subjects.update');

        Route::delete('/subjects/{subject}',
            [CollegeSubjectController::class, 'destroy']
        )->name('admin.subjects.destroy');

        Route::get('/registrables', [RegistrableController::class, 'index'])
            ->name('admin.registrables.index');
        Route::get('/program-branches-management', [RegistrableController::class, 'programBranches'])
            ->name('admin.program_branches.index');
        Route::get('/training-program-branches-management', [RegistrableController::class, 'trainingProgramBranches'])
            ->name('admin.training_program_branches.index');
        Route::put('/registrables/{entity}', [RegistrableController::class, 'updatePrice'])
            ->name('admin.registrables.update');
        Route::get('/registrables/{entity}/subjects', [RegistrableController::class, 'subjects'])
            ->name('admin.registrables.subjects');
        Route::post('/registrables/{entity}/subjects', [RegistrableController::class, 'storeSubject'])
            ->name('admin.registrables.subjects.store');
        Route::put('/registrable-subjects/{subject}', [RegistrableController::class, 'updateSubject'])
            ->name('admin.registrable_subjects.update');
        Route::delete('/registrable-subjects/{subject}', [RegistrableController::class, 'destroySubject'])
            ->name('admin.registrable_subjects.destroy');

        // Enrollment Cycles
        Route::get('/enrollment-cycles', [RegistrationSeasonController::class, 'index'])
            ->name('admin.enrollment_cycles.index');
        Route::get('/registration-seasons/{season}', [RegistrationSeasonController::class, 'show'])
            ->name('admin.registration_seasons.show');
        Route::post('/registration-seasons/{season}/archive', [RegistrationSeasonController::class, 'archive'])
            ->name('admin.registration_seasons.archive');
        Route::put('/registration-seasons/{season}', [RegistrationSeasonController::class, 'update'])
            ->name('admin.registration_seasons.update');
        Route::put('/registration-seasons/{season}/entities/{entity}', [RegistrationSeasonController::class, 'toggleEntity'])
            ->name('admin.registration_seasons.entities.toggle');
        Route::get('/archived-enrollment-cycles', [RegistrationSeasonController::class, 'archivedIndex'])
            ->name('admin.archived_enrollment_cycles.index');
        Route::post('/enrollment-cycles', [RegistrationSeasonController::class, 'store'])
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
        Route::post('/enrollment-cycles/{cycle}/registrations/{registration}/results',
            [EnrollmentCycleController::class, 'updateResultStatuses']
        )->name('admin.enrollment_cycles.registrations.results');
        Route::post('/enrollment-cycles/{cycle}/registrations/bulk-status',
            [EnrollmentCycleController::class, 'bulkUpdateRegistrationStatus']
        )->name('admin.enrollment_cycles.registrations.bulk_status');
        Route::get('/archived-enrollment-cycles/{season}', [RegistrationSeasonController::class, 'archivedShow'])
            ->name('admin.archived_enrollment_cycles.show');
        Route::post('/archived-enrollment-cycles/{season}/restore', [RegistrationSeasonController::class, 'restore'])
            ->name('admin.archived_enrollment_cycles.restore');
        Route::delete('/archived-enrollment-cycles/{season}', [RegistrationSeasonController::class, 'destroyArchived'])
            ->name('admin.archived_enrollment_cycles.destroy');
        Route::get('/registrables/{entity}/registrations', [RegistrableRegistrationController::class, 'index'])
            ->name('admin.registrables.registrations.index');

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
        Route::post('/sections/{section}/students', [SemesterSectionController::class, 'attachStudent'])
            ->name('admin.sections.students.attach');
        Route::delete('/sections/{section}/students/{student}', [SemesterSectionController::class, 'detachStudent'])
            ->name('admin.sections.students.detach');
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

        Route::put('/doctors/{doctor}', [DoctorAdminController::class, 'update'])
            ->name('admin.doctors.update');

        Route::post('/doctors/{doctor}/toggle', [DoctorAdminController::class, 'toggle'])
            ->name('admin.doctors.toggle');

        Route::post('/doctors/{doctor}/reset-password', [DoctorAdminController::class, 'resetPassword'])
            ->name('admin.doctors.reset_password');

        Route::get('/employees', [EmployeeAdminController::class, 'index'])
            ->name('admin.employees.index');

        Route::post('/employees', [EmployeeAdminController::class, 'store'])
            ->name('admin.employees.store');

        Route::put('/employees/{employee}', [EmployeeAdminController::class, 'update'])
            ->name('admin.employees.update');

        Route::post('/employees/{employee}/toggle', [EmployeeAdminController::class, 'toggle'])
            ->name('admin.employees.toggle');

        Route::post('/employees/{employee}/reset-password', [EmployeeAdminController::class, 'resetPassword'])
            ->name('admin.employees.reset_password');
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

Route::get('/training-programs/{trainingProgram}/{branch}', [TrainingProgramController::class, 'showBranch'])
    ->name('training.branches.show');

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
    Route::get('register/verify-email', [StudentAuthController::class, 'showVerifyRegistration'])->name('register.verify');
    Route::post('register/verify-email', [StudentAuthController::class, 'verifyRegistration'])->name('register.verify.submit');
    Route::post('register/resend-code', [StudentAuthController::class, 'resendRegistrationCode'])->name('register.resend_code');
    Route::post('register/cancel', [StudentAuthController::class, 'cancelRegistration'])->name('register.cancel');

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
        Route::get('materials', [StudentMaterialController::class, 'index'])
            ->name('materials.index');
        Route::get('materials/{material}', [StudentMaterialController::class, 'download'])
            ->name('materials.download');

        Route::get('invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::get('schedule', [ScheduleController::class, 'index'])
            ->name('schedule.index');

        Route::get('live-sessions/{liveSession}', [StudentLiveSessionController::class, 'show'])
            ->name('live_sessions.show');
        Route::post('live-sessions/{liveSession}/heartbeat', [StudentLiveSessionController::class, 'heartbeat'])
            ->name('live_sessions.heartbeat');
        Route::get('live-sessions/{liveSession}/comments', [StudentLiveSessionController::class, 'comments'])
            ->name('live_sessions.comments');
        Route::post('live-sessions/{liveSession}/comments', [StudentLiveSessionController::class, 'storeComment'])
            ->name('live_sessions.comments.store');
    });
});

/*
|--------------------------------------------------------------------------
| Doctor Auth + Portal
|--------------------------------------------------------------------------
*/
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::middleware('guest:doctor')->group(function () {
        Route::get('login', [DoctorAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [DoctorAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:doctor')->group(function () {
        Route::post('logout', [DoctorAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
        Route::get('materials', [DoctorMaterialController::class, 'index'])->name('materials.index');
        Route::post('materials/videos', [DoctorMaterialController::class, 'storeVideo'])->name('materials.videos.store');
        Route::post('materials/files', [DoctorMaterialController::class, 'storeFile'])->name('materials.files.store');
        Route::put('materials/{material}', [DoctorMaterialController::class, 'update'])->name('materials.update');
        Route::delete('materials/{material}', [DoctorMaterialController::class, 'destroy'])->name('materials.destroy');
        Route::get('materials/{material}/download', [DoctorMaterialController::class, 'download'])->name('materials.download');
        Route::get('sections/{section}', [DoctorSectionController::class, 'show'])->name('sections.show');
        Route::put('sections/{section}/next-link', [DoctorSectionController::class, 'updateNextLink'])->name('sections.next_link');
        Route::post('meetings/{meeting}/start', [DoctorLiveSessionController::class, 'start'])->name('meetings.start');
        Route::get('live-sessions/{liveSession}', [DoctorLiveSessionController::class, 'show'])->name('live_sessions.show');
        Route::post('live-sessions/{liveSession}/close-entry', [DoctorLiveSessionController::class, 'closeEntry'])->name('live_sessions.close_entry');
        Route::post('live-sessions/{liveSession}/reopen-entry', [DoctorLiveSessionController::class, 'reopenEntry'])->name('live_sessions.reopen_entry');
        Route::post('live-sessions/{liveSession}/end', [DoctorLiveSessionController::class, 'end'])->name('live_sessions.end');
        Route::get('live-sessions/{liveSession}/attendance', [DoctorLiveSessionController::class, 'attendance'])->name('live_sessions.attendance');
        Route::get('live-sessions/{liveSession}/comments', [DoctorLiveSessionController::class, 'comments'])->name('live_sessions.comments');
        Route::post('live-sessions/{liveSession}/comments', [DoctorLiveSessionController::class, 'storeComment'])->name('live_sessions.comments.store');
        Route::post('live-sessions/{liveSession}/comments/{comment}/hide', [DoctorLiveSessionController::class, 'hideComment'])->name('live_sessions.comments.hide');
        Route::post('live-sessions/{liveSession}/comment-blocks', [DoctorLiveSessionController::class, 'updateCommentBlocks'])->name('live_sessions.comment_blocks');
        Route::post('live-sessions/{liveSession}/host-presence', [DoctorLiveSessionController::class, 'updateHostPresence'])->name('live_sessions.host_presence');
        Route::post('live-sessions/{liveSession}/moderation', [DoctorLiveSessionController::class, 'updateModeration'])->name('live_sessions.moderation');
    });
});

/*
|--------------------------------------------------------------------------
| Employee Auth + Portal
|--------------------------------------------------------------------------
*/
Route::prefix('employee')->name('employee.')->group(function () {
    Route::middleware('guest:employee')->group(function () {
        Route::get('login', [EmployeeAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [EmployeeAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:employee')->group(function () {
        Route::post('logout', [EmployeeAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

        Route::get('enrollment-cycles', [RegistrationSeasonController::class, 'index'])
            ->name('enrollment_cycles.index');
        Route::get('registration-seasons/{season}', [RegistrationSeasonController::class, 'show'])
            ->name('registration_seasons.show');
        Route::post('registration-seasons/{season}/archive', [RegistrationSeasonController::class, 'archive'])
            ->name('registration_seasons.archive');
        Route::put('registration-seasons/{season}', [RegistrationSeasonController::class, 'update'])
            ->name('registration_seasons.update');
        Route::put('registration-seasons/{season}/entities/{entity}', [RegistrationSeasonController::class, 'toggleEntity'])
            ->name('registration_seasons.entities.toggle');
        Route::get('archived-enrollment-cycles', [RegistrationSeasonController::class, 'archivedIndex'])
            ->name('archived_enrollment_cycles.index');
        Route::post('enrollment-cycles', [RegistrationSeasonController::class, 'store'])
            ->name('enrollment_cycles.store');
        Route::get('enrollment-cycles/{cycle}', [EnrollmentCycleController::class, 'show'])
            ->name('enrollment_cycles.show');
        Route::put('enrollment-cycles/{cycle}', [EnrollmentCycleController::class, 'update'])
            ->name('enrollment_cycles.update');
        Route::post('enrollment-cycles/{cycle}/subjects', [EnrollmentCycleController::class, 'updateSubjects'])
            ->name('enrollment_cycles.subjects');
        Route::post('enrollment-cycles/{cycle}/open', [EnrollmentCycleController::class, 'open'])
            ->name('enrollment_cycles.open');
        Route::post('enrollment-cycles/{cycle}/close', [EnrollmentCycleController::class, 'close'])
            ->name('enrollment_cycles.close');
        Route::post('enrollment-cycles/{cycle}/approve', [EnrollmentCycleController::class, 'approve'])
            ->name('enrollment_cycles.approve');
        Route::post('enrollment-cycles/{cycle}/start-semester', [EnrollmentCycleController::class, 'startSemester'])
            ->name('enrollment_cycles.start_semester');
        Route::post('enrollment-cycles/{cycle}/registrations/{registration}/status', [EnrollmentCycleController::class, 'updateRegistrationStatus'])
            ->name('enrollment_cycles.registrations.status');
        Route::post('enrollment-cycles/{cycle}/registrations/{registration}/results', [EnrollmentCycleController::class, 'updateResultStatuses'])
            ->name('enrollment_cycles.registrations.results');
        Route::post('enrollment-cycles/{cycle}/registrations/bulk-status', [EnrollmentCycleController::class, 'bulkUpdateRegistrationStatus'])
            ->name('enrollment_cycles.registrations.bulk_status');
        Route::get('archived-enrollment-cycles/{season}', [RegistrationSeasonController::class, 'archivedShow'])
            ->name('archived_enrollment_cycles.show');
        Route::post('archived-enrollment-cycles/{season}/restore', [RegistrationSeasonController::class, 'restore'])
            ->name('archived_enrollment_cycles.restore');
        Route::delete('archived-enrollment-cycles/{season}', [RegistrationSeasonController::class, 'destroyArchived'])
            ->name('archived_enrollment_cycles.destroy');
        Route::get('registrables/{entity}/registrations', [RegistrableRegistrationController::class, 'index'])
            ->name('registrables.registrations.index');

        Route::get('semesters/{semester}/sections', [SemesterSectionController::class, 'index'])
            ->name('semesters.sections.index');
        Route::post('semesters/{semester}/sections', [SemesterSectionController::class, 'store'])
            ->name('semesters.sections.store');
        Route::put('sections/{section}', [SemesterSectionController::class, 'update'])
            ->name('sections.update');
        Route::delete('sections/{section}', [SemesterSectionController::class, 'destroy'])
            ->name('sections.destroy');
        Route::get('sections/{section}/meetings', [SemesterSectionController::class, 'meetings'])
            ->name('sections.meetings.index');
        Route::post('sections/{section}/meetings', [SemesterSectionController::class, 'storeMeeting'])
            ->name('sections.meetings.store');
        Route::post('sections/{section}/students', [SemesterSectionController::class, 'attachStudent'])
            ->name('sections.students.attach');
        Route::delete('sections/{section}/students/{student}', [SemesterSectionController::class, 'detachStudent'])
            ->name('sections.students.detach');
        Route::put('meetings/{meeting}', [SemesterSectionController::class, 'updateMeeting'])
            ->name('meetings.update');
        Route::delete('meetings/{meeting}', [SemesterSectionController::class, 'destroyMeeting'])
            ->name('meetings.destroy');

        Route::get('colleges', [CollegeSubjectController::class, 'colleges'])
            ->name('colleges.index');
        Route::post('colleges', [CollegeSubjectController::class, 'storeCollege'])
            ->name('colleges.store');
        Route::put('colleges/{college}', [CollegeSubjectController::class, 'updateCollege'])
            ->name('colleges.update');
        Route::delete('colleges/{college}', [CollegeSubjectController::class, 'destroyCollege'])
            ->name('colleges.destroy');
        Route::get('colleges/{college}/subjects', [CollegeSubjectController::class, 'subjects'])
            ->name('colleges.subjects');
        Route::get('colleges/{college}/years', [StudyStructureController::class, 'collegeYears'])
            ->name('colleges.years');
        Route::post('registrables/{entity}/years', [StudyStructureController::class, 'storeYear'])
            ->name('study_years.store');
        Route::put('study-years/{studyYear}', [StudyStructureController::class, 'updateYear'])
            ->name('study_years.update');
        Route::delete('study-years/{studyYear}', [StudyStructureController::class, 'destroyYear'])
            ->name('study_years.destroy');
        Route::get('registrables/{entity}/years', [StudyStructureController::class, 'registrableYears'])
            ->name('registrables.years');
        Route::get('study-years/{studyYear}/terms', [StudyStructureController::class, 'terms'])
            ->name('study_years.terms');
        Route::post('study-years/{studyYear}/terms', [StudyStructureController::class, 'storeTerm'])
            ->name('study_terms.store');
        Route::put('study-terms/{studyTerm}', [StudyStructureController::class, 'updateTerm'])
            ->name('study_terms.update');
        Route::delete('study-terms/{studyTerm}', [StudyStructureController::class, 'destroyTerm'])
            ->name('study_terms.destroy');
        Route::get('study-terms/{studyTerm}/subjects', [StudyStructureController::class, 'subjects'])
            ->name('study_terms.subjects');
        Route::post('colleges/{college}/subjects', [CollegeSubjectController::class, 'store'])
            ->name('subjects.store');
        Route::put('subjects/{subject}', [CollegeSubjectController::class, 'update'])
            ->name('subjects.update');
        Route::delete('subjects/{subject}', [CollegeSubjectController::class, 'destroy'])
            ->name('subjects.destroy');
        Route::get('program-branches', [RegistrableController::class, 'programBranches'])
            ->name('program_branches.index');
        Route::get('training-program-branches', [RegistrableController::class, 'trainingProgramBranches'])
            ->name('training_program_branches.index');
        Route::put('registrables/{entity}', [RegistrableController::class, 'updatePrice'])
            ->name('registrables.update');
        Route::post('registrables/{entity}/subjects', [RegistrableController::class, 'storeSubject'])
            ->name('registrables.subjects.store');
        Route::put('registrable-subjects/{subject}', [RegistrableController::class, 'updateSubject'])
            ->name('registrable_subjects.update');
        Route::delete('registrable-subjects/{subject}', [RegistrableController::class, 'destroySubject'])
            ->name('registrable_subjects.destroy');
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
