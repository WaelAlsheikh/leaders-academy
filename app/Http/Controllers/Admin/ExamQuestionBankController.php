<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\ExamQuestion;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamQuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $collegeId = $request->filled('college_id') ? (int) $request->college_id : null;
        $subjectId = $request->filled('registrable_subject_id') ? (int) $request->registrable_subject_id : null;

        $colleges = College::query()->orderBy('title')->get(['id', 'title']);

        $subjects = collect();
        if ($collegeId) {
            $entityId = RegistrableEntity::query()
                ->where('entity_type', 'college')
                ->where('entity_id', $collegeId)
                ->value('id');

            if ($entityId) {
                $subjects = RegistrableSubject::query()
                    ->where('registrable_entity_id', $entityId)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']);
            }
        }

        $questions = collect();
        $questionsPaginator = null;

        if ($collegeId && $subjectId) {
            $questionsPaginator = ExamQuestion::query()
                ->where('registrable_subject_id', $subjectId)
                ->whereHas('registrableSubject.registrableEntity', function ($query) use ($collegeId) {
                    $query->where('entity_type', 'college')
                        ->where('entity_id', $collegeId);
                })
                ->with(['doctor', 'category', 'choices', 'registrableSubject'])
                ->latest('id')
                ->paginate(25)
                ->withQueryString();

            $questions = $questionsPaginator;
        }

        return view('admin.exams.question_bank.index', array_merge(
            compact('colleges', 'subjects', 'questions', 'questionsPaginator', 'collegeId', 'subjectId'),
            $this->portalViewData($request)
        ));
    }

    public function subjects(Request $request): JsonResponse
    {
        $collegeId = (int) $request->query('college_id');

        if (! $collegeId) {
            return response()->json(['subjects' => []]);
        }

        $entityId = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->where('entity_id', $collegeId)
            ->value('id');

        if (! $entityId) {
            return response()->json(['subjects' => []]);
        }

        $subjects = RegistrableSubject::query()
            ->where('registrable_entity_id', $entityId)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (RegistrableSubject $subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
            ]);

        return response()->json(['subjects' => $subjects]);
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
