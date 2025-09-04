<?php

namespace App\Service\User\Presence;

class PresenceReportService
{
    public function __construct(
        private DailyPresenceService $dailyPresenceService
    ) {}

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
                    $this->dailyPresenceService->getDayStatus($day['entries'])
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