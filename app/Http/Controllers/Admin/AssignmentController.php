<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmissionFile;
use App\Models\Doctor;
use App\Models\RegistrableSubject;
use App\Services\Assignments\AssignmentSubmissionService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly AssignmentSubmissionService $submissionService,
    ) {}

    public function index(Request $request)
    {
        $doctorId = $request->filled('doctor_id') ? (int) $request->doctor_id : null;
        $subjectId = $request->filled('registrable_subject_id') ? (int) $request->registrable_subject_id : null;

        $assignments = Assignment::query()
            ->with(['doctor', 'registrableSubject', 'classSection'])
            ->withCount('submissions')
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->when($subjectId, fn ($q) => $q->where('registrable_subject_id', $subjectId))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $doctors = Doctor::query()->orderBy('full_name')->get(['id', 'full_name']);
        $subjects = RegistrableSubject::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.assignments.index', array_merge(
            compact('assignments', 'doctors', 'subjects', 'doctorId', 'subjectId'),
            $this->portalViewData($request)
        ));
    }

    public function show(Request $request, Assignment $assignment)
    {
        $assignment->load(['doctor', 'registrableSubject', 'classSection']);

        $students = $assignment->classSection
            ? $assignment->classSection->students()
                ->wherePivot('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
            : collect();

        $submissions = $assignment->submissions()
            ->with(['files', 'student'])
            ->get()
            ->keyBy('student_id');

        return view('admin.assignments.show', array_merge(
            compact('assignment', 'students', 'submissions'),
            $this->portalViewData($request)
        ));
    }

    public function downloadFile(Request $request, AssignmentSubmissionFile $file)
    {
        $file->load('submission.assignment');

        return $this->submissionService->downloadResponse(
            $file,
            $request->boolean('download') || ! $file->isPreviewableInline()
        );
    }

    private function portalViewData(Request $request): array
    {
        $portalContext = str_starts_with((string) $request->route()?->getName(), 'employee.')
            ? 'employee'
            : 'admin';

        return [
            'portalContext' => $portalContext,
            'routeBase' => $portalContext,
            'layout' => $portalContext === 'employee' ? 'layouts.app' : 'voyager::master',
            'hideNavbar' => $portalContext === 'employee',
            'bodyClass' => $portalContext === 'employee' ? 'employee-shell' : '',
        ];
    }
}
