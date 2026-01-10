<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingProgram;

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
        // $trainingProgram محمّل تلقائياً لأننا اعتمدنا getRouteKeyName()
        return view('training.show', ['program' => $trainingProgram]);
    }
}
