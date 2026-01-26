<?php

use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AccreditationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentPlatformController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\ApplicationController;

// Admin
use App\Http\Controllers\Admin\StudentAdminController;
use App\Http\Controllers\Admin\DoctorAdminController;

// Student
use App\Http\Controllers\Student\AuthController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\StudentRegistrationController;

/*
|--------------------------------------------------------------------------
| Language switch
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
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/virtual_university/virtual-university', [HomeController::class, 'virtualUniversity'])->name('virtual');

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
| CMS pages (Voyager)
|--------------------------------------------------------------------------
*/
Route::get('/page/{slug}', [PageController::class, 'show'])
    ->name('page.show');

/*
|--------------------------------------------------------------------------
| Admin (Voyager Core)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Voyager::routes();
});

/*
|--------------------------------------------------------------------------
| Admin - Custom Management
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
    });

Route::prefix('admin')
    ->middleware(['web', 'auth'])
    ->group(function () {

        // Doctors
        Route::get('/doctors', [DoctorAdminController::class, 'index'])
            ->name('admin.doctors.index');

        Route::post('/doctors', [DoctorAdminController::class, 'store'])
            ->name('admin.doctors.store');

        Route::post('/doctors/{doctor}/toggle', [DoctorAdminController::class, 'toggle'])
            ->name('admin.doctors.toggle');
    });

/*
|--------------------------------------------------------------------------
| Colleges
|--------------------------------------------------------------------------
*/
Route::get('/colleges', [CollegeController::class, 'index'])
    ->name('colleges.index');

Route::get('/colleges/{slug}', [CollegeController::class, 'show'])
    ->name('colleges.show');

/*
|--------------------------------------------------------------------------
| Student Platform (Public)
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
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.submit');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected
    Route::middleware('auth:student')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // 🔥 NEW: Registration system
        Route::get('registration', [StudentRegistrationController::class, 'create'])
            ->name('registration.create');

        Route::post('registration', [StudentRegistrationController::class, 'store'])
            ->name('registration.store');

        Route::get('/registrations', [StudentRegistrationController::class, 'index'])
            ->name('registrations.index');
    });
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');