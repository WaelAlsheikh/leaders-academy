<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\College;
use App\Models\EnrollmentCycle;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\Registration;
use App\Models\Semester;
use App\Models\SharedLectureGroup;
use App\Models\Student;
use App\Models\Subject;
use App\Models\StudyTerm;
use App\Models\StudyYear;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SharedLectureService
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService,
    ) {
    }

    /**
     * @return array<string, string> messages per group key
     */
    public function syncAll(): array
    {
        $results = [];

        foreach (config('shared_lectures', []) as $key => $config) {
            $results[$key] = $this->syncGroup($key, $config);
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function syncGroup(string $key, array $config): string
    {
        return DB::transaction(function () use ($key, $config): string {
            $group = SharedLectureGroup::query()->firstOrCreate(
                ['key' => $key],
                ['name' => (string) ($config['name'] ?? $key)]
            );

            $group->update(['name' => (string) ($config['name'] ?? $group->name)]);

            if (! empty($config['copy_subject_to_colleges'])) {
                $this->copySubjectsFromSourceCollege($config);
            }

            $subjectIds = $this->resolveLinkedSubjectIds($config);
            if ($subjectIds->isEmpty()) {
                return 'لم تُعثر على مواد مطابقة للربط.';
            }

            $group->registrableSubjects()->syncWithoutDetaching($subjectIds->all());

            $hostCollegeId = (int) ($config['host_college_id'] ?? 0);
            $hostSemester = $this->resolveHostSemester($hostCollegeId, $subjectIds);
            if (! $hostSemester) {
                return 'لم يُعثر على فصل دراسي نشط للكلية المستضيفة.';
            }

            $hostSubjectId = $this->resolveHostRegistrableSubjectId($config, $hostCollegeId, $subjectIds);
            if (! $hostSubjectId) {
                return 'لم تُعثر على مادة مستضيفة في الكلية المحددة.';
            }

            $group->update(['host_registrable_subject_id' => $hostSubjectId]);

            $this->ensureSubjectOnSemester($hostSemester, $hostSubjectId);
            $hostSection = $this->ensureHostSection($group, $hostSemester, $hostSubjectId, $config);
            $group->update(['host_section_id' => $hostSection->id]);

            $this->ensureMeetings($hostSection, $hostSemester, $config);
            $this->mirrorMeetingsToPartnerColleges($group, $hostSection, $config, $subjectIds);

            $enrolled = $this->syncStudentEnrollments($group, $hostSection, $subjectIds);
            $detached = $this->detachStudentsFromNonHostPrepSections($group, $hostSection, $config);

            return "شعبة مستضيفة #{$hostSection->id} — طلاب مُسجَّلون: {$enrolled}".($detached > 0 ? " — أُزيلوا من شعب مكررة: {$detached}" : '');
        });
    }

    public function groupForRegistrableSubject(int $registrableSubjectId): ?SharedLectureGroup
    {
        return SharedLectureGroup::query()
            ->whereHas('registrableSubjects', fn ($q) => $q->where('registrable_subjects.id', $registrableSubjectId))
            ->first();
    }

    public function groupForSection(int $sectionId): ?SharedLectureGroup
    {
        return SharedLectureGroup::query()
            ->where('host_section_id', $sectionId)
            ->first();
    }

    public function studentCanAccessLiveSession(Student $student, int $sectionId): bool
    {
        if ($student->sections()
            ->wherePivot('status', 'active')
            ->where('class_sections.id', $sectionId)
            ->exists()) {
            return true;
        }

        $group = $this->groupForSection($sectionId);
        if (! $group) {
            return false;
        }

        $subjectIds = $group->registrableSubjects()->pluck('registrable_subjects.id');

        return Registration::query()
            ->where('student_id', $student->id)
            ->where('status', 'accepted')
            ->whereHas('registrableSubjects', fn ($q) => $q->whereIn('registrable_subjects.id', $subjectIds))
            ->exists();
    }

    public function resolveTargetSection(Semester $semester, int $registrableSubjectId): ?ClassSection
    {
        $group = $this->groupForRegistrableSubject($registrableSubjectId);
        if ($group?->host_section_id) {
            return $group->hostSection;
        }

        return ClassSection::query()
            ->where('semester_id', $semester->id)
            ->where('registrable_subject_id', $registrableSubjectId)
            ->withCount(['students', 'meetings'])
            ->orderByDesc('meetings_count')
            ->orderBy('students_count')
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function copySubjectsFromSourceCollege(array $config): void
    {
        $sourceCollegeId = (int) ($config['source_college_id'] ?? 0);
        $subjectName = trim((string) ($config['subject_name'] ?? ''));
        $yearSort = (int) ($config['study_year_sort'] ?? 1);
        $termSort = (int) ($config['study_term_sort'] ?? 2);

        if ($sourceCollegeId === 0 || $subjectName === '') {
            return;
        }

        $sourceEntity = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->where('entity_id', $sourceCollegeId)
            ->first();

        if (! $sourceEntity) {
            return;
        }

        $sourceTerm = $this->resolveStudyTerm($sourceEntity->id, $yearSort, $termSort);
        $sourceRs = RegistrableSubject::query()
            ->where('registrable_entity_id', $sourceEntity->id)
            ->where('study_term_id', $sourceTerm?->id)
            ->whereRaw('TRIM(name) = ?', [$subjectName])
            ->first();

        if (! $sourceRs?->legacy_subject_id) {
            return;
        }

        $sourceLegacy = Subject::find($sourceRs->legacy_subject_id);
        if (! $sourceLegacy) {
            return;
        }

        foreach ((array) ($config['copy_subject_to_colleges'] ?? []) as $targetCollegeId) {
            $targetCollegeId = (int) $targetCollegeId;
            if ($targetCollegeId === 0 || $targetCollegeId === $sourceCollegeId) {
                continue;
            }

            $targetCollege = College::find($targetCollegeId);
            if (! $targetCollege) {
                continue;
            }

            $targetEntity = $this->collegeSubjectSyncService->ensureCollegeEntity($targetCollege);
            $targetTerm = $this->resolveStudyTerm($targetEntity->id, $yearSort, $termSort);
            if (! $targetTerm) {
                continue;
            }

            $exists = RegistrableSubject::query()
                ->where('registrable_entity_id', $targetEntity->id)
                ->where('study_term_id', $targetTerm->id)
                ->whereRaw('TRIM(name) = ?', [$subjectName])
                ->exists();

            if ($exists) {
                continue;
            }

            $code = $this->uniqueSubjectCodeForCollege($targetCollegeId, (string) $sourceLegacy->code);

            $created = $this->collegeSubjectSyncService->createLegacyAndSync($targetCollege, [
                'name' => $sourceLegacy->name,
                'code' => $code,
                'study_term_id' => $targetTerm->id,
                'credit_hours' => $sourceLegacy->credit_hours,
                'is_active' => true,
                'doctor_id' => $sourceRs->doctor_id,
            ]);

            $this->ensureSubjectOnOpenCycles($created);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveLinkedSubjectIds(array $config): Collection
    {
        $subjectName = trim((string) ($config['subject_name'] ?? ''));

        $query = RegistrableSubject::query()
            ->whereRaw('TRIM(name) = ?', [$subjectName]);

        if (! empty($config['college_ids'])) {
            $entityIds = RegistrableEntity::query()
                ->where('entity_type', 'college')
                ->whereIn('entity_id', (array) $config['college_ids'])
                ->pluck('id');

            $query->whereIn('registrable_entity_id', $entityIds);
        }

        if (! empty($config['study_year_sort']) && ! empty($config['study_term_sort'])) {
            $termIds = StudyTerm::query()
                ->whereHas('studyYear', function ($q) use ($config): void {
                    $q->where('sort_order', (int) $config['study_year_sort']);
                    if (! empty($config['college_ids'])) {
                        $entityIds = RegistrableEntity::query()
                            ->where('entity_type', 'college')
                            ->whereIn('entity_id', (array) $config['college_ids'])
                            ->pluck('id');
                        $q->whereIn('registrable_entity_id', $entityIds);
                    }
                })
                ->where('sort_order', (int) $config['study_term_sort'])
                ->pluck('id');

            $query->whereIn('study_term_id', $termIds);
        }

        return $query->pluck('id');
    }

    private function resolveHostSemester(int $hostCollegeId, Collection $subjectIds): ?Semester
    {
        $entity = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->where('entity_id', $hostCollegeId)
            ->first();

        if (! $entity) {
            return null;
        }

        $semesterWithSubject = Semester::query()
            ->where('status', 'active')
            ->whereHas('enrollmentCycle', fn ($q) => $q->where('registrable_entity_id', $entity->id))
            ->whereHas('registrableSubjects', fn ($q) => $q->whereIn('registrable_subjects.id', $subjectIds))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        if ($semesterWithSubject) {
            return $semesterWithSubject;
        }

        return Semester::query()
            ->where('status', 'active')
            ->whereHas('enrollmentCycle', fn ($q) => $q->where('registrable_entity_id', $entity->id))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
    }

    private function resolveHostRegistrableSubjectId(array $config, int $hostCollegeId, Collection $subjectIds): ?int
    {
        $entity = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->where('entity_id', $hostCollegeId)
            ->value('id');

        if (! $entity) {
            return $subjectIds->first();
        }

        $match = RegistrableSubject::query()
            ->whereIn('id', $subjectIds)
            ->where('registrable_entity_id', $entity)
            ->orderBy('id')
            ->value('id');

        return $match ?? $subjectIds->first();
    }

    private function ensureSubjectOnSemester(Semester $semester, int $registrableSubjectId): void
    {
        $legacySubjectId = RegistrableSubject::where('id', $registrableSubjectId)->value('legacy_subject_id');

        $semester->registrableSubjects()->syncWithoutDetaching([
            $registrableSubjectId => [
                'is_active' => true,
                'registered_count' => 0,
                'subject_id' => $legacySubjectId,
            ],
        ]);
    }

    private function ensureHostSection(
        SharedLectureGroup $group,
        Semester $semester,
        int $hostSubjectId,
        array $config
    ): ClassSection {
        $legacySubjectId = RegistrableSubject::where('id', $hostSubjectId)->value('legacy_subject_id');
        $doctorId = RegistrableSubject::where('id', $hostSubjectId)->value('doctor_id');

        $notes = 'محاضرة مشتركة: '.($config['name'] ?? $group->name);

        return ClassSection::query()->firstOrCreate(
            [
                'semester_id' => $semester->id,
                'registrable_subject_id' => $hostSubjectId,
                'name' => 'مشتركة',
            ],
            [
                'subject_id' => $legacySubjectId,
                'doctor_id' => $doctorId,
                'mode' => 'online',
                'zoom_url' => null,
                'notes' => $notes,
            ]
        );
    }

    private function ensureMeetings(ClassSection $section, Semester $semester, array $config): void
    {
        if ($section->meetings()->exists()) {
            return;
        }

        $donorSection = ClassSection::query()
            ->where('registrable_subject_id', $section->registrable_subject_id)
            ->where('id', '!=', $section->id)
            ->whereHas('meetings')
            ->with('meetings')
            ->orderByDesc('id')
            ->first();

        if ($donorSection) {
            foreach ($donorSection->meetings as $meeting) {
                $section->meetings()->updateOrCreate(
                    [
                        'day_of_week' => $meeting->day_of_week,
                        'starts_at' => $meeting->starts_at,
                    ],
                    [
                        'ends_at' => $meeting->ends_at,
                        'start_date' => $meeting->start_date,
                        'end_date' => $meeting->end_date,
                    ]
                );
            }

            return;
        }

        $meeting = $config['default_meeting'] ?? null;
        if (! is_array($meeting)) {
            return;
        }

        $startDate = $semester->start_date?->toDateString()
            ?? $semester->enrollmentCycle?->registration_starts_at?->toDateString()
            ?? Carbon::now(config('app.timezone', 'UTC'))->toDateString();
        $endDate = $semester->end_date?->toDateString()
            ?? Carbon::parse($startDate)->addMonths(4)->toDateString();

        $section->meetings()->create([
            'day_of_week' => (int) ($meeting['day_of_week'] ?? 0),
            'starts_at' => (string) ($meeting['starts_at'] ?? '10:00:00'),
            'ends_at' => (string) ($meeting['ends_at'] ?? '12:00:00'),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    private function mirrorMeetingsToPartnerColleges(
        SharedLectureGroup $group,
        ClassSection $hostSection,
        array $config,
        Collection $subjectIds
    ): void {
        if (empty($config['college_ids']) || count((array) $config['college_ids']) < 2) {
            return;
        }

        $hostCollegeId = (int) ($config['host_college_id'] ?? 0);
        $meetings = $hostSection->meetings()->get();
        if ($meetings->isEmpty()) {
            return;
        }

        foreach ((array) $config['college_ids'] as $collegeId) {
            $collegeId = (int) $collegeId;
            if ($collegeId === $hostCollegeId) {
                continue;
            }

            $entity = RegistrableEntity::query()
                ->where('entity_type', 'college')
                ->where('entity_id', $collegeId)
                ->first();

            if (! $entity) {
                continue;
            }

            $partnerSubjectId = RegistrableSubject::query()
                ->whereIn('id', $subjectIds)
                ->where('registrable_entity_id', $entity->id)
                ->value('id');

            if (! $partnerSubjectId) {
                continue;
            }

            $partnerSemester = Semester::query()
                ->where('status', 'active')
                ->whereHas('enrollmentCycle', fn ($q) => $q->where('registrable_entity_id', $entity->id))
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first();

            if (! $partnerSemester) {
                continue;
            }

            $this->ensureSubjectOnSemester($partnerSemester, (int) $partnerSubjectId);

            $partnerSection = ClassSection::query()->firstOrCreate(
                [
                    'semester_id' => $partnerSemester->id,
                    'registrable_subject_id' => $partnerSubjectId,
                    'name' => 'مشتركة (مرآة)',
                ],
                [
                    'subject_id' => RegistrableSubject::where('id', $partnerSubjectId)->value('legacy_subject_id'),
                    'doctor_id' => $hostSection->doctor_id,
                    'mode' => 'online',
                    'zoom_url' => null,
                    'notes' => 'مرآة مواعيد محاضرة مشتركة — الدخول عبر الشعبة المستضيفة #'.$hostSection->id,
                ]
            );

            foreach ($meetings as $meeting) {
                $partnerSection->meetings()->updateOrCreate(
                    [
                        'day_of_week' => $meeting->day_of_week,
                        'starts_at' => $meeting->starts_at,
                    ],
                    [
                        'ends_at' => $meeting->ends_at,
                        'start_date' => $meeting->start_date,
                        'end_date' => $meeting->end_date,
                    ]
                );
            }
        }
    }

    private function syncStudentEnrollments(
        SharedLectureGroup $group,
        ClassSection $hostSection,
        Collection $subjectIds
    ): int {
        $studentIds = Registration::query()
            ->where('status', 'accepted')
            ->whereHas('registrableSubjects', fn ($q) => $q->whereIn('registrable_subjects.id', $subjectIds))
            ->pluck('student_id')
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = ['status' => 'active'];
        }

        $hostSection->students()->syncWithoutDetaching($syncData);

        return $studentIds->count();
    }

    private function detachStudentsFromNonHostPrepSections(
        SharedLectureGroup $group,
        ClassSection $hostSection,
        array $config
    ): int {
        if (($config['subject_name'] ?? '') !== 'البرنامج التحضيري') {
            return 0;
        }

        $subjectIds = $group->registrableSubjects()->pluck('registrable_subjects.id');
        $otherSectionIds = ClassSection::query()
            ->whereIn('registrable_subject_id', $subjectIds)
            ->where('id', '!=', $hostSection->id)
            ->pluck('id');

        if ($otherSectionIds->isEmpty()) {
            return 0;
        }

        return DB::table('student_sections')
            ->whereIn('section_id', $otherSectionIds)
            ->delete();
    }

    private function resolveStudyTerm(int $registrableEntityId, int $yearSort, int $termSort): ?StudyTerm
    {
        $year = StudyYear::query()
            ->where('registrable_entity_id', $registrableEntityId)
            ->where('sort_order', $yearSort)
            ->first();

        if (! $year) {
            return null;
        }

        return StudyTerm::query()
            ->where('study_year_id', $year->id)
            ->where('sort_order', $termSort)
            ->first();
    }

    private function ensureSubjectOnOpenCycles(RegistrableSubject $subject): void
    {
        $cycles = EnrollmentCycle::query()
            ->where('registrable_entity_id', $subject->registrable_entity_id)
            ->whereIn('status', ['open', 'approved', 'draft'])
            ->get();

        foreach ($cycles as $cycle) {
            $cycle->registrableSubjects()->syncWithoutDetaching([
                $subject->id => ['is_open' => true],
            ]);
        }
    }

    private function uniqueSubjectCodeForCollege(int $collegeId, string $baseCode): string
    {
        $baseCode = trim($baseCode) !== '' ? trim($baseCode) : 'SUB';
        $candidate = $baseCode;
        $suffix = 1;

        while (Subject::query()->where('code', $candidate)->exists()) {
            $candidate = $baseCode.'-C'.$collegeId.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function attachStudentToSharedHostIfApplicable(Registration $registration, RegistrableSubject $subject): void
    {
        $group = $this->groupForRegistrableSubject($subject->id);
        if (! $group?->host_section_id) {
            return;
        }

        if ($registration->status !== 'accepted') {
            return;
        }

        $group->hostSection?->students()->syncWithoutDetaching([
            $registration->student_id => ['status' => 'active'],
        ]);
    }
}
