<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use App\Models\ClassSection;

use App\Models\Exam;

use App\Services\Exams\ExamCreationService;

use App\Services\Exams\ExamQuestionBankQueryService;

use App\Services\Exams\ExamSchedulingService;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;



class ExamController extends Controller

{

    public function __construct(

        private readonly ExamCreationService $creationService,

        private readonly ExamSchedulingService $schedulingService,

        private readonly ExamQuestionBankQueryService $bankQuery,

    ) {}



    public function index(Request $request)

    {

        $this->schedulingService->syncStatuses();



        $exams = Exam::query()

            ->with(['registrableSubject', 'classSection', 'doctor', 'quizQuestions'])

            ->latest('id')

            ->paginate(20);



        return view('admin.exams.index', array_merge(

            compact('exams'),

            $this->portalViewData($request)

        ));

    }



    public function create(Request $request)

    {

        $sections = ClassSection::query()

            ->with(['registrableSubject', 'doctor', 'semester'])

            ->withCount(['students' => fn ($q) => $q->where('student_sections.status', 'active')])

            ->whereNotNull('registrable_subject_id')

            ->whereNotNull('doctor_id')

            ->orderByDesc('id')

            ->get();



        return view('admin.exams.create', array_merge(

            compact('sections'),

            $this->portalViewData($request)

        ));

    }



    public function sectionContext(Request $request, ClassSection $section): JsonResponse

    {

        return response()->json($this->bankQuery->sectionContext($section));

    }



    public function store(Request $request)

    {

        $data = $request->validate([

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'class_section_id' => 'required|exists:class_sections,id',

            'question_count' => 'required|integer|min:1|max:200',

            'category_ids' => 'nullable|array',

            'category_ids.*' => 'integer|exists:exam_question_categories,id',

            'question_types' => 'nullable|array',

            'question_types.*' => 'in:single_choice,multiple_choice,essay',

            'exam_date' => 'required|date',

            'starts_at' => 'required|date',

            'ends_at' => 'required|date|after:starts_at',

            'duration_minutes' => 'required|integer|min:5|max:480',

            'allow_late_entry' => 'nullable|boolean',

        ], [

            'ends_at.after' => 'وقت نهاية الامتحان يجب أن يكون بعد وقت البداية.',

            'class_section_id.required' => 'يجب اختيار الشعبة.',

            'question_count.required' => 'يجب تحديد عدد الأسئلة.',

        ]);



        $data['allow_late_entry'] = $request->boolean('allow_late_entry');



        try {

            $exam = $this->creationService->createRandomDraft($data, Auth::guard('web')->user());

        } catch (ValidationException $exception) {

            return back()->withErrors($exception->errors())->withInput();

        }



        $base = $this->portalViewData($request)['routeBase'];



        return redirect()

            ->route($base.'.exams.show', $exam)

            ->with('success', 'تم إنشاء الامتحان وتوليد الأسئلة من بنك الدكتور للمادة المحددة.');

    }



    public function show(Request $request, Exam $exam)

    {

        $exam->load(['registrableSubject', 'classSection', 'doctor', 'quizQuestions.choices', 'quizQuestions.question', 'grades.student']);



        return view('admin.exams.show', array_merge(

            compact('exam'),

            $this->portalViewData($request)

        ));

    }



    public function regenerate(Request $request, Exam $exam)

    {

        try {

            $this->creationService->regenerateRandomQuestions($exam);

        } catch (ValidationException $exception) {

            return back()->withErrors($exception->errors());

        }



        return back()->with('success', 'تم إعادة توليد الأسئلة من نفس بنك المادة والفلاتر.');

    }



    public function syncChoices(Request $request, Exam $exam)

    {

        $synced = $this->creationService->syncQuizChoicesFromBank($exam);



        return back()->with(

            'success',

            $synced > 0

                ? "تمت مزامنة خيارات {$synced} سؤالاً من بنك الأسئلة."

                : 'لا توجد أسئلة تحتاج مزامنة، أو بنك الأسئلة لا يحتوي على خيارات لهذه الأسئلة.'

        );

    }



    public function approve(Request $request, Exam $exam)

    {

        try {

            $this->creationService->approveRandomQuestions($exam);

        } catch (ValidationException $exception) {

            return back()->withErrors($exception->errors());

        }



        return back()->with('success', 'تم اعتماد أسئلة الامتحان.');

    }



    public function schedule(Request $request, Exam $exam)

    {

        try {

            $this->creationService->schedule($exam);

        } catch (ValidationException $exception) {

            return back()->withErrors($exception->errors());

        }



        return back()->with('success', 'تم جدولة الامتحان. سيظهر للطلاب المسجلين في الشعبة.');

    }



    public function archive(Request $request, Exam $exam)

    {

        $exam->forceFill(['status' => 'archived'])->save();



        return back()->with('success', 'تم أرشفة الامتحان.');

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


