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
            : trim(($actor->first_name ?? '') . ' ' . ($actor->last_name ?? ''));

        if ($displayName === '') {
            $displayName = $actor->username ?? 'Leaders User';
        }

        $subject = trim(
            ($liveSession->section?->registrableSubject?->name ?? 'محاضرة') .
            ' - الشعبة ' .
            ($liveSession->section?->name ?? '—')
        );

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
            'domain' => $payload['domain'] ?? config('meetings.jitsi_public_domain', 'meet.jit.si'),
            'roomName' => $liveSession->provider_room_name,
            'roomPassword' => $payload['room_password'] ?? null,
            'meetingUrl' => sprintf(
                'https://%s/%s#config.prejoinConfig.enabled=false',
                $payload['domain'] ?? config('meetings.jitsi_public_domain', 'meet.jit.si'),
                $liveSession->provider_room_name
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
