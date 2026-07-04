<?php

namespace App\Services\Exams;

use App\Models\Exam;
use Illuminate\Support\Carbon;

class ExamSchedulingService
{
    public function syncStatuses(): void
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        Exam::query()
            ->where('status', 'scheduled')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->update(['status' => 'running']);

        Exam::query()
            ->whereIn('status', ['scheduled', 'running'])
            ->where('ends_at', '<', $now)
            ->update(['status' => 'finished']);
    }

    public function refreshExamStatus(Exam $exam): Exam
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        if ($exam->status === 'scheduled' && $exam->starts_at <= $now && $exam->ends_at >= $now) {
            $exam->forceFill(['status' => 'running'])->save();
        }

        if (in_array($exam->status, ['scheduled', 'running'], true) && $exam->ends_at < $now) {
            $exam->forceFill(['status' => 'finished'])->save();
        }

        return $exam->fresh();
    }

    public function canStudentStart(Exam $exam): bool
    {
        $exam = $this->refreshExamStatus($exam);
        $now = Carbon::now(config('app.timezone', 'UTC'));

        if (! in_array($exam->status, ['scheduled', 'running'], true)) {
            return false;
        }

        if ($exam->starts_at > $now) {
            return false;
        }

        if ($exam->ends_at < $now) {
            return false;
        }

        if (! $exam->allow_late_entry && $exam->status !== 'running' && $exam->starts_at->diffInMinutes($now) > 0) {
            // still ok if within window
        }

        return true;
    }
}
