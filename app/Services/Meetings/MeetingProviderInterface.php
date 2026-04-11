<?php

namespace App\Services\Meetings;

use App\Models\Doctor;
use App\Models\LiveSession;
use App\Models\Student;

interface MeetingProviderInterface
{
    public function key(): string;

    public function provisionSession(LiveSession $liveSession): LiveSession;

    public function buildEmbedPayload(LiveSession $liveSession, Doctor|Student $actor, string $actorRole): array;

    public function supports(string $feature): bool;
}
