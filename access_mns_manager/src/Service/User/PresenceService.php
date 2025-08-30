<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;

class PresenceService
{
    public function __construct(
        private WorkTimeCalculatorService $workTimeCalculator,
        private UserService $userService
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
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence hebdomadaire');
        }
    }

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

    public function getOrganisationPresence(string $startDate, string $endDate): array
    {
        try {
            $users = $this->userService->getOrganisationUsers();
            $presences = [];

            foreach ($users as $user) {
                $summary = $this->getPresenceSummary($user, $startDate, $endDate);
                $presences[] = [
                    'user' => [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom(),
                        'email' => $user->getEmail()
                    ],
                    'presence' => $summary
                ];
            }

            $currentOrg = $this->userService->getCurrentUserOrganisation();
            
            return [
                'organisation' => $currentOrg?->getNomOrganisation() ?? 'Organisation inconnue',
                'period' => ['start' => $startDate, 'end' => $endDate],
                'users' => $presences
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence organisationnelle');
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

    private function getDayStatus(array $entries): string
    {
        if (empty($entries)) {
            return 'absent';
        }

        $lastEntry = end($entries);
        return $lastEntry['type'] === 'entree' ? 'present' : 'absent';
    }

    private function detectAnomalies(array $days): array
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

    public function generateCSVReport(array $presences): string
    {
        $csv = "Date,Nom,Prénom,Email,Heures Travaillées,Statut\n";

        foreach ($presences['users'] as $userData) {
            $user = $userData['user'];
            $presence = $userData['presence'];

            foreach ($presence['daily_details'] as $day) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%.2f,%s\n",
                    $this->escapeCSV($day['date']),
                    $this->escapeCSV($user['nom']),
                    $this->escapeCSV($user['prenom']),
                    $this->escapeCSV($user['email']),
                    $day['total_hours'],
                    $this->getDayStatus($day['entries'])
                );
            }
        }

        return $csv;
    }

    private function escapeCSV(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
