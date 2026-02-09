<?php

namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\PricingSetting;
use App\Models\Registration;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentRegistrationController extends Controller
{
    public function create()
    {
        $colleges = College::with('subjects')->get();
        $pricing = PricingSetting::query()->latest()->first();
        $minSubjects = $pricing?->min_subjects ?? 4;
        $registrationFee = (float) ($pricing?->registration_fee ?? 0);

        return view('student.registration.create', compact(
            'colleges',
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
            'college_id' => 'required|integer|exists:colleges,id',
            'subjects' => 'required|array|min:' . $minSubjects,
            'subjects.*' => 'required|integer|exists:subjects,id',
        ]);

        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        $subjectIds = array_values(array_unique($data['subjects']));

        $college = College::findOrFail($data['college_id']);

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('college_id', $college->id)
            ->where('is_active', true)
            ->get();

        if ($subjects->count() !== count($subjectIds)) {
            return back()
                ->withInput()
                ->withErrors(['subjects' => 'يجب اختيار مواد صحيحة من نفس الكلية.']);
        }

        $pricePerHour = (float) ($college->price_per_credit_hour ?? 0);
        $totalHours = (int) $subjects->sum('credit_hours');
        $subtotalAmount = $totalHours * $pricePerHour;
        $totalAmount = $subtotalAmount + $registrationFee;

        DB::transaction(function () use (
            $student,
            $college,
            $subjects,
            $pricePerHour,
            $totalHours,
            $subtotalAmount,
            $registrationFee,
            $totalAmount
        ) {
            $registration = Registration::create([
                'student_id' => $student->id,
                'college_id' => $college->id,
                'status' => 'pending',
                'subjects_count' => $subjects->count(),
                'total_hours' => $totalHours,
                'subtotal_amount' => $subtotalAmount,
                'registration_fee' => $registrationFee,
                'total_amount' => $totalAmount,
            ]);

            foreach ($subjects as $subject) {
                $registration->subjects()->attach($subject->id, [
                    'credit_hours' => $subject->credit_hours,
                    'price_per_hour' => $pricePerHour,
                    'total_price' => $subject->credit_hours * $pricePerHour,
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

        $registrations = Registration::with(['college', 'subjects'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return view('student.registration.index', compact('registrations'));
    }
}
