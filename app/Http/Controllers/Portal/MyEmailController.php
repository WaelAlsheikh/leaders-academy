<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Email\Models\MailAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MyEmailController extends Controller
{
    public function show(Request $request)
    {
        $portal = $this->portal($request);
        $identity = $this->identity($portal);
        $account = $identity->mailAccount()?->load(['aliases', 'mailbox', 'domain']);

        $data = [
            'identity' => $identity,
            'account' => $account,
            'webmailUrl' => rtrim((string) config('email_module.webmail_url'), '/'),
            'portal' => $portal,
            'routeBase' => $portal,
        ];

        if ($portal === 'admin') {
            return view('admin.email.me.show', array_merge($data, [
                'portalContext' => 'admin',
                'layout' => 'voyager::master',
                'hideNavbar' => false,
                'bodyClass' => '',
            ]));
        }

        return view("{$portal}.email.show", $data);
    }

    public function openWebmail(Request $request)
    {
        $portal = $this->portal($request);
        $identity = $this->identity($portal);
        $account = $identity->mailAccount;

        abort_unless($account instanceof MailAccount && $account->isActive(), 403, 'صندوق البريد غير مفعّل بعد.');

        $token = Str::random(64);
        cache()->put('webmail_sso:'.$token, [
            'mail_account_id' => $account->id,
            'email' => $account->institutional_email,
            'issued_at' => now()->toIso8601String(),
            'owner_type' => $identity->getMorphClass(),
            'owner_id' => $identity->getKey(),
        ], now()->addMinutes(2));

        $webmail = rtrim((string) config('email_module.webmail_url'), '/');

        return redirect()->away($webmail.'/?sso='.urlencode($token));
    }

    private function portal(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return match (true) {
            str_starts_with($name, 'student.') => 'student',
            str_starts_with($name, 'doctor.') => 'doctor',
            str_starts_with($name, 'employee.') => 'employee',
            default => 'admin',
        };
    }

    private function identity(string $portal): object
    {
        $guard = match ($portal) {
            'student' => 'student',
            'doctor' => 'doctor',
            'employee' => 'employee',
            default => 'web',
        };

        $user = Auth::guard($guard)->user();
        abort_unless($user, 401);

        return $user;
    }
}
