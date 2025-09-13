<?php

namespace App\Service\User\Presence;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;

class WeeklyPresenceService
{
    public function __construct(
        private WorkTimeCalculatorService $workTimeCalculator
    ) {}

    public function getWeeklyPresence(User $user, string $weekStart): array
    {
        try {
            $startDate = new \DateTime($weekStart);
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P6D'));
            $endDate->setTime(23, 59, 59);

            $workingTime = $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);

            return [
                'user_id' => $user->getId(),
                'week' => $weekStart,
                'total_hours' => $workingTime['total_hours'],
                'total_minutes' => $workingTime['total_minutes'],
                'days' => $workingTime['days']
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence hebdomadaire: ' . $e->getMessage());
        }
    }
}