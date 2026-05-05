<?php

namespace App\Services\Meetings;

use App\Models\Doctor;
use App\Models\LiveSession;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class JitsiPublicMeetingProvider implements MeetingProviderInterface
{
    public function key(): string
    {
        return 'jitsi_public';
    }

    public function provisionSession(LiveSession $liveSession): LiveSession
    {
        $payload = $liveSession->provider_payload ?? [];

        if (! $liveSession->provider_room_name) {
            $liveSession->provider_room_name = sprintf(
                'leaders-%s-%s-%s-%s',
                $liveSession->id,
                $liveSession->section_id,
                $liveSession->section_meeting_id,
                Str::lower(Str::random(18))
            );
        }

        $payload['domain'] = config('meetings.jitsi_public_domain', 'meet.jit.si');
        $payload['room_password'] = Arr::get($payload, 'room_password', Str::random(16));

        $liveSession->forceFill([
            'meeting_provider' => $this->key(),
            'provider_payload' => $payload,
        ])->save();

        return $liveSession->fresh();
    }

    public function buildEmbedPayload(LiveSession $liveSession, Doctor|Student $actor, string $actorRole): array
    {
        $payload = $liveSession->provider_payload ?? [];
        $displayName = $actorRole === 'doctor'
            ? $actor->full_name
            : trim(($actor->first_name ?? '').' '.($actor->last_name ?? ''));

        if ($displayName === '') {
            $displayName = $actor->username ?? 'Leaders User';
        }

        $subject = trim(
            ($liveSession->section?->registrableSubject?->name ?? 'محاضرة').
            ' - الشعبة '.
            ($liveSession->section?->name ?? '—')
        );

        $domain = $payload['domain'] ?? config('meetings.jitsi_public_domain', 'meet.jit.si');
        $toolbarButtons = $actorRole === 'doctor'
            ? [
                'microphone',
                'camera',
                'desktop',
                'fullscreen',
                'participants-pane',
                'settings',
                'raisehand',
                'recording',
                'tileview',
                'hangup',
            ]
            : [
                'microphone',
                'camera',
                'fullscreen',
                'settings',
                'raisehand',
                'tileview',
                'hangup',
            ];

        return [
            'domain' => $domain,
            'roomName' => $liveSession->provider_room_name,
            'roomPassword' => $payload['room_password'] ?? null,
            'meetingUrl' => $this->standaloneMeetingUrl(
                $domain,
                $liveSession->provider_room_name,
                $displayName,
                $actor->email ?? null
            ),
            'subject' => $subject,
            'userInfo' => [
                'displayName' => $displayName,
                'email' => $actor->email ?? null,
            ],
            'configOverwrite' => [
                'disableDeepLinking' => true,
                'startWithAudioMuted' => true,
                'startWithVideoMuted' => true,
                'buttonsWithNotifyClick' => [
                    [
                        'key' => 'hangup',
                        'preventExecution' => true,
                    ],
                ],
                'prejoinConfig' => [
                    'enabled' => false,
                ],
                'toolbarButtons' => $toolbarButtons,
            ],
            'interfaceConfigOverwrite' => [
                'MOBILE_APP_PROMO' => false,
            ],
        ];
    }

    /**
     * Opening Jitsi in a new tab (standalone) does not use External API flags; the display name must be in the URL hash.
     *
     * @see https://jitsi.github.io/handbook/docs/user-guide/user-guide-start-a-jitsi-meeting/#passing-parameters-at-url-level
     */
    private function standaloneMeetingUrl(string $domain, string $roomName, string $displayName, ?string $email): string
    {
        $fragments = [
            'config.prejoinConfig.enabled' => 'false',
            'userInfo.displayName' => $displayName,
        ];

        if ($email !== null && $email !== '') {
            $fragments['userInfo.email'] = $email;
        }

        $hash = collect($fragments)
            ->map(static fn (string $value, string $key): string => $key.'='.rawurlencode($value))
            ->implode('&');

        return sprintf('https://%s/%s#%s', $domain, $roomName, $hash);
    }

    public function supports(string $feature): bool
    {
        return in_array($feature, [
            'moderation',
            'local_recording',
            'room_password',
            'embed',
        ], true);
    }
}
