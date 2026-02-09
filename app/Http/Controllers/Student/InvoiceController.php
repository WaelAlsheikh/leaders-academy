<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $registrations = Registration::with(['college', 'subjects'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return view('student.invoices.index', compact('registrations'));
    }
}
