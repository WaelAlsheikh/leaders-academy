<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $registrationsQuery = Registration::query()
            ->where('student_id', $student->id);

        $hasRegistrations = $registrationsQuery->exists();

        $stats = [
            'total_registrations' => 0,
            'accepted_registrations' => 0,
            'under_review_registrations' => 0,
            'semester_linked_registrations' => 0,
        ];

        $latestRegistrations = collect();

        if ($hasRegistrations) {
            $stats = [
                'total_registrations' => (clone $registrationsQuery)->count(),
                'accepted_registrations' => (clone $registrationsQuery)->where('status', 'accepted')->count(),
                'under_review_registrations' => (clone $registrationsQuery)->where('status', 'under_review')->count(),
                'semester_linked_registrations' => (clone $registrationsQuery)->whereNotNull('semester_id')->count(),
            ];

            $latestRegistrations = (clone $registrationsQuery)
                ->with(['college', 'registrableEntity'])
                ->latest('created_at')
                ->limit(3)
                ->get();
        }

        return view('student.dashboard', compact(
            'student',
            'hasRegistrations',
            'stats',
            'latestRegistrations'
        ));
    }
}
