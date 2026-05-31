<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$domain = config('meetings.jitsi_public_domain');
$embed = config('meetings.jitsi_embed_enabled');
$standalone = config('meetings.jitsi_standalone_window_domains');
$shouldUse = App\Services\Meetings\MeetingStandaloneWindowHelper::shouldUse(['domain' => $domain]);

echo "domain={$domain}\n";
echo 'embed='.($embed ? 'true' : 'false')."\n";
echo 'standalone_domains='.json_encode($standalone)."\n";
echo 'shouldUseStandalone='.($shouldUse ? 'true' : 'false')."\n";

$provider = app(App\Services\Meetings\JitsiPublicMeetingProvider::class);
$session = new App\Models\LiveSession([
    'id' => 999,
    'section_id' => 1,
    'section_meeting_id' => 1,
    'provider_room_name' => 'leaders-test-room',
    'provider_payload' => ['domain' => 'meet.jit.si', 'room_password' => 'test'],
]);

$doctor = new App\Models\Doctor([
    'id' => 1,
    'full_name' => 'Test Doctor',
    'email' => 'doctor@test.local',
    'username' => 'doctor',
]);

$payload = $provider->buildEmbedPayload($session, $doctor, 'doctor');

echo 'payload_domain='.$payload['domain']."\n";
echo 'meeting_url='.$payload['meetingUrl']."\n";
echo 'uses_leaders_domain='.(str_contains($payload['meetingUrl'], 'meet.leaders-academy.net') ? 'true' : 'false')."\n";
