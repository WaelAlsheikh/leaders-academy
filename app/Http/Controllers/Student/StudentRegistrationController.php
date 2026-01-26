<?php

namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;

class StudentRegistrationController extends Controller
{
    public function create()
    {
        $colleges = College::with('subjects')->get();
        return view('student.registration.create', compact('colleges'));
    }
}
