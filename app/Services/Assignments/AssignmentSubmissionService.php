<?php

namespace App\Services\Assignments;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssignmentSubmissionService
{
    public function findOrCreateSubmission(Assignment $assignment, Student $student): AssignmentSubmission
    {
        return AssignmentSubmission::query()->firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'submitted_at' => null,
                'doctor_notes' => null,
            ]
        );
    }

    public function uploadFile(Assignment $assignment, Student $student, UploadedFile $file): AssignmentSubmissionFile
    {
        if (! $assignment->isOpenForSubmission()) {
            throw ValidationException::withMessages([
                'upload' => 'نافذة تسليم الوظيفة غير مفتوحة حالياً.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $student, $file) {
            $submission = $this->findOrCreateSubmission($assignment, $student);

            $path = $file->store(
                'assignments/submissions/'.$assignment->id.'/'.$student->id,
                'public'
            );

            $stored = AssignmentSubmissionFile::query()->create([
                'assignment_submission_id' => $submission->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                'file_size' => $file->getSize() ?: 0,
            ]);

            $submission->forceFill(['submitted_at' => now()])->save();

            return $stored;
        });
    }

    public function replaceFile(
        AssignmentSubmissionFile $existing,
        Student $student,
        UploadedFile $file
    ): AssignmentSubmissionFile {
        $submission = $existing->submission;
        abort_unless($submission && $submission->student_id === $student->id, 403);

        $assignment = $submission->assignment;
        if (! $assignment || ! $assignment->isOpenForSubmission()) {
            throw ValidationException::withMessages([
                'upload' => 'لا يمكن تعديل الملفات خارج نافذة التسليم.',
            ]);
        }

        return DB::transaction(function () use ($existing, $submission, $assignment, $student, $file) {
            $this->deleteFileFromDisk($existing->file_path);

            $path = $file->store(
                'assignments/submissions/'.$assignment->id.'/'.$student->id,
                'public'
            );

            $existing->update([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                'file_size' => $file->getSize() ?: 0,
            ]);

            $submission->forceFill(['submitted_at' => now()])->save();

            return $existing->fresh();
        });
    }

    public function deleteFile(AssignmentSubmissionFile $file, Student $student): void
    {
        $submission = $file->submission;
        abort_unless($submission && $submission->student_id === $student->id, 403);

        $assignment = $submission->assignment;
        if (! $assignment || ! $assignment->isOpenForSubmission()) {
            throw ValidationException::withMessages([
                'upload' => 'لا يمكن حذف الملفات خارج نافذة التسليم.',
            ]);
        }

        DB::transaction(function () use ($file, $submission) {
            $this->deleteFileFromDisk($file->file_path);
            $file->delete();

            if ($submission->files()->count() === 0) {
                $submission->forceFill(['submitted_at' => null])->save();
            } else {
                $submission->forceFill(['submitted_at' => now()])->save();
            }
        });
    }

    public function updateDoctorNotes(AssignmentSubmission $submission, string $notes): AssignmentSubmission
    {
        $submission->forceFill([
            'doctor_notes' => $notes !== '' ? $notes : null,
        ])->save();

        return $submission->fresh();
    }

    public function downloadResponse(AssignmentSubmissionFile $file, bool $forceDownload = false)
    {
        abort_unless($file->existsOnDisk(), 404);

        $path = $file->absolutePath();
        $headers = [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
        ];

        if ($forceDownload || ! $file->isPreviewableInline()) {
            return response()->download($path, $file->original_name, $headers);
        }

        return response()->file($path, $headers);
    }

    private function deleteFileFromDisk(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
