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

    /**
     * When true, doctor live-session pages show long operator tips (Jitsi embed limits, meet.jit.si moderator notes).
     * Default false for production-friendly UI.
     */
    'show_operator_tips' => filter_var(env('MEETING_SHOW_OPERATOR_TIPS', false), FILTER_VALIDATE_BOOLEAN),

    /**
     * TTL for signed LMS URLs that redirect to Jitsi (student/doctor meet launch).
     */
    'meet_launch_url_ttl_minutes' => max(5, (int) env('MEET_LAUNCH_URL_TTL_MINUTES', 45)),

    /*
    |--------------------------------------------------------------------------
    | Jitsi JWT (self-hosted or JaaS only)
    |--------------------------------------------------------------------------
    |
    | meet.jit.si ignores third-party JWTs — to enforce moderator = lecturer only,
    | run your own Jitsi (docker-jitsi-meet) or 8x8 JaaS and set the variables below.
    | jitsi_jwt_sub must match the Jitsi deployment hostname (same idea as JITSI_PUBLIC_DOMAIN).
    |
    */
    'jitsi_jwt_app_id' => env('JITSI_JWT_APP_ID'),
    'jitsi_jwt_secret' => env('JITSI_JWT_APP_SECRET'),
    'jitsi_jwt_sub' => env('JITSI_JWT_SUB'),
    'jitsi_jwt_audience' => env('JITSI_JWT_AUDIENCE', 'jitsi'),
    'jitsi_jwt_ttl_seconds' => (int) env('JITSI_JWT_TTL_SECONDS', 21600),
];
