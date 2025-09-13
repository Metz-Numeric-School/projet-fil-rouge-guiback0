<?php

namespace App\Service\User\Presence;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;

class DailyPresenceService
{
    public function __construct(
        private WorkTimeCalculatorService $workTimeCalculator
    ) {}

    public function getDailyPresence(User $user, string $date): array
    {
        try {
            $startDate = new \DateTime($date);
            $endDate = clone $startDate;
            $endDate->setTime(23, 59, 59);

            $workingTime = $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);

            $dayData = $workingTime['days'][0] ?? [
                'date' => $date,
                'entries' => [],
                'total_hours' => 0
            ];

            return [
                'user_id' => $user->getId(),
                'date' => $date,
                'entries' => $dayData['entries'],
                'total_hours' => $dayData['total_hours'] ?? 0,
                'status' => $this->getDayStatus($dayData['entries'])
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence journalière');
        }
    }

    public function getDayStatus(array $entries): string
    {
        if (empty($entries)) {
            return 'absent';
        }

        $lastEntry = end($entries);
        return $lastEntry['type'] === 'entree' ? 'present' : 'absent';
    }
}