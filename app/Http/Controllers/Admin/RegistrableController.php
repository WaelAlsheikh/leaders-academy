<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\ProgramBranch;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\TrainingProgramBranch;
use Illuminate\Http\Request;

class RegistrableController extends Controller
{
    public function index()
    {
        RegistrableEntity::syncFromSources();

        $entities = RegistrableEntity::query()
            ->orderBy('entity_type')
            ->orderBy('title_snapshot')
            ->get();

        return view('admin.registrables.index', compact('entities'));
    }

    public function updatePrice(Request $request, RegistrableEntity $entity)
    {
        $data = $request->validate([
            'price_per_credit_hour' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $entity->update([
            'price_per_credit_hour' => $data['price_per_credit_hour'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($entity->entity_type === 'college') {
            College::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        } elseif ($entity->entity_type === 'program_branch') {
            ProgramBranch::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        } elseif ($entity->entity_type === 'training_program_branch') {
            TrainingProgramBranch::where('id', $entity->entity_id)
                ->update(['price_per_credit_hour' => $data['price_per_credit_hour']]);
        }

        return back()->with('success', 'تم تحديث الإعدادات');
    }

    public function subjects(RegistrableEntity $entity)
    {
        $subjects = $entity->subjects()->orderBy('name')->get();

        return view('admin.registrables.subjects', compact('entity', 'subjects'));
    }

    public function storeSubject(Request $request, RegistrableEntity $entity)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        RegistrableSubject::create([
            'registrable_entity_id' => $entity->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'تمت إضافة المادة');
    }

    public function updateSubject(Request $request, RegistrableSubject $subject)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'credit_hours' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $subject->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'credit_hours' => $data['credit_hours'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث المادة');
    }

    public function destroySubject(RegistrableSubject $subject)
    {
        if ($subject->enrollmentCycles()->exists() || $subject->registrations()->exists()) {
            return back()->withErrors(['status' => 'لا يمكن حذف مادة مرتبطة بتسجيلات أو دورات']);
        }

        $subject->delete();

        return back()->with('success', 'تم حذف المادة');
    }
}
