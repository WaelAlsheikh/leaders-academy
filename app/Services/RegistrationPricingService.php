<?php

namespace App\Services;

use App\Models\PricingSetting;
use App\Models\College;
use App\Models\Subject;

class RegistrationPricingService
{
    public function calculate(int $collegeId, array $subjectIds): array
    {
        $pricing = PricingSetting::query()->latest()->first();
        $minSubjects = $pricing?->min_subjects ?? 4;
        $registrationFee = (float) ($pricing?->registration_fee ?? 0);

        $college = College::findOrFail($collegeId);
        $pricePerHour = (float) ($college->price_per_credit_hour ?? 0);

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('college_id', $college->id)
            ->where('is_active', true)
            ->get();

        if ($subjects->count() < $minSubjects) {
            throw new \Exception(
                'الحد الأدنى للتسجيل هو ' . $minSubjects . ' مواد'
            );
        }

        $totalHours = $subjects->sum('credit_hours');
        $subtotal = $totalHours * $pricePerHour;
        $total = $subtotal + $registrationFee;

        return [
            'subjects'        => $subjects,
            'subjects_count'  => $subjects->count(),
            'total_hours'     => $totalHours,
            'price_per_hour'  => $pricePerHour,
            'registration_fee'=> $registrationFee,
            'subtotal'        => $subtotal,
            'total'           => $total,
        ];
    }
}
