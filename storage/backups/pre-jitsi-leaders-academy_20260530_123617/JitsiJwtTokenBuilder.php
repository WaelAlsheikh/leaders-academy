<?php

namespace App\Services\Meetings;

use App\Models\Doctor;
use App\Models\Student;

/**
 * HS256 JWT for Jitsi token authentication (self-hosted Prosody or 8x8 JaaS).
 * When configured, the "moderator" claim is true only for doctors so students cannot take host controls.
 *
 * @see https://github.com/jitsi/docker-jitsi-meet/blob/master/resources/README.md
 */
final class JitsiJwtTokenBuilder
{
    public static function isEnabled(): bool
    {
        $id = (string) config('meetings.jitsi_jwt_app_id', '');
        $secret = (string) config('meetings.jitsi_jwt_secret', '');
        $sub = (string) config('meetings.jitsi_jwt_sub', '');

        return $id !== '' && $secret !== '' && $sub !== '';
    }

    /**
     * @param  'doctor'|'student'  $actorRole
     */
    public static function issue(
        Doctor|Student $actor,
        string $actorRole,
        string $roomName,
        string $displayName,
        ?string $email
    ): ?string {
        if (! self::isEnabled()) {
            return null;
        }

        // meet.jit.si does not accept application-issued JWTs; tokens would break joins.
        if (strtolower((string) config('meetings.jitsi_public_domain', '')) === 'meet.jit.si') {
            return null;
        }

        $now = time();
        $ttl = max(300, (int) config('meetings.jitsi_jwt_ttl_seconds', 21600));
        $isModerator = $actorRole === 'doctor';
        $actorKey = $actorRole === 'doctor'
            ? 'doctor-'.$actor->getKey()
            : 'student-'.$actor->getKey();

        $payload = [
            'iss' => (string) config('meetings.jitsi_jwt_app_id'),
            'aud' => (string) config('meetings.jitsi_jwt_audience', 'jitsi'),
            'sub' => (string) config('meetings.jitsi_jwt_sub'),
            'room' => $roomName,
            'exp' => $now + $ttl,
            'nbf' => $now - 30,
            'context' => [
                'user' => [
                    'name' => $displayName,
                    'email' => $email ?? '',
                    'avatar' => '',
                    'id' => $actorKey,
                    // Jitsi / Prosody historically expect string booleans in the user block.
                    'moderator' => $isModerator ? 'true' : 'false',
                ],
                'features' => [
                    'livestreaming' => 'false',
                    'recording' => $isModerator ? 'true' : 'false',
                    'transcription' => 'false',
                    'outbound-call' => 'false',
                ],
            ],
        ];

        return self::signHs256($payload, (string) config('meetings.jitsi_jwt_secret'));
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function signHs256(array $payload, string $secret): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE)),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }
}
