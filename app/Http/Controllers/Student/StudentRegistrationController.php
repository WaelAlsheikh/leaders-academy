<?php

namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\EnrollmentCycle;
use App\Models\PricingSetting;
use App\Models\Registration;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Services\StudentRegistrationEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentRegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationEligibilityService $eligibilityService
    ) {
    }

    public function create()
    {
        RegistrableEntity::syncFromSources();

        $openCycles = EnrollmentCycle::with(['registrableEntity', 'registrableSubjects' => function ($q) {
            $q->wherePivot('is_open', true);
        }])
            ->activeListing()
            ->where('status', 'open')
            ->get()
            ->filter(fn ($cycle) => $cycle->isOpenNow())
            ->values();

        $registrableEntities = $openCycles->map(function ($cycle) {
            return $cycle->registrableEntity;
        })->filter()->unique('id')->values();

        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $latestCyclesByEntity = $openCycles
            ->sortByDesc('id')
            ->groupBy('registrable_entity_id')
            ->map(fn ($cyclesForEntity) => $cyclesForEntity->first());

        $entitySubjects = $latestCyclesByEntity->map(function ($cycle) use ($student) {
            return $this->eligibilityService->eligibleSubjectsForCycle($student, $cycle);
        });

        $entitySubjectGroups = $entitySubjects->map(function ($subjects) {
            return $this->eligibilityService->groupSubjectsByPlan($subjects);
        });

        $entitiesByType = [
            'college' => $registrableEntities->where('entity_type', 'college')->values(),
            'program_branch' => $registrableEntities->where('entity_type', 'program_branch')->values(),
            'training_program_branch' => $registrableEntities->where('entity_type', 'training_program_branch')->values(),
        ];

        $pricing = PricingSetting::query()->latest()->first();
        $minSubjects = $pricing?->min_subjects ?? 4;
        $registrationFee = (float) ($pricing?->registration_fee ?? 0);

        return view('student.registration.create', compact(
            'entitiesByType',
            'openCycles',
            'entitySubjects',
            'entitySubjectGroups',
            'minSubjects',
            'registrationFee'
        ));
    }

    public function store(Request $request)
    {
        $pricing = PricingSetting::query()->latest()->first();
        $minSubjects = $pricing?->min_subjects ?? 4;
        $registrationFee = (float) ($pricing?->registration_fee ?? 0);

        $data = $request->validate([
            'registrable_entity_id' => 'required|integer|exists:registrable_entities,id',
            'subjects' => 'required|array|min:' . $minSubjects,
            'subjects.*' => 'required|integer|exists:registrable_subjects,id',
        ]);

        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $subjectIds = array_values(array_unique($data['subjects']));

        $entity = RegistrableEntity::findOrFail($data['registrable_entity_id']);
        if (!$entity->is_active) {
            return back()
                ->withInput()
                ->withErrors(['registrable_entity_id' => 'هذا الخيار غير متاح للتسجيل حالياً.']);
        }
        $collegeId = $entity->entity_type === 'college' ? $entity->entity_id : null;

        $cycle = EnrollmentCycle::where('registrable_entity_id', $entity->id)
            ->activeListing()
            ->where('status', 'open')
            ->orderByDesc('id')
            ->get()
            ->first(fn ($c) => $c->isOpenNow());

        if (!$cycle) {
            return back()
                ->withInput()
                ->withErrors(['registrable_entity_id' => 'لا توجد دورة تسجيل مفتوحة لهذا الخيار حالياً.']);
        }

        $allowedSubjectIds = $cycle->registrableSubjects()
            ->wherePivot('is_open', true)
            ->pluck('registrable_subjects.id')
            ->toArray();

        $eligibleSubjects = $this->eligibilityService
            ->eligibleSubjectsForCycle($student, $cycle)
            ->whereIn('id', $allowedSubjectIds)
            ->values();

        $subjects = $eligibleSubjects->whereIn('id', $subjectIds)->values();

        if ($subjects->count() !== count($subjectIds)) {
            return back()
                ->withInput()
                ->withErrors(['subjects' => 'يجب اختيار مواد صحيحة من نفس الخيار.']);
        }

        $pricePerHour = (float) ($entity->price_per_credit_hour ?? 0);
        $totalHours = (int) $subjects->sum('credit_hours');
        $subtotalAmount = $totalHours * $pricePerHour;
        $totalAmount = $subtotalAmount + $registrationFee;

        DB::transaction(function () use (
            $student,
            $entity,
            $collegeId,
            $cycle,
            $subjects,
            $pricePerHour,
            $totalHours,
            $subtotalAmount,
            $registrationFee,
            $totalAmount
        ) {
            $registration = Registration::create([
                'student_id' => $student->id,
                'college_id' => $collegeId,
                'registrable_entity_id' => $entity->id,
                'enrollment_cycle_id' => $cycle->id,
                'status' => 'under_review',
                'subjects_count' => $subjects->count(),
                'total_hours' => $totalHours,
                'subtotal_amount' => $subtotalAmount,
                'registration_fee' => $registrationFee,
                'total_amount' => $totalAmount,
            ]);

            foreach ($subjects as $subject) {
                $registration->registrableSubjects()->attach($subject->id, [
                    'credit_hours' => $subject->credit_hours,
                    'price_per_hour' => $pricePerHour,
                    'total_price' => $subject->credit_hours * $pricePerHour,
                    'result_status' => 'undefined',
                ]);
            }
        });

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'تم إرسال طلب التسجيل بنجاح');
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $registrations = Registration::with(['college', 'registrableEntity', 'registrableSubjects', 'enrollmentCycle.archiveRecord', 'semester'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return view('student.registration.index', compact('registrations'));
    }
}
