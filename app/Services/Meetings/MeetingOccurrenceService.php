<?php

namespace App\Services\Meetings;

use App\Models\SectionMeeting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class MeetingOccurrenceService
{
    public function occurrencesForRange(iterable $meetings, Carbon $rangeStart, Carbon $rangeEnd, ?string $timezone = null): Collection
    {
        $timezone ??= config('app.timezone', 'UTC');
        $occurrences = collect();

        foreach ($meetings as $meeting) {
            if (! $meeting instanceof SectionMeeting) {
                continue;
            }

            $meetingStart = Carbon::parse($meeting->start_date, $timezone)->startOfDay();
            $meetingEnd = $meeting->end_date
                ? Carbon::parse($meeting->end_date, $timezone)->startOfDay()
                : $rangeEnd->copy()->startOfDay();

            $cursor = $rangeStart->copy()->startOfDay();
            $windowEnd = $rangeEnd->copy()->startOfDay();

            while ($cursor->lte($windowEnd)) {
                if (
                    $cursor->gte($meetingStart)
                    && $cursor->lte($meetingEnd)
                    && (int) $cursor->dayOfWeek === (int) $meeting->day_of_week
                ) {
                    $occurrences->push($this->buildOccurrence($meeting, $cursor, $timezone));
                }

                $cursor->addDay();
            }
        }

        return $occurrences
            ->sortBy(fn (array $occurrence) => $occurrence['scheduled_starts_at']->timestamp)
            ->values();
    }

    public function occurrenceForDate(SectionMeeting $meeting, string|Carbon $date, ?string $timezone = null): array
    {
        $timezone ??= config('app.timezone', 'UTC');
        $date = $date instanceof Carbon
            ? $date->copy()->setTimezone($timezone)
            : Carbon::parse($date, $timezone);

        if ((int) $date->dayOfWeek !== (int) $meeting->day_of_week) {
            throw new InvalidArgumentException('Occurrence date does not match meeting weekday.');
        }

        $meetingStart = Carbon::parse($meeting->start_date, $timezone)->startOfDay();
        $meetingEnd = $meeting->end_date
            ? Carbon::parse($meeting->end_date, $timezone)->startOfDay()
            : null;

        if ($date->copy()->startOfDay()->lt($meetingStart)) {
            throw new InvalidArgumentException('Occurrence date is before meeting start date.');
        }

        if ($meetingEnd && $date->copy()->startOfDay()->gt($meetingEnd)) {
            throw new InvalidArgumentException('Occurrence date is after meeting end date.');
        }

        return $this->buildOccurrence($meeting, $date, $timezone);
    }

    private function buildOccurrence(SectionMeeting $meeting, Carbon $date, string $timezone): array
    {
        $occurrenceDate = $date->copy()->startOfDay();
        $scheduledStartsAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $occurrenceDate->toDateString() . ' ' . $meeting->starts_at,
            $timezone
        );
        $scheduledEndsAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $occurrenceDate->toDateString() . ' ' . $meeting->ends_at,
            $timezone
        );

        return [
            'section_meeting' => $meeting,
            'section' => $meeting->section,
            'occurrence_date' => $occurrenceDate->toDateString(),
            'scheduled_starts_at' => $scheduledStartsAt,
            'scheduled_ends_at' => $scheduledEndsAt,
        ];
    }
}
