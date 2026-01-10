<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\TrainingProgram;
use App\Models\RegistrationRequest;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /**
     * Show application form for a program/training.
     *
     * URL example: /apply/program/my-program-slug
     */
    public function create($type, $slug)
    {
        if ($type === 'program') {
            $program = Program::where('slug', $slug)->firstOrFail();
            $title = $program->title;
            $id = $program->id;
        } elseif ($type === 'training') {
            $program = TrainingProgram::where('slug', $slug)->firstOrFail();
            $title = $program->title;
            $id = $program->id;
        } else {
            abort(404);
        }

        return view('apply', [
            'program_type' => $type,
            'program_slug' => $slug,
            'program_id' => $id,
            'program_title' => $title,
        ]);
    }

    /**
     * Handle submission: validate then redirect to wa.me with prefilled message.
     * Also save the request to registration_requests table.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'program_type' => 'required|in:program,training',
            'program_id'   => 'required|integer',
            'program_title'=> 'required|string',
            'name'         => 'required|string|max:200',
            'phone'        => 'required|string|max:50',
            'email'        => 'nullable|email|max:200',
            'notes'        => 'nullable|string|max:2000',
        ]);

        // Normalize target whatsapp number provided by you
        $targetNumberRaw = '00963965121776'; // <-- عدّل إن لزم
        $digitsOnly = preg_replace('/\D+/', '', $targetNumberRaw);
        $digitsOnly = ltrim($digitsOnly, '0'); // remove leading zeros if present

        if (empty($digitsOnly)) {
            $digitsOnly = '963000000000';
        }

        // Prepare whatsapp message text
        $messageLines = [
            "طلب تسجيل جديد",
            "البرنامج: " . $data['program_title'] . " (" . strtoupper($data['program_type']) . ")",
            "الاسم: " . $data['name'],
            "الهاتف: " . $data['phone'],
        ];
        if (!empty($data['email'])) {
            $messageLines[] = "الإيميل: " . $data['email'];
        }
        if (!empty($data['notes'])) {
            $messageLines[] = "ملاحظات: " . $data['notes'];
        }
        $messageLines[] = "";
        $messageLines[] = "المصدر: موقع Leaders Institute";

        $text = implode("\n", $messageLines);
        $encoded = urlencode($text);
        $waLink = "https://wa.me/{$digitsOnly}?text={$encoded}";

        // ============================
        // SAVE TO DATABASE (registration_requests)
        // ============================
        try {
            $req = RegistrationRequest::create([
                'program_type'  => $data['program_type'],
                'program_id'    => $data['program_id'],
                'program_title' => $data['program_title'],
                'name'          => $data['name'],
                'phone'         => $data['phone'],
                'email'         => $data['email'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'source'        => 'Website - Leaders Institute',
                'status'        => 'new',
                'meta'          => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $request->headers->get('referer'),
                ],
            ]);
        } catch (\Exception $ex) {
            // في حالة فشل الحفظ، نكتب في اللوج ولكن نكمل للواتساب
            \Log::error('Failed saving registration request: '.$ex->getMessage(), [
                'payload' => $data
            ]);
        }

        // Redirect the user to WhatsApp URL
        return redirect()->away($waLink);
    }
}
