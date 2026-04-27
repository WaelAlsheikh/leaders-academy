<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\RegistrableEntity;
use App\Models\Registration;
use App\Models\RegistrationSeason;
use App\Services\StudentRegistrationEligibilityService;
use Illuminate\Http\Request;

class RegistrableRegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationEligibilityService $eligibilityService
    ) {
    }

    public function index(Request $request, RegistrableEntity $entity)
    {
        $registrationsQuery = Registration::query()
            ->with([
                'student',
                'registrableSubjects.studyTerm.studyYear',
                'enrollmentCycle.registrationSeason',
                'enrollmentCycle.semester',
            ])
            ->where('registrable_entity_id', $entity->id)
            ->latest();

        $seasonId = $request->integer('season_id');
        if ($seasonId) {
            $registrationsQuery->whereHas('enrollmentCycle', function ($query) use ($seasonId) {
                $query->where('registration_season_id', $seasonId);
            });
        }

        $status = $request->string('status')->toString();
        if (in_array($status, ['under_review', 'accepted', 'rejected'], true)) {
            $registrationsQuery->where('status', $status);
        }

        $studentSearch = trim($request->string('student')->toString());
        if ($studentSearch !== '') {
            $registrationsQuery->whereHas('student', function ($query) use ($studentSearch) {
                $query->where('first_name', 'like', '%' . $studentSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $studentSearch . '%')
                    ->orWhere('username', 'like', '%' . $studentSearch . '%')
                    ->orWhere('email', 'like', '%' . $studentSearch . '%');
            });
        }

        $registrations = $registrationsQuery->get();

        $seasonOptions = RegistrationSeason::query()
            ->whereHas('enrollmentCycles', function ($query) use ($entity) {
                $query->where('registrable_entity_id', $entity->id);
            })
            ->latest('id')
            ->get();

        $progressSummaries = [];
        foreach ($registrations as $registration) {
            $progressSummaries[$registration->id] = $this->eligibilityService
                ->summarizeStudentProgressForEntity($registration->student, $entity, $registration);
        }

        $entityLabel = match ($entity->entity_type) {
            'college' => 'الكلية',
            'program_branch' => 'البرنامج الجامعي',
            'training_program_branch' => 'البرنامج التدريبي',
            default => 'الكيان',
        };

        return view('admin.registrable_registrations.index', array_merge(
            compact(
                'entity',
                'registrations',
                'seasonOptions',
                'progressSummaries',
                'seasonId',
                'status',
                'studentSearch',
                'entityLabel'
            ),
            $this->portalViewData($request),
            [
                'college' => $entity->entity_type === 'college'
                    ? College::find($entity->entity_id)
                    : null,
            ]
        ));
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
