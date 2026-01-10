<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\About;
use App\Models\Program;
use App\Models\Accreditation;
use App\Models\Partner;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\TrainingProgram;
use App\Models\College;
use Illuminate\Support\Facades\View;
use App\Models\AccreditationSection;

class HomeController extends Controller
{
    public function __construct()
    {
        // مشاركة جميع البرامج مع كل الصفحات (layout)
        $allPrograms = Program::orderBy('id', 'asc')->get();
        View::share('allPrograms', $allPrograms);
    }

    // الصفحة الرئيسية
    public function index()
    {
        $about = About::first();

        // جلب البرامج التدريبية — الآن من الأقدم إلى الأحدث (asc)
        $trainingPrograms = TrainingProgram::orderBy('id', 'asc')->get();

        // جلب الكليات — الآن من الأقدم إلى الأحدث (asc)
        $colleges = College::orderBy('id', 'asc')->get();

        // جلب "برامج الجامعة" — من الأقدم إلى الأحدث
        $universityPrograms = Program::orderBy('id', 'asc')->get();

        $accreditations = Accreditation::all();
        $partners = Partner::all();
        $galleries = Gallery::take(4)->get();
    
        $sections = AccreditationSection::orderBy('order','asc')->get();

        return view('home', compact(
            'about',
            'trainingPrograms',
            'colleges',
            'universityPrograms',
            'accreditations',
            'partners',
            'galleries',
            'sections'
        ));
    }

    // بقية الدوال تبقى كما هي...
    public function programs()
    {
        $programs = Program::all();
        return view('programs', compact('programs'));
    }

    public function programDetails($id)
    {
        $program = Program::findOrFail($id);
        return view('program-details', compact('program'));
    }

    public function contact()
    {
        $settings = Setting::pluck('value', 'key');
        return view('contact', compact('settings'));
    }

    public function virtualUniversity()
    {
        return view('virtual_university.virtual-university');
    }
}
