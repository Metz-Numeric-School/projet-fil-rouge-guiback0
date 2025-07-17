<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Pointage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour la gestion des présences et calculs de temps de travail
 */
class PresenceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BadgeService $badgeService,
        private OrganisationService $organisationService
    ) {}

    /**
     * Calcule la présence hebdomadaire d'un utilisateur
     */
    public function getWeeklyPresence(User $user, string $weekStart): array
    {
        $startDate = new \DateTime($weekStart);
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P6D')); // Ajouter 6 jours pour avoir une semaine complète
        $endDate->setTime(23, 59, 59);

        $workingTime = $this->badgeService->calculateWorkingTime($user, $startDate, $endDate);

        return [
            'user_id' => $user->getId(),
            'week' => $weekStart,
            'total_hours' => $workingTime['total_hours'],
            'total_minutes' => $workingTime['total_minutes'],
            'days' => $workingTime['days']
        ];
    }

    /**
     * Calcule la présence mensuelle d'un utilisateur
     */
    public function getMonthlyPresence(User $user, string $monthYear): array
    {
        $startDate = new \DateTime($monthYear . '-01');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));
        $endDate->setTime(23, 59, 59);

        $workingTime = $this->badgeService->calculateWorkingTime($user, $startDate, $endDate);

        // Calcul des statistiques mensuelles
        $stats = $this->calculateMonthlyStats($workingTime['days']);

        return [
            'user_id' => $user->getId(),
            'month' => $monthYear,
            'total_hours' => $workingTime['total_hours'],
            'total_minutes' => $workingTime['total_minutes'],
            'days' => $workingTime['days'],
            'statistics' => $stats
        ];
    }

    /**
     * Calcule la présence journalière d'un utilisateur
     */
    public function getDailyPresence(User $user, string $date): array
    {
        $startDate = new \DateTime($date);
        $endDate = clone $startDate;
        $endDate->setTime(23, 59, 59);

        $workingTime = $this->badgeService->calculateWorkingTime($user, $startDate, $endDate);

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
    }

    /**
     * Génère un résumé des temps de travail pour une période
     */
    public function getPresenceSummary(User $user, string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->setTime(23, 59, 59);

        $workingTime = $this->badgeService->calculateWorkingTime($user, $start, $end);

        // Calcul des moyennes et statistiques
        $workingDays = array_filter($workingTime['days'], function ($day) {
            return $day['total_hours'] > 0;
        });

        $totalDays = count($workingDays);
        $averageHours = $totalDays > 0 ? $workingTime['total_hours'] / $totalDays : 0;

        // Détection des anomalies
        $anomalies = $this->detectAnomalies($workingTime['days']);

        return [
            'user_id' => $user->getId(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => [
                'total_hours' => $workingTime['total_hours'],
                'total_days_worked' => $totalDays,
                'average_hours_per_day' => round($averageHours, 2),
                'total_days_in_period' => $start->diff($end)->days + 1
            ],
            'anomalies' => $anomalies,
            'daily_details' => $workingTime['days']
        ];
    }

    /**
     * Récupère la présence de tous les utilisateurs de l'organisation pour une période
     */
    public function getOrganisationPresence(string $startDate, string $endDate): array
    {
        $users = $this->organisationService->getOrganisationUsers();
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

        return [
            'organisation' => $this->organisationService->getCurrentUserOrganisation()->getNomOrganisation(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'users' => $presences
        ];
    }

    /**
     * Calcule les statistiques mensuelles
     */
    private function calculateMonthlyStats(array $days): array
    {
        $workingDays = array_filter($days, function ($day) {
            return $day['total_hours'] > 0;
        });

        $totalDays = count($workingDays);
        $totalHours = array_sum(array_column($workingDays, 'total_hours'));

        $averageHours = $totalDays > 0 ? $totalHours / $totalDays : 0;

        // Calcul des heures par semaine
        $weeklyHours = [];
        foreach ($days as $day) {
            $date = new \DateTime($day['date']);
            $weekNumber = $date->format('W');

            if (!isset($weeklyHours[$weekNumber])) {
                $weeklyHours[$weekNumber] = 0;
            }
            $weeklyHours[$weekNumber] += $day['total_hours'];
        }

        return [
            'total_working_days' => $totalDays,
            'average_hours_per_day' => round($averageHours, 2),
            'weekly_hours' => $weeklyHours,
            'max_daily_hours' => $totalDays > 0 ? max(array_column($workingDays, 'total_hours')) : 0,
            'min_daily_hours' => $totalDays > 0 ? min(array_column($workingDays, 'total_hours')) : 0
        ];
    }

    /**
     * Détermine le statut d'une journée en fonction des badgeages
     */
    private function getDayStatus(array $entries): string
    {
        if (empty($entries)) {
            return 'absent';
        }

        $lastEntry = end($entries);
        return $lastEntry['type'] === 'entree' ? 'present' : 'absent';
    }

    /**
     * Détecte les anomalies dans les temps de travail
     */
    private function detectAnomalies(array $days): array
    {
        $anomalies = [];

        foreach ($days as $day) {
            $date = $day['date'];
            $entries = $day['entries'];
            $totalHours = $day['total_hours'];

            // Anomalie : journée trop longue (plus de 12h)
            if ($totalHours > 12) {
                $anomalies[] = [
                    'date' => $date,
                    'type' => 'LONG_DAY',
                    'description' => 'Journée de travail exceptionnellement longue',
                    'hours' => $totalHours
                ];
            }

            // Anomalie : journée trop courte (moins de 2h mais présent)
            if ($totalHours > 0 && $totalHours < 2) {
                $anomalies[] = [
                    'date' => $date,
                    'type' => 'SHORT_DAY',
                    'description' => 'Journée de travail très courte',
                    'hours' => $totalHours
                ];
            }

            // Anomalie : badgeage impair (entrée sans sortie ou vice versa)
            if (count($entries) % 2 !== 0) {
                $anomalies[] = [
                    'date' => $date,
                    'type' => 'INCOMPLETE_BADGE',
                    'description' => 'Badgeage incomplet (entrée sans sortie ou vice versa)',
                    'entries_count' => count($entries)
                ];
            }

            // Anomalie : trop de badgeages dans la journée
            if (count($entries) > 10) {
                $anomalies[] = [
                    'date' => $date,
                    'type' => 'TOO_MANY_BADGES',
                    'description' => 'Nombre inhabituel de badgeages',
                    'entries_count' => count($entries)
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Génère un rapport CSV des présences
     */
    public function generateCSVReport(array $presences): string
    {
        $csv = "Date,Nom,Prénom,Email,Heures Travaillées,Statut\n";

        foreach ($presences['users'] as $userData) {
            $user = $userData['user'];
            $presence = $userData['presence'];

            foreach ($presence['daily_details'] as $day) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%.2f,%s\n",
                    $day['date'],
                    $user['nom'],
                    $user['prenom'],
                    $user['email'],
                    $day['total_hours'],
                    $this->getDayStatus($day['entries'])
                );
            }
        }

        return $csv;
    }
}
