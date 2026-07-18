<?php

namespace App\Services\Assignments;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Doctor;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function create(Doctor $doctor, array $data): Assignment
    {
        $section = $this->authorizedSection($doctor, (int) $data['class_section_id']);
        $this->assertValidWindow($data['starts_at'], $data['ends_at']);

        return Assignment::query()->create([
            'doctor_id' => $doctor->id,
            'registrable_subject_id' => $section->registrable_subject_id,
            'class_section_id' => $section->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'status' => $data['status'] ?? 'published',
        ]);
    }

    public function update(Assignment $assignment, Doctor $doctor, array $data): Assignment
    {
        abort_unless($assignment->doctor_id === $doctor->id, 403);

        if (! empty($data['class_section_id'])) {
            $section = $this->authorizedSection($doctor, (int) $data['class_section_id']);
            $assignment->class_section_id = $section->id;
            $assignment->registrable_subject_id = $section->registrable_subject_id;
        }

        $startsAt = $data['starts_at'] ?? $assignment->starts_at;
        $endsAt = $data['ends_at'] ?? $assignment->ends_at;
        $this->assertValidWindow($startsAt, $endsAt);

        $assignment->fill([
            'title' => $data['title'] ?? $assignment->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $assignment->description,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ])->save();

        return $assignment->fresh();
    }

    public function close(Assignment $assignment, Doctor $doctor): Assignment
    {
        abort_unless($assignment->doctor_id === $doctor->id, 403);
        $assignment->forceFill(['status' => 'closed'])->save();

        return $assignment->fresh();
    }

    public function archive(Assignment $assignment, Doctor $doctor): Assignment
    {
        abort_unless($assignment->doctor_id === $doctor->id, 403);
        $assignment->forceFill(['status' => 'archived'])->save();

        return $assignment->fresh();
    }

    public function authorizedSection(Doctor $doctor, int $sectionId): ClassSection
    {
        $section = ClassSection::query()
            ->with('registrableSubject')
            ->where('id', $sectionId)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (! $section) {
            throw ValidationException::withMessages([
                'class_section_id' => 'الشعبة المختارة غير مرتبطة بحسابك.',
            ]);
        }

        return $section;
    }

    private function assertValidWindow(mixed $startsAt, mixed $endsAt): void
    {
        $start = \Carbon\Carbon::parse($startsAt);
        $end = \Carbon\Carbon::parse($endsAt);

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'ends_at' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء.',
            ]);
        }
    }
}
