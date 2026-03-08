<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramBranch;

class ProgramController extends Controller
{
    // عرض جميع البرامج
    public function index()
    {
        $programs = Program::all();
        return view('programs.index', compact('programs'));
    }

    // عرض تفاصيل برنامج واحد
    public function show(Program $program)
    {
        $branches = $program->branches()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('programs.show', compact('program', 'branches'));
    }

    public function showBranch(Program $program, ProgramBranch $branch)
    {
        if ((int) $branch->program_id !== (int) $program->id) {
            abort(404);
        }

        return view('programs.branch-show', compact('program', 'branch'));
    }
}
