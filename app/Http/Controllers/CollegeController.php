<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\College;

class CollegeController extends Controller
{
    // صفحة قائمة الكليات (optionally)
    public function index()
    {
        $colleges = College::all();
        return view('colleges.index', compact('colleges'));
    }

    // صفحة تفاصيل كلية حسب slug
    public function show($slug)
    {
        $college = College::where('slug', $slug)->firstOrFail();
        return view('colleges.show', compact('college'));
    }
}
