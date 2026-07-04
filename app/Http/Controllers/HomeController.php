<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Accreditation;
use App\Models\AccreditationSection;
use App\Models\College;
use App\Models\Gallery;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Setting;
use App\Models\TrainingProgram;

class HomeController extends Controller
{
    public function index()
    {
        $about = About::query()->orderByDesc('updated_at')->orderByDesc('id')->first();
        $trainingPrograms = TrainingProgram::orderBy('id', 'asc')->get();
        $colleges = College::orderBy('id', 'asc')->get();
        $universityPrograms = Program::orderBy('id', 'asc')->get();
        $accreditations = Accreditation::all();
        $partners = Partner::all();
        $galleries = Gallery::take(4)->get();
        $sections = AccreditationSection::orderBy('order', 'asc')->get();

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

    public function programs()
    {
        return view('programs', ['programs' => Program::all()]);
    }

    public function programDetails($id)
    {
        return view('program-details', ['program' => Program::findOrFail($id)]);
    }

    public function contact()
    {
        return view('contact', ['settings' => Setting::pluck('value', 'key')]);
    }

    public function virtualUniversity()
    {
        return view('virtual_university.virtual-university');
    }
}
