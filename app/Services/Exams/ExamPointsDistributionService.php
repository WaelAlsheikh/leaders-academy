<?php

namespace App\Services\Exams;

class ExamPointsDistributionService
{
    /**
     * @return array<int, float>
     */
    public function distribute(int $count, float $total = 100): array
    {
        if ($count <= 0) {
            return [];
        }

        $base = intdiv((int) round($total * 100), $count) / 100;
        $points = array_fill(0, $count, $base);
        $assigned = array_sum($points);
        $remainder = round($total - $assigned, 2);

        $i = 0;
        while ($remainder > 0 && $i < $count) {
            $increment = min(0.01, $remainder);
            $points[$i] = round($points[$i] + $increment, 2);
            $remainder = round($remainder - $increment, 2);
            $i++;
        }

        return $points;
    }
}
