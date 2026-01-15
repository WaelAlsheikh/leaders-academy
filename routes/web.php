<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AccreditationController;
use App\Http\Controllers\GalleryController;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\StudentPlatformController;
use App\Http\Controllers\TrainingProgramController;
use App\Http\Controllers\ApplicationController;

use App\Http\Controllers\Student\AuthController;
use App\Http\Controllers\Student\DashboardController;

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
Route::get('/programs', [\App\Http\Controllers\ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{slug}', [\App\Http\Controllers\ProgramController::class, 'show'])->name('programs.show');


/*
|--------------------------------------------------------------------------
| Accreditations & Gallery
|--------------------------------------------------------------------------
*/
Route::get('/accreditations', [AccreditationController::class, 'index'])->name('accreditations.index');

/*
|--------------------------------------------------------------------------
| CMS pages (Voyager)
|--------------------------------------------------------------------------
*/
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

/*
|--------------------------------------------------------------------------
| Admin (Voyager)
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

// قائمة الكليات (صفحة عامة)
Route::get('/colleges', [CollegeController::class, 'index'])->name('colleges.index');

// صفحة تفاصيل كلية (باستخدام slug)
Route::get('/colleges/{slug}', [CollegeController::class, 'show'])->name('colleges.show');

Route::get('/student-platform', [StudentPlatformController::class, 'index'])->name('student-platform.index');
Route::get('/student-platform/{slug}', [StudentPlatformController::class, 'show'])->name('student-platform.show');

// قائمة البرامج التدريبية
Route::get('/training-programs', [TrainingProgramController::class, 'index'])->name('training.index');

// صفحة تفاصيل برنامج تدريبي (نستخدم slug)
Route::get('/training-programs/{trainingProgram}', [TrainingProgramController::class, 'show'])->name('training.show');

// صفحة عرض فورم التسجيل لبرنامج (GET)
Route::get('/apply/{type}/{slug}', [ApplicationController::class, 'create'])
    ->where('type', 'program|training')
    ->name('applications.create');

// إرسال بيانات الفورم (POST)
Route::post('/apply', [ApplicationController::class, 'store'])->name('applications.store');

Route::prefix('student')->name('student.')->group(function () {

    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.submit');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:student')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});







