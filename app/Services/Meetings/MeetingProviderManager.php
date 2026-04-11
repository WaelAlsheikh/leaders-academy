<?php

namespace App\Services\Meetings;

use RuntimeException;

class MeetingProviderManager
{
    public function for(?string $provider = null): MeetingProviderInterface
    {
        $provider ??= config('meetings.default_provider', 'jitsi_public');

        return match ($provider) {
            'jitsi_public' => app(JitsiPublicMeetingProvider::class),
            default => throw new RuntimeException("Unsupported meeting provider [{$provider}]"),
        };
    }
}
