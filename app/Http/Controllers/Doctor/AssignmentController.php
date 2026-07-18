<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Models\ClassSection;
use App\Services\Assignments\AssignmentService;
use App\Services\Assignments\AssignmentSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly AssignmentService $assignmentService,
        private readonly AssignmentSubmissionService $submissionService,
    ) {}

    public function index()
    {
        $doctor = $this->doctor();

        $assignments = Assignment::query()
            ->where('doctor_id', $doctor->id)
            ->with(['registrableSubject', 'classSection'])
            ->withCount('submissions')
            ->latest('id')
            ->paginate(20);

        return view('doctor.assignments.index', compact('doctor', 'assignments'));
    }

    public function create()
    {
        $doctor = $this->doctor();
        $sections = $this->sectionsForDoctor($doctor);

        return view('doctor.assignments.create', compact('doctor', 'sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        try {
            $assignment = $this->assignmentService->create($this->doctor(), $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('doctor.assignments.show', $assignment)
            ->with('success', 'تم إنشاء الوظيفة بنجاح.');
    }

    public function show(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $assignment->load(['registrableSubject', 'classSection', 'doctor']);

        $students = $assignment->classSection
            ? $assignment->classSection->students()
                ->wherePivot('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
            : collect();

        $submissions = AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->with(['files', 'student'])
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            if (! $submissions->has($student->id)) {
                $created = $this->submissionService->findOrCreateSubmission($assignment, $student);
                $created->setRelation('files', collect());
                $created->setRelation('student', $student);
                $submissions->put($student->id, $created);
            }
        }

        return view('doctor.assignments.show', [
            'doctor' => $this->doctor(),
            'assignment' => $assignment,
            'students' => $students,
            'submissions' => $submissions,
        ]);
    }

    public function edit(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $doctor = $this->doctor();
        $sections = $this->sectionsForDoctor($doctor);

        return view('doctor.assignments.edit', compact('doctor', 'assignment', 'sections'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $data = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        try {
            $this->assignmentService->update($assignment, $this->doctor(), $data);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('doctor.assignments.show', $assignment)
            ->with('success', 'تم تحديث الوظيفة.');
    }

    public function close(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $this->assignmentService->close($assignment, $this->doctor());

        return back()->with('success', 'تم إغلاق الوظيفة.');
    }

    public function archive(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $this->assignmentService->archive($assignment, $this->doctor());

        return redirect()
            ->route('doctor.assignments.index')
            ->with('success', 'تم أرشفة الوظيفة.');
    }

    public function updateNotes(Request $request, AssignmentSubmission $submission)
    {
        abort_unless($submission->assignment?->doctor_id === $this->doctor()->id, 403);

        $data = $request->validate([
            'doctor_notes' => 'nullable|string|max:5000',
        ]);

        $this->submissionService->updateDoctorNotes($submission, (string) ($data['doctor_notes'] ?? ''));

        return back()->with('success', 'تم حفظ الملاحظات.');
    }

    public function downloadFile(Request $request, AssignmentSubmissionFile $file)
    {
        $file->load('submission.assignment');
        abort_unless($file->submission?->assignment?->doctor_id === $this->doctor()->id, 403);

        return $this->submissionService->downloadResponse(
            $file,
            $request->boolean('download') || ! $file->isPreviewableInline()
        );
    }

    private function doctor()
    {
        return Auth::guard('doctor')->user();
    }

    private function authorizeAssignment(Assignment $assignment): void
    {
        abort_unless($assignment->doctor_id === $this->doctor()->id, 403);
    }

    private function sectionsForDoctor($doctor)
    {
        return ClassSection::query()
            ->where('doctor_id', $doctor->id)
            ->with('registrableSubject')
            ->orderBy('name')
            ->get();
    }
}
