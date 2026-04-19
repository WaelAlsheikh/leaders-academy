<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\StudentRegistrationOtpMail;
use App\Mail\StudentWelcomeMail;
use App\Models\Student;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    private const REGISTRATION_CACHE_PREFIX = 'student_registration_pending:';
    private const REGISTRATION_SESSION_KEY = 'student_registration_pending_key';
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;

    /* ================== Register ================== */

    public function showRegister()
    {
        return view('student.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'first_name_en' => [
                'required',
                'regex:/^[a-zA-Z]{2,}$/'
            ],
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string|min:8',
            'password' => 'required|min:6|confirmed',
        ], [
            'first_name_en.regex' => 'الاسم الإنكليزي يجب أن يحتوي أحرف إنكليزية فقط وبدون فراغات',
        ]);

        $pendingKey = $this->resetPendingRegistration();
        $code = $this->generateVerificationCode();
        $now = now();

        $pendingRegistration = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'first_name_en' => $data['first_name_en'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => $now->copy()->addMinutes(self::OTP_EXPIRY_MINUTES)->getTimestamp(),
            'resend_available_at' => $now->copy()->addSeconds(self::OTP_RESEND_COOLDOWN_SECONDS)->getTimestamp(),
        ];

        $this->pendingRegistrationStore()->put(
            $this->cacheKey($pendingKey),
            $pendingRegistration,
            now()->addMinutes(self::OTP_EXPIRY_MINUTES + 5)
        );

        try {
            $this->sendVerificationCode($pendingRegistration['email'], $code);
        } catch (Throwable $exception) {
            $this->clearPendingRegistration();
            report($exception);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'تعذر إرسال رمز التحقق الآن. يرجى التأكد من إعدادات البريد والمحاولة مرة أخرى.']);
        }

        return redirect()
            ->route('student.register.verify')
            ->with('status', 'أرسلنا رمز تحقق إلى بريدك الإلكتروني. أدخله لإكمال إنشاء الحساب.');
    }

    public function showVerifyRegistration()
    {
        $pendingRegistration = $this->getPendingRegistration();

        if (!$pendingRegistration) {
            return redirect()
                ->route('student.register')
                ->withErrors(['email' => 'ابدأ التسجيل أولًا ثم أدخل رمز التحقق الذي يصل إلى بريدك.']);
        }

        return view('student.auth.verify_email_code', [
            'pendingEmail' => $pendingRegistration['email'],
            'maskedEmail' => $this->maskEmail($pendingRegistration['email']),
            'expiresAt' => $pendingRegistration['expires_at'],
            'resendAvailableAt' => $pendingRegistration['resend_available_at'],
            'remainingAttempts' => max(0, self::OTP_MAX_ATTEMPTS - (int) $pendingRegistration['attempts']),
            'maxAttempts' => self::OTP_MAX_ATTEMPTS,
        ]);
    }

    public function verifyRegistration(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|digits:' . self::OTP_LENGTH,
        ], [
            'verification_code.required' => 'يرجى إدخال رمز التحقق.',
            'verification_code.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
        ]);

        $pendingRegistration = $this->getPendingRegistration();

        if (!$pendingRegistration) {
            return redirect()
                ->route('student.register')
                ->withErrors(['email' => 'انتهت جلسة التسجيل المؤقتة. يرجى تعبئة البيانات من جديد.']);
        }

        if ($this->hasCodeExpired($pendingRegistration)) {
            return back()->withErrors([
                'verification_code' => 'انتهت صلاحية رمز التحقق. يمكنك طلب رمز جديد للمتابعة.',
            ]);
        }

        if ((int) $pendingRegistration['attempts'] >= self::OTP_MAX_ATTEMPTS) {
            return back()->withErrors([
                'verification_code' => 'تم تجاوز الحد الأقصى للمحاولات. اطلب رمزًا جديدًا للمتابعة.',
            ]);
        }

        if (!Hash::check($request->verification_code, $pendingRegistration['code_hash'])) {
            $pendingRegistration['attempts'] = (int) $pendingRegistration['attempts'] + 1;
            $this->storePendingRegistration($pendingRegistration);

            $remainingAttempts = max(0, self::OTP_MAX_ATTEMPTS - (int) $pendingRegistration['attempts']);
            $message = $remainingAttempts > 0
                ? "رمز التحقق غير صحيح. المحاولات المتبقية: {$remainingAttempts}."
                : 'تم تجاوز الحد الأقصى للمحاولات. اطلب رمزًا جديدًا للمتابعة.';

            return back()->withErrors(['verification_code' => $message]);
        }

        if (Student::where('email', $pendingRegistration['email'])->exists()) {
            $this->clearPendingRegistration();

            return redirect()
                ->route('student.register')
                ->withErrors(['email' => 'هذا البريد مستخدم مسبقًا. يرجى استخدام بريد آخر أو تسجيل الدخول.']);
        }

        $student = Student::create([
            'first_name' => $pendingRegistration['first_name'],
            'last_name' => $pendingRegistration['last_name'],
            'first_name_en' => $pendingRegistration['first_name_en'],
            'username' => $this->generateUniqueUsername($pendingRegistration['first_name_en']),
            'email' => $pendingRegistration['email'],
            'email_verified_at' => now(),
            'phone' => $pendingRegistration['phone'],
            'is_active' => true,
            'password' => $pendingRegistration['password'],
            'acceptance_number' => $this->generateAcceptanceNumber(),
        ]);

        $this->clearPendingRegistration();

        $welcomeEmailSent = true;

        try {
            Mail::to($student->email)->send(new StudentWelcomeMail($student));
        } catch (Throwable $exception) {
            $welcomeEmailSent = false;
            report($exception);
        }

        return view('student.auth.register_success', compact('student', 'welcomeEmailSent'));
    }

    public function resendRegistrationCode()
    {
        $pendingRegistration = $this->getPendingRegistration();

        if (!$pendingRegistration) {
            return redirect()
                ->route('student.register')
                ->withErrors(['email' => 'لا يوجد تسجيل معلق لإعادة إرسال رمز التحقق له.']);
        }

        if ((int) $pendingRegistration['resend_available_at'] > now()->getTimestamp()) {
            $seconds = (int) $pendingRegistration['resend_available_at'] - now()->getTimestamp();

            return back()->withErrors([
                'verification_code' => "يمكنك إعادة إرسال الرمز بعد {$seconds} ثانية.",
            ]);
        }

        $code = $this->generateVerificationCode();
        $now = now();

        $pendingRegistration['code_hash'] = Hash::make($code);
        $pendingRegistration['attempts'] = 0;
        $pendingRegistration['expires_at'] = $now->copy()->addMinutes(self::OTP_EXPIRY_MINUTES)->getTimestamp();
        $pendingRegistration['resend_available_at'] = $now->copy()->addSeconds(self::OTP_RESEND_COOLDOWN_SECONDS)->getTimestamp();

        $this->storePendingRegistration($pendingRegistration);

        try {
            $this->sendVerificationCode($pendingRegistration['email'], $code);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'verification_code' => 'تعذر إعادة إرسال الرمز الآن. يرجى المحاولة بعد قليل.',
            ]);
        }

        return back()->with('status', 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني.');
    }

    public function cancelRegistration(Request $request)
    {
        $this->clearPendingRegistration();

        return redirect()
            ->route('student.register')
            ->with('status', 'تم إلغاء التحقق الحالي. يمكنك تعديل البيانات والبدء من جديد.');
    }

    /* ================== Login ================== */

    public function showLogin()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'student_username' => 'required|string',
            'student_password' => 'required|string',
        ]);

        if (Auth::guard('student')->attempt([
            'username' => $credentials['student_username'],
            'password' => $credentials['student_password'],
        ])) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'login' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }

    private function generateUniqueUsername(string $baseName): string
    {
        $base = strtolower($baseName);

        do {
            $username = $base . '_' . random_int(100000, 999999);
        } while (Student::where('username', $username)->exists());

        return $username;
    }

    private function generateAcceptanceNumber(): string
    {
        do {
            $acceptanceNumber = strtoupper(Str::random(10));
        } while (Student::where('acceptance_number', $acceptanceNumber)->exists());

        return $acceptanceNumber;
    }

    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    private function sendVerificationCode(string $email, string $code): void
    {
        Mail::to($email)->send(new StudentRegistrationOtpMail($code, self::OTP_EXPIRY_MINUTES));
    }

    private function hasCodeExpired(array $pendingRegistration): bool
    {
        return (int) $pendingRegistration['expires_at'] < now()->getTimestamp();
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email);

        if (mb_strlen($name) <= 2) {
            $maskedName = mb_substr($name, 0, 1) . '*';
        } else {
            $maskedName = mb_substr($name, 0, 2) . str_repeat('*', max(2, mb_strlen($name) - 2));
        }

        return $maskedName . '@' . $domain;
    }

    private function resetPendingRegistration(): string
    {
        $this->clearPendingRegistration();

        $pendingKey = (string) Str::uuid();
        session([self::REGISTRATION_SESSION_KEY => $pendingKey]);

        return $pendingKey;
    }

    private function getPendingRegistration(): ?array
    {
        $pendingKey = session(self::REGISTRATION_SESSION_KEY);

        if (!$pendingKey) {
            return null;
        }

        return $this->pendingRegistrationStore()->get($this->cacheKey($pendingKey));
    }

    private function storePendingRegistration(array $pendingRegistration): void
    {
        $pendingKey = session(self::REGISTRATION_SESSION_KEY);

        if (!$pendingKey) {
            return;
        }

        $ttlMinutes = max(
            1,
            (int) ceil(max(60, ((int) $pendingRegistration['expires_at'] - now()->getTimestamp()) + 300) / 60)
        );

        $this->pendingRegistrationStore()->put(
            $this->cacheKey($pendingKey),
            $pendingRegistration,
            now()->addMinutes($ttlMinutes)
        );
    }

    private function clearPendingRegistration(): void
    {
        $pendingKey = session(self::REGISTRATION_SESSION_KEY);

        if ($pendingKey) {
            $this->pendingRegistrationStore()->forget($this->cacheKey($pendingKey));
            session()->forget(self::REGISTRATION_SESSION_KEY);
        }
    }

    private function cacheKey(string $pendingKey): string
    {
        return self::REGISTRATION_CACHE_PREFIX . $pendingKey;
    }

    private function pendingRegistrationStore(): CacheRepository
    {
        return Cache::store();
    }
}
