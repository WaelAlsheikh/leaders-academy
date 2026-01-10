<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;

class ProgramController extends Controller
{
    // عرض جميع البرامج
    public function index()
    {
        $programs = Program::all();
        return view('programs.index', compact('programs'));
    }

    // عرض تفاصيل برنامج واحد
    public function show($slug)
    {
        $program = Program::where('slug', $slug)->firstOrFail();
        return view('programs.show', compact('program'));
    }
}
