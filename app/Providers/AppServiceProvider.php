<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Program;
use App\Models\College;
use App\Models\StudentPlatform;
use App\Models\TrainingProgram;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // مشاركة البيانات في جميع الصفحات
        View::composer('*', function ($view) {

            // جميع البرامج الأكاديمية
            $view->with('allPrograms', Program::select('id', 'title', 'slug')->get());

            // جميع الكليات
            $view->with('allColleges', College::select('id', 'title', 'slug')->get());

            // جميع روابط منصة الطالب
            View::share('allStudentPlatforms', StudentPlatform::orderBy('title')->get());

            // ✅ جميع البرامج التدريبية — Training Programs
            $view->with('allTrainingPrograms', TrainingProgram::select('id','title','slug','category')->orderBy('title')->get());
        });
    }
}
