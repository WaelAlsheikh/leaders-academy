<?php

namespace App\Http\Controllers;

use App\Models\TrainingProgram;
use App\Models\TrainingProgramBranch;

class TrainingProgramController extends Controller
{
    // قائمة البرامج التدريبية
    public function index()
    {
        $programs = TrainingProgram::orderBy('title')->get();
        return view('training.index', compact('programs'));
    }

    // عرض برنامج تفصيلي (route-model binding عبر slug)
    public function show(TrainingProgram $trainingProgram)
    {
        $branches = $trainingProgram->branches()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('training.show', [
            'program' => $trainingProgram,
            'branches' => $branches,
        ]);
    }

    public function showBranch(TrainingProgram $trainingProgram, TrainingProgramBranch $branch)
    {
        if ((int) $branch->training_program_id !== (int) $trainingProgram->id) {
            abort(404);
        }

        return view('training.branch-show', [
            'program' => $trainingProgram,
            'branch' => $branch,
        ]);
    }
}
