<?php

return [
    'default_provider' => env('MEETING_PROVIDER_DEFAULT', 'jitsi_public'),

    /*
    |--------------------------------------------------------------------------
    | Jitsi domain
    |--------------------------------------------------------------------------
    |
    | JITSI_PUBLIC_DOMAIN is the primary setting. JITSI_DOMAIN is supported as
    | a backward-compatible alias.
    |
    */
    'jitsi_public_domain' => env('JITSI_PUBLIC_DOMAIN', env('JITSI_DOMAIN', 'meet.leaders-academy.net')),

    /**
     * When true, Jitsi runs inside the LMS page via JitsiMeetExternalAPI (iframe).
     * Standalone tab mode is used only for hosts listed in jitsi_standalone_window_domains.
     */
    'jitsi_embed_enabled' => filter_var(env('JITSI_EMBED_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * Legacy flag: when jitsi_embed_enabled is false, and this is true, domains listed
     * below open in a separate browser tab instead of an embedded iframe.
     */
    'jitsi_prefer_standalone_window' => filter_var(env('JITSI_PREFER_STANDALONE_WINDOW', false), FILTER_VALIDATE_BOOLEAN),

    /**
     * Comma-separated hostnames (without https://) that must use standalone mode.
     * Leave empty for self-hosted servers that support iframe embedding (recommended).
     */
    'jitsi_standalone_window_domains' => array_values(array_unique(array_filter(array_map(
        static fn (string $chunk): string => strtolower(trim($chunk)),
        explode(',', (string) env('JITSI_STANDALONE_WINDOW_DOMAINS', ''))
    )))),

    'comment_poll_seconds' => (int) env('LIVE_SESSION_COMMENT_POLL_SECONDS', 3),

    'heartbeat_interval_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_INTERVAL_SECONDS', 12),

    'heartbeat_timeout_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_TIMEOUT_SECONDS', 45),

    /**
     * When true, doctor live-session pages show long operator tips about Jitsi hosting.
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
    | Set JITSI_JWT_* on your own Jitsi deployment to enforce moderator = lecturer only.
    | jitsi_jwt_sub must match the Jitsi deployment hostname (same idea as JITSI_PUBLIC_DOMAIN).
    |
    */
    'jitsi_jwt_app_id' => env('JITSI_JWT_APP_ID'),
    'jitsi_jwt_secret' => env('JITSI_JWT_APP_SECRET'),
    'jitsi_jwt_sub' => env('JITSI_JWT_SUB'),
    'jitsi_jwt_audience' => env('JITSI_JWT_AUDIENCE', 'jitsi'),
    'jitsi_jwt_ttl_seconds' => (int) env('JITSI_JWT_TTL_SECONDS', 21600),

    /*
    |--------------------------------------------------------------------------
    | Local recording (doctor embedded Jitsi sessions)
    |--------------------------------------------------------------------------
    */
    'local_recording_auto_start' => filter_var(env('LIVE_SESSION_AUTO_LOCAL_RECORDING', true), FILTER_VALIDATE_BOOLEAN),

    'local_recording_target_video_kbps' => max(50, (int) env('LIVE_SESSION_RECORDING_VIDEO_KBPS', 100)),

    'local_recording_target_audio_kbps' => max(32, (int) env('LIVE_SESSION_RECORDING_AUDIO_KBPS', 64)),

    'local_recording_output_height' => max(240, (int) env('LIVE_SESSION_RECORDING_HEIGHT', 480)),
];
