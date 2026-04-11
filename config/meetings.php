<?php

return [
    'default_provider' => env('MEETING_PROVIDER_DEFAULT', 'jitsi_public'),

    'jitsi_public_domain' => env('JITSI_PUBLIC_DOMAIN', 'meet.jit.si'),

    'comment_poll_seconds' => (int) env('LIVE_SESSION_COMMENT_POLL_SECONDS', 3),

    'heartbeat_interval_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_INTERVAL_SECONDS', 12),

    'heartbeat_timeout_seconds' => (int) env('LIVE_SESSION_HEARTBEAT_TIMEOUT_SECONDS', 45),
];
