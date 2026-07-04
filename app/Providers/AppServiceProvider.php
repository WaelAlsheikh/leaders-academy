<?php

namespace App\Providers;

use App\Models\About;
use App\Models\College;
use App\Models\Program;
use App\Models\StudentPlatform;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            static $shared = null;

            if ($shared !== null) {
                $view->with($shared);

                return;
            }

            try {
                $shared = [
                    'allPrograms' => Program::select('id', 'title', 'slug')->orderBy('id')->get(),
                    'allColleges' => College::select('id', 'title', 'slug')->orderBy('id')->get(),
                    'allStudentPlatforms' => StudentPlatform::orderBy('title')->get(),
                    'allTrainingPrograms' => TrainingProgram::select('id', 'title', 'slug', 'category')->orderBy('title')->get(),
                    'siteAbout' => About::query()
                        ->orderByDesc('updated_at')
                        ->orderByDesc('id')
                        ->first(),
                ];
            } catch (Throwable) {
                $shared = [
                    'allPrograms' => collect(),
                    'allColleges' => collect(),
                    'allStudentPlatforms' => collect(),
                    'allTrainingPrograms' => collect(),
                    'siteAbout' => null,
                ];
            }

            $view->with($shared);
        });
    }
}
