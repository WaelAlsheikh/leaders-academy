<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmissionFile;
use App\Services\Assignments\AssignmentSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly AssignmentSubmissionService $submissionService,
    ) {}

    public function index()
    {
        $student = $this->student();
        $sectionIds = $this->activeSectionIds($student);

        $assignments = Assignment::query()
            ->whereIn('class_section_id', $sectionIds)
            ->whereIn('status', ['published', 'closed'])
            ->with(['registrableSubject', 'classSection', 'doctor'])
            ->latest('starts_at')
            ->paginate(20);

        $submissions = $student->id
            ? \App\Models\AssignmentSubmission::query()
                ->where('student_id', $student->id)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->withCount('files')
                ->get()
                ->keyBy('assignment_id')
            : collect();

        return view('student.assignments.index', compact('student', 'assignments', 'submissions'));
    }

    public function show(Assignment $assignment)
    {
        $student = $this->student();
        $this->authorizeAssignmentAccess($assignment, $student);

        $assignment->load(['registrableSubject', 'classSection', 'doctor']);

        $submission = $this->submissionService->findOrCreateSubmission($assignment, $student);
        $submission->load('files');

        return view('student.assignments.show', [
            'student' => $student,
            'assignment' => $assignment,
            'submission' => $submission,
            'canSubmit' => $assignment->isOpenForSubmission(),
            'allowedMimes' => config('assignments.allowed_mimes'),
            'maxFileKb' => config('assignments.max_file_kb'),
        ]);
    }

    public function upload(Request $request, Assignment $assignment)
    {
        $student = $this->student();
        $this->authorizeAssignmentAccess($assignment, $student);

        $mimes = config('assignments.allowed_mimes');
        $maxKb = (int) config('assignments.max_file_kb');

        $data = $request->validate([
            'upload' => 'required|file|mimes:'.$mimes.'|max:'.$maxKb,
        ]);

        try {
            $this->submissionService->uploadFile($assignment, $student, $request->file('upload'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'تم رفع الملف بنجاح.');
    }

    public function replace(Request $request, AssignmentSubmissionFile $file)
    {
        $student = $this->student();
        $file->load('submission.assignment');
        abort_unless($file->submission?->student_id === $student->id, 403);
        $this->authorizeAssignmentAccess($file->submission->assignment, $student);

        $mimes = config('assignments.allowed_mimes');
        $maxKb = (int) config('assignments.max_file_kb');

        $request->validate([
            'upload' => 'required|file|mimes:'.$mimes.'|max:'.$maxKb,
        ]);

        try {
            $this->submissionService->replaceFile($file, $student, $request->file('upload'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'تم استبدال الملف.');
    }

    public function destroyFile(AssignmentSubmissionFile $file)
    {
        $student = $this->student();
        $file->load('submission.assignment');
        abort_unless($file->submission?->student_id === $student->id, 403);
        $this->authorizeAssignmentAccess($file->submission->assignment, $student);

        try {
            $this->submissionService->deleteFile($file, $student);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'تم حذف الملف.');
    }

    public function downloadFile(Request $request, AssignmentSubmissionFile $file)
    {
        $student = $this->student();
        $file->load('submission.assignment');
        abort_unless($file->submission?->student_id === $student->id, 403);
        $this->authorizeAssignmentAccess($file->submission->assignment, $student);

        return $this->submissionService->downloadResponse(
            $file,
            $request->boolean('download') || ! $file->isPreviewableInline()
        );
    }

    private function student()
    {
        return Auth::guard('student')->user();
    }

    private function activeSectionIds($student)
    {
        return $student->sections()
            ->where('student_sections.status', 'active')
            ->pluck('class_sections.id');
    }

    private function authorizeAssignmentAccess(Assignment $assignment, $student): void
    {
        abort_unless(in_array($assignment->status, ['published', 'closed'], true), 404);

        $allowed = $this->activeSectionIds($student)->contains($assignment->class_section_id);
        abort_unless($allowed, 403);
    }
}
