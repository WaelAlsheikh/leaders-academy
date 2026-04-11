<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\RegistrableEntity;
use App\Models\Subject;
use App\Services\CollegeSubjectSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollegeSubjectController extends Controller
{
    public function __construct(
        private readonly CollegeSubjectSyncService $collegeSubjectSyncService
    ) {
    }

    // عرض جميع الكليات
    public function colleges(Request $request)
    {
        $colleges = College::query()
            ->withCount('subjects')
            ->orderBy('title')
            ->get();

        return view('admin.colleges.index', array_merge(
            compact('colleges'),
            $this->portalViewData($request)
        ));
    }

    // عرض مواد كلية محددة
    public function subjects(Request $request, College $college)
    {
        $this->collegeSubjectSyncService->ensureCollegeEntity($college);
        $college->subjects()->get()->each(function (Subject $subject) {
            $this->collegeSubjectSyncService->syncLegacySubject($subject);
        });

        $subjects = $college->subjects()
            ->with('registrableSubject')
            ->orderBy('name')
            ->get();

        return view('admin.subjects.index', array_merge(
            compact('college', 'subjects'),
            $this->portalViewData($request)
        ));
    }

    public function storeCollege(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'long_description' => 'nullable|string',
            'price_per_credit_hour' => 'required|numeric|min:0',
        ]);

        $college = College::create([
            'title' => $data['title'],
            'slug' => $this->uniqueCollegeSlug($data['title']),
            'short_description' => $data['short_description'] ?? null,
            'long_description' => $data['long_description'] ?? null,
            'price_per_credit_hour' => $data['price_per_credit_hour'],
        ]);

        $this->collegeSubjectSyncService->ensureCollegeEntity($college);

        return back()->with('success', 'تمت إضافة الكلية بنجاح');
    }

    public function updateCollege(Request $request, College $college)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'long_description' => 'nullable|string',
            'price_per_credit_hour' => 'required|numeric|min:0',
        ]);

        $college->update([
            'title' => $data['title'],
            'slug' => $this->uniqueCollegeSlug($data['title'], $college),
            'short_description' => $data['short_description'] ?? null,
            'long_description' => $data['long_description'] ?? null,
            'price_per_credit_hour' => $data['price_per_credit_hour'],
        ]);

        $this->collegeSubjectSyncService->ensureCollegeEntity($college->fresh());

        return back()->with('success', 'تم تحديث بيانات الكلية');
    }

    public function destroyCollege(College $college)
    {
        $collegeEntity = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->where('entity_id', $college->id)
            ->first();

        if (
            $college->enrollmentCycles()->exists()
            || $college->semesters()->exists()
            || ($collegeEntity && (
                $collegeEntity->enrollmentCycles()->exists()
                || $collegeEntity->registrations()->exists()
            ))
        ) {
            return back()->withErrors([
                'status' => 'لا يمكن حذف كلية مرتبطة بدورات تسجيل أو فصول أو تسجيلات طلابية.',
            ]);
        }

        $college->loadMissing('subjects.registrableSubject');

        foreach ($college->subjects as $subject) {
            $registrableSubject = $subject->registrableSubject;

            if (
                $registrableSubject
                && (
                    $registrableSubject->enrollmentCycles()->exists()
                    || $registrableSubject->registrations()->exists()
                    || $registrableSubject->classSections()->exists()
                    || $registrableSubject->semesters()->exists()
                )
            ) {
                return back()->withErrors([
                    'status' => 'لا يمكن حذف الكلية لأن إحدى موادها مرتبطة بدورات أو تسجيلات أو شعب دراسية.',
                ]);
            }
        }

        DB::transaction(function () use ($college) {
            $college->subjects->each(function (Subject $subject) {
                $this->collegeSubjectSyncService->deleteLegacyAndSyncedSubject($subject);
            });

            $college->delete();
        });

        return back()->with('success', 'تم حذف الكلية بنجاح');
    }

    // إضافة مادة
    public function store(Request $request, College $college)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $this->collegeSubjectSyncService->createLegacyAndSync($college, [
            'name' => $request->name,
            'code' => $request->code,
            'credit_hours' => $request->credit_hours,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تمت إضافة المادة بنجاح');
    }

    // تعديل مادة
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code,' . $subject->id,
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $this->collegeSubjectSyncService->updateLegacyAndSync($subject, [
            'name' => $request->name,
            'code' => $request->code,
            'credit_hours' => $request->credit_hours,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث المادة');
    }

    // حذف مادة
    public function destroy(Subject $subject)
    {
        $registrableSubject = $subject->registrableSubject;

        if (
            $registrableSubject
            && (
                $registrableSubject->enrollmentCycles()->exists()
                || $registrableSubject->registrations()->exists()
                || $registrableSubject->classSections()->exists()
                || $registrableSubject->semesters()->exists()
            )
        ) {
            return back()->withErrors(['status' => 'لا يمكن حذف مادة مرتبطة بتسجيلات أو دورات أو شعب']);
        }

        $this->collegeSubjectSyncService->deleteLegacyAndSyncedSubject($subject);

        return back()->with('success', 'تم حذف المادة');
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

    private function uniqueCollegeSlug(string $title, ?College $ignore = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : trim($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            College::query()
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
