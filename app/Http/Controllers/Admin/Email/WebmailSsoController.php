<?php

namespace App\Http\Controllers\Admin\Email;

use App\Domain\Email\Models\MailAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Future SSO bridge for SnappyMail / custom WebMail.
 * Issues a short-lived signed handshake token; WebMail validates and opens IMAP session.
 */
class WebmailSsoController extends Controller
{
    public function handshake(Request $request, MailAccount $account)
    {
        abort_unless($account->isActive(), 403);

        $token = Str::random(64);
        cache()->put('webmail_sso:'.$token, [
            'mail_account_id' => $account->id,
            'email' => $account->institutional_email,
            'issued_at' => now()->toIso8601String(),
        ], now()->addMinutes(2));

        $webmail = rtrim((string) config('email_module.webmail_url'), '/');
        $redirect = $webmail.'/?sso='.urlencode($token);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'expires_in' => 120,
            'redirect_url' => $redirect,
            'note' => 'SnappyMail plugin should redeem this token via internal API and start IMAP session.',
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);
        $payload = cache()->pull('webmail_sso:'.$data['token']);

        if (! $payload) {
            return response()->json(['ok' => false, 'message' => 'Invalid or expired token'], 401);
        }

        return response()->json([
            'ok' => true,
            'email' => $payload['email'],
            'mail_account_id' => $payload['mail_account_id'],
            // Password is never returned; WebMail should use masteruser/proxyauth or app password vault on mail server.
            'auth_mode' => 'proxyauth_planned',
        ]);
    }
}
