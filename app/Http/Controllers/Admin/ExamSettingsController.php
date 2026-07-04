<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSetting;
use Illuminate\Http\Request;

class ExamSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $settings = ExamSetting::current();

        return view('admin.exams.settings', array_merge(
            compact('settings'),
            $this->portalViewData($request)
        ));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'creation_mode' => 'required|in:random,manual',
        ]);

        ExamSetting::current()->update($data);

        return back()->with('success', 'تم تحديث إعدادات الامتحانات.');
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
