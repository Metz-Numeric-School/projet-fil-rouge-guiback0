<?php

namespace App\Service\User\Presence;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;

class MonthlyPresenceService
{
    public function __construct(
        private WorkTimeCalculatorService $workTimeCalculator
    ) {}

    public function getMonthlyPresence(User $user, string $monthYear): array
    {
        try {
            $startDate = new \DateTime($monthYear . '-01');
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));
            $endDate->setTime(23, 59, 59);

            $workingTime = $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);
            $stats = $this->calculateMonthlyStats($workingTime['days']);

            return [
                'user_id' => $user->getId(),
                'month' => $monthYear,
                'total_hours' => $workingTime['total_hours'],
                'total_minutes' => $workingTime['total_minutes'],
                'days' => $workingTime['days'],
                'statistics' => $stats
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence mensuelle');
        }
    }

    private function calculateMonthlyStats(array $days): array
    {
        $workingDays = array_filter($days, fn($day) => $day['total_hours'] > 0);
        $totalDays = count($workingDays);
        $totalHours = array_sum(array_column($workingDays, 'total_hours'));
        $averageHours = $totalDays > 0 ? $totalHours / $totalDays : 0;

        $weeklyHours = [];
        foreach ($days as $day) {
            $date = new \DateTime($day['date']);
            $weekNumber = $date->format('W');
            $weeklyHours[$weekNumber] = ($weeklyHours[$weekNumber] ?? 0) + $day['total_hours'];
        }

        return [
            'total_working_days' => $totalDays,
            'average_hours_per_day' => round($averageHours, 2),
            'weekly_hours' => $weeklyHours,
            'max_daily_hours' => $totalDays > 0 ? max(array_column($workingDays, 'total_hours')) : 0,
            'min_daily_hours' => $totalDays > 0 ? min(array_column($workingDays, 'total_hours')) : 0
        ];
    }
}