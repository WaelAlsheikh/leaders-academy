<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArchivedEnrollmentCycle;
use App\Models\Registration;
use App\Models\RegistrationSeason;
use App\Models\RegistrableEntity;
use App\Services\RegistrationSeasonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrationSeasonController extends Controller
{
    public function __construct(
        private readonly RegistrationSeasonService $registrationSeasonService
    ) {
    }

    public function index(Request $request)
    {
        RegistrableEntity::syncFromSources();

        $seasons = RegistrationSeason::query()
            ->activeListing()
            ->with([
                'enrollmentCycles.registrableEntity',
            ])
            ->withCount('enabledEnrollmentCycles')
            ->latest('id')
            ->get();

        $registrableEntities = RegistrableEntity::query()
            ->where('is_active', true)
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get()
            ->groupBy('entity_type');

        return view('admin.registration_seasons.index', array_merge(
            compact('seasons', 'registrableEntities'),
            $this->portalViewData($request)
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'integer|exists:registrable_entities,id',
        ]);

        $entities = RegistrableEntity::query()
            ->where('is_active', true)
            ->whereIn('id', $data['entity_ids'])
            ->get();

        if ($entities->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['entity_ids' => 'يجب اختيار كيان واحد على الأقل لفتحه في هذه الدورة.']);
        }

        $season = $this->registrationSeasonService->createSeason(
            [
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'registration_starts_at' => $data['registration_starts_at'] ?? null,
                'registration_ends_at' => $data['registration_ends_at'] ?? null,
                'status' => 'open',
            ],
            $entities,
            $this->currentActorId()
        );

        return redirect()
            ->route($this->routeName($request, 'registration_seasons.show'), $season)
            ->with('success', 'تم إنشاء الدورة الفصلية العامة وفتح الكيانات المحددة بنجاح.');
    }

    public function show(Request $request, RegistrationSeason $season)
    {
        if ($season->is_archived) {
            return redirect()->route($this->routeName($request, 'archived_enrollment_cycles.show'), $season);
        }

        $season->load([
            'enrollmentCycles' => function ($query) {
                $query->with(['registrableEntity', 'semester'])
                    ->withCount(['registrations', 'registrableSubjects'])
                    ->orderBy('id');
            },
        ]);

        $seasonEntityIds = $season->enrollmentCycles->pluck('registrable_entity_id')->all();

        $registrableEntities = RegistrableEntity::query()
            ->where(function ($query) use ($seasonEntityIds) {
                $query->where('is_active', true);

                if ($seasonEntityIds !== []) {
                    $query->orWhereIn('id', $seasonEntityIds);
                }
            })
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        $cyclesByEntity = $season->enrollmentCycles->keyBy('registrable_entity_id');

        return view('admin.registration_seasons.show', array_merge(
            compact('season', 'registrableEntities', 'cyclesByEntity'),
            $this->portalViewData($request)
        ));
    }

    public function archivedIndex(Request $request)
    {
        $seasons = RegistrationSeason::query()
            ->archivedListing()
            ->with(['archivedBy'])
            ->withCount('enabledEnrollmentCycles')
            ->latest('archived_at')
            ->latest('id')
            ->get();

        return view('admin.registration_seasons.archived_index', array_merge(
            compact('seasons'),
            $this->portalViewData($request)
        ));
    }

    public function archivedShow(Request $request, RegistrationSeason $season)
    {
        if (!$season->is_archived) {
            return redirect()->route($this->routeName($request, 'registration_seasons.show'), $season);
        }

        $season->load([
            'archivedBy',
            'enrollmentCycles' => function ($query) {
                $query->with(['registrableEntity', 'semester'])
                    ->withCount(['registrations', 'registrableSubjects'])
                    ->orderBy('id');
            },
        ]);

        $cyclesByEntity = $season->enrollmentCycles->keyBy('registrable_entity_id');

        return view('admin.registration_seasons.archived_show', array_merge(
            compact('season', 'cyclesByEntity'),
            $this->portalViewData($request)
        ));
    }

    public function update(Request $request, RegistrationSeason $season)
    {
        if ($season->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'archived_enrollment_cycles.show'), $season)
                ->withErrors(['status' => 'هذه الدورة العامة مؤرشفة وتُعرض للقراءة فقط.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after_or_equal:registration_starts_at',
            'status' => 'required|in:open,closed',
        ]);

        $season->update([
            'name' => $data['name'],
            'code' => $data['code'] ?: null,
            'registration_starts_at' => $data['registration_starts_at'] ?? null,
            'registration_ends_at' => $data['registration_ends_at'] ?? null,
            'status' => $data['status'],
        ]);

        $this->registrationSeasonService->syncSeason($season->fresh(), $this->currentActorId());

        return back()->with('success', 'تم تحديث بيانات الدورة العامة.');
    }

    public function archive(Request $request, RegistrationSeason $season)
    {
        if ($season->is_archived) {
            return back()->withErrors(['status' => 'هذه الدورة العامة مؤرشفة بالفعل.']);
        }

        DB::transaction(function () use ($season) {
            $season->update([
                'status' => 'closed',
                'archived_by' => Auth::id(),
                'archived_at' => now(),
            ]);

            $this->registrationSeasonService->syncSeason($season->fresh(), $this->currentActorId());

            $season->fresh('enrollmentCycles')->enrollmentCycles->each(function ($cycle): void {
                ArchivedEnrollmentCycle::updateOrCreate(
                    ['enrollment_cycle_id' => $cycle->id],
                    [
                        'archived_by' => Auth::id(),
                        'archived_at' => now(),
                    ]
                );
            });
        });

        return redirect()
            ->route($this->routeName($request, 'archived_enrollment_cycles.index'))
            ->with('success', 'تمت أرشفة الدورة العامة ونقلها إلى صفحة المؤرشفات.');
    }

    public function restore(Request $request, RegistrationSeason $season)
    {
        if (!$season->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'registration_seasons.show'), $season)
                ->withErrors(['status' => 'هذه الدورة العامة غير مؤرشفة.']);
        }

        DB::transaction(function () use ($season) {
            $cycleIds = $season->enrollmentCycles()->pluck('id');

            ArchivedEnrollmentCycle::query()
                ->whereIn('enrollment_cycle_id', $cycleIds)
                ->delete();

            $season->update([
                'status' => 'closed',
                'archived_by' => null,
                'archived_at' => null,
            ]);

            $this->registrationSeasonService->syncSeason($season->fresh(), $this->currentActorId());
        });

        return redirect()
            ->route($this->routeName($request, 'enrollment_cycles.index'))
            ->with('success', 'تمت استعادة الدورة العامة. بقيت حالتها مغلقة ويمكنك فتحها يدويًا عند الحاجة.');
    }

    public function destroyArchived(Request $request, RegistrationSeason $season)
    {
        if (!$season->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'registration_seasons.show'), $season)
                ->withErrors(['status' => 'لا يمكن حذف دورة عامة غير مؤرشفة من هذه الصفحة.']);
        }

        DB::transaction(function () use ($season) {
            $cycleIds = $season->enrollmentCycles()->pluck('id');

            if ($cycleIds->isNotEmpty()) {
                Registration::query()
                    ->whereIn('enrollment_cycle_id', $cycleIds)
                    ->delete();
            }

            $season->delete();
        });

        return redirect()
            ->route($this->routeName($request, 'archived_enrollment_cycles.index'))
            ->with('success', 'تم حذف الدورة العامة المؤرشفة نهائيًا مع كل توابعها.');
    }

    public function toggleEntity(Request $request, RegistrationSeason $season, RegistrableEntity $entity)
    {
        if ($season->is_archived) {
            return redirect()
                ->route($this->routeName($request, 'archived_enrollment_cycles.show'), $season)
                ->withErrors(['status' => 'لا يمكن تعديل كيان داخل دورة عامة مؤرشفة.']);
        }

        $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        if ($request->boolean('is_enabled')) {
            $this->registrationSeasonService->enableEntity($season, $entity, $this->currentActorId());

            return back()->with('success', 'تم فتح هذا الكيان داخل الدورة العامة.');
        }

        $this->registrationSeasonService->disableEntity($season, $entity);

        return back()->with('success', 'تم إيقاف فتح هذا الكيان داخل الدورة العامة.');
    }

    private function currentActorId(): ?int
    {
        return auth()->id();
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

    private function routeName(Request $request, string $suffix): string
    {
        return $this->portalViewData($request)['routeBase'] . '.' . $suffix;
    }
}
