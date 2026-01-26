<?php

namespace App\Services;

use App\Models\PricingSetting;
use App\Models\Subject;

class RegistrationPricingService
{
    public function calculate(array $subjectIds): array
    {
        $pricing = PricingSetting::firstOrFail();
        $subjects = Subject::whereIn('id', $subjectIds)->get();

        if ($subjects->count() < $pricing->min_subjects) {
            throw new \Exception(
                'الحد الأدنى للتسجيل هو ' . $pricing->min_subjects . ' مواد'
            );
        }

        $totalHours = $subjects->sum('credit_hours');
        $subtotal = $totalHours * $pricing->price_per_credit_hour;
        $total = $subtotal + $pricing->registration_fee;

        return [
            'subjects'        => $subjects,
            'subjects_count'  => $subjects->count(),
            'total_hours'     => $totalHours,
            'price_per_hour'  => $pricing->price_per_credit_hour,
            'registration_fee'=> $pricing->registration_fee,
            'subtotal'        => $subtotal,
            'total'           => $total,
        ];
    }
}
