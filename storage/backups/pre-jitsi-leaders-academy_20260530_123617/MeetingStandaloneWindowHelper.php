<?php

namespace App\Services\Meetings;

class MeetingStandaloneWindowHelper
{
    /**
     * When true: do not embed Jitsi inside the LMS page — open meeting in a separate browser tab/window instead.
     * Required for domains like meet.jit.si that disconnect embedded demos after ~5 minutes.
     *
     * @param  array<string, mixed>|null  $embedPayload  Output of {@see MeetingProviderInterface::buildEmbedPayload}
     */
    public static function shouldUse(?array $embedPayload): bool
    {
        if (! config('meetings.jitsi_prefer_standalone_window')) {
            return false;
        }

        $domain = (string) ($embedPayload['domain'] ?? config('meetings.jitsi_public_domain', ''));
        $domain = strtolower(trim($domain));

        foreach (self::standaloneDomainsNormalized() as $allowed) {
            if ($allowed !== '' && $domain === $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public static function standaloneDomainsNormalized(): array
    {
        $list = config('meetings.jitsi_standalone_window_domains');

        if (is_array($list)) {
            return array_values(array_unique(array_filter(array_map(
                static fn ($d): string => strtolower(trim((string) $d)),
                $list,
            ))));
        }

        return [];
    }
}
