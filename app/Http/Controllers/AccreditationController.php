<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accreditation;
use App\Models\AccreditationSection;

class AccreditationController extends Controller
{
    public function index()
    {
        // جلب جميع الاعتمادات من قاعدة البيانات
        $accreditations = Accreditation::all();

        // جلب أقسام العرض (مرتّبة)
        $sections = AccreditationSection::orderBy('order','asc')->get();

        // تمريرهم إلى صفحة العرض
        return view('accreditations.index', compact('accreditations','sections'));
    }
}
