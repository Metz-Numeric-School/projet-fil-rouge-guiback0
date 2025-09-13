<?php

namespace App\Service\User\Presence;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;

class PresenceAnalyzerService
{
    public function __construct(
        private WorkTimeCalculatorService $workTimeCalculator
    ) {}

    public function getPresenceSummary(User $user, string $startDate, string $endDate): array
    {
        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $end->setTime(23, 59, 59);

            $workingTime = $this->workTimeCalculator->calculateWorkingTime($user, $start, $end);

            $workingDays = array_filter($workingTime['days'], fn($day) => $day['total_hours'] > 0);
            $totalDays = count($workingDays);
            $averageHours = $totalDays > 0 ? $workingTime['total_hours'] / $totalDays : 0;
            $anomalies = $this->detectAnomalies($workingTime['days']);

            return [
                'user_id' => $user->getId(),
                'period' => ['start' => $startDate, 'end' => $endDate],
                'summary' => [
                    'total_hours' => $workingTime['total_hours'],
                    'total_days_worked' => $totalDays,
                    'average_hours_per_day' => round($averageHours, 2),
                    'total_days_in_period' => $start->diff($end)->days + 1
                ],
                'anomalies' => $anomalies,
                'daily_details' => $workingTime['days']
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul du résumé de présence');
        }
    }

    public function detectAnomalies(array $days): array
    {
        $anomalies = [];

        foreach ($days as $day) {
            $date = $day['date'];
            $entries = $day['entries'];
            $totalHours = $day['total_hours'];

            $anomalies = array_merge($anomalies, $this->checkDayAnomalies($date, $entries, $totalHours));
        }

        return $anomalies;
    }

    private function checkDayAnomalies(string $date, array $entries, float $totalHours): array
    {
        $anomalies = [];

        if ($totalHours > 12) {
            $anomalies[] = [
                'date' => $date,
                'type' => 'LONG_DAY',
                'description' => 'Journée de travail exceptionnellement longue',
                'hours' => $totalHours
            ];
        }

        if ($totalHours > 0 && $totalHours < 2) {
            $anomalies[] = [
                'date' => $date,
                'type' => 'SHORT_DAY',
                'description' => 'Journée de travail très courte',
                'hours' => $totalHours
            ];
        }

        if (count($entries) % 2 !== 0) {
            $anomalies[] = [
                'date' => $date,
                'type' => 'INCOMPLETE_BADGE',
                'description' => 'Badgeage incomplet (entrée sans sortie ou vice versa)',
                'entries_count' => count($entries)
            ];
        }

        if (count($entries) > 10) {
            $anomalies[] = [
                'date' => $date,
                'type' => 'TOO_MANY_BADGES',
                'description' => 'Nombre inhabituel de badgeages',
                'entries_count' => count($entries)
            ];
        }

        return $anomalies;
    }
}