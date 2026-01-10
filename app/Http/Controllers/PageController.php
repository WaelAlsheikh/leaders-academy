<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Accreditation;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return view('pages.show', compact('page'));
    }

    public function accreditations()
    {
        $accreditations = Accreditation::all();
        return view('pages.accreditations', compact('accreditations'));
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
