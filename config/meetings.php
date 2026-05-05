<?php

return [
    'default_provider' => env('MEETING_PROVIDER_DEFAULT', 'jitsi_public'),

    'jitsi_public_domain' => env('JITSI_PUBLIC_DOMAIN', 'meet.jit.si'),

    /**
     * If true and the resolved Jitsi host is listed in {@see jitsi_standalone_window_domains},
     * the UI opens the conference in a new browser tab instead of an embedded iframe.
     * avoids meet.jit.si’s ~5 minute embedded-session limit (“demo embedding only”).
     */
    'jitsi_prefer_standalone_window' => filter_var(env('JITSI_PREFER_STANDALONE_WINDOW', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * Comma-separated hostnames (without https://) that must use standalone mode when the flag above is on.
     * Self-hosted domains are typically omitted here so iframe embedding keeps working inside the LMS.
     */
    'jitsi_standalone_window_domains' => array_values(array_unique(array_filter(array_map(
        static fn (string $chunk): string => strtolower(trim($chunk)),
        explode(',', (string) env('JITSI_STANDALONE_WINDOW_DOMAINS', 'meet.jit.si'))
    )))),

    'comment_poll_seconds' => (int) env('LIVE_SESSION_COMMENT_POLL_SECONDS', 3),

    'heartbeat_interval_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_INTERVAL_SECONDS', 12),

    'heartbeat_timeout_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_TIMEOUT_SECONDS', 45),
];
