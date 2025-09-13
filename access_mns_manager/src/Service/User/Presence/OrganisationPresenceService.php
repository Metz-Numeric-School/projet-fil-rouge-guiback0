<?php

namespace App\Service\User\Presence;

use App\Exception\PresenceException;
use App\Service\User\UserOrganisationService;

class OrganisationPresenceService
{
    public function __construct(
        private UserOrganisationService $userOrganisationService,
        private PresenceAnalyzerService $presenceAnalyzer
    ) {}

    public function getOrganisationPresence(string $startDate, string $endDate): array
    {
        try {
            $users = $this->userOrganisationService->getOrganisationUsers();
            $presences = [];

            foreach ($users as $user) {
                $summary = $this->presenceAnalyzer->getPresenceSummary($user, $startDate, $endDate);
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

            $currentOrg = $this->userOrganisationService->getCurrentUserOrganisation();
            
            return [
                'organisation' => $currentOrg?->getNomOrganisation() ?? 'Organisation inconnue',
                'period' => ['start' => $startDate, 'end' => $endDate],
                'users' => $presences
            ];
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::CALCULATION_ERROR, 'Erreur lors du calcul de la présence organisationnelle');
        }
    }
}