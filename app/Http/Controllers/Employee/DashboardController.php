<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\College;
use App\Models\EnrollmentCycle;
use App\Models\ProgramBranch;
use App\Models\SectionMeeting;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TrainingProgramBranch;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $collegeCount = College::count();
        $subjectCount = Subject::count();
        $activeCollegeCount = College::whereHas('subjects', function ($query) {
            $query->where('is_active', true);
        })->count();
        $programBranchCount = ProgramBranch::count();
        $trainingProgramBranchCount = TrainingProgramBranch::count();
        $cycleCount = EnrollmentCycle::activeListing()->count();
        $archivedCycleCount = EnrollmentCycle::archivedListing()->count();
        $semesterCount = Semester::count();
        $sectionCount = ClassSection::count();
        $meetingCount = SectionMeeting::count();

        return view('employee.dashboard', compact(
            'employee',
            'collegeCount',
            'subjectCount',
            'activeCollegeCount',
            'programBranchCount',
            'trainingProgramBranchCount',
            'cycleCount',
            'archivedCycleCount',
            'semesterCount',
            'sectionCount',
            'meetingCount'
        ));
    }
}
