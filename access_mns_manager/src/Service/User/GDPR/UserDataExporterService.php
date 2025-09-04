<?php

namespace App\Service\User\GDPR;

use App\Entity\User;
use App\Service\User\UserOrganisationService;

class UserDataExporterService
{
    public function __construct(
        private UserOrganisationService $userOrganisationService
    ) {}

    public function exportUserData(User $user): array
    {
        return [
            'personal_information' => $this->formatPersonalInfo($user),
            'account_information' => $this->formatAccountInfo($user),
            'organisation' => $this->formatOrganisationInfo($user),
            'services' => $this->formatServicesInfo($user),
            'badges' => $this->formatBadgesInfo($user)
        ];
    }

    private function formatPersonalInfo(User $user): array
    {
        return [
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'telephone' => $user->getTelephone(),
            'date_naissance' => $user->getDateNaissance()?->format('Y-m-d'),
            'date_inscription' => $user->getDateInscription()->format('Y-m-d'),
            'poste' => $user->getPoste(),
            'horraire' => $user->getHorraire()?->format('H:i'),
            'heure_debut' => $user->getHeureDebut()?->format('H:i'),
            'jours_semaine_travaille' => $user->getJoursSemaineTravaille()
        ];
    }

    private function formatAccountInfo(User $user): array
    {
        return [
            'compte_actif' => $user->isCompteActif(),
            'date_derniere_connexion' => $user->getDateDerniereConnexion()?->format('Y-m-d H:i:s'),
            'date_derniere_modification' => $user->getDateDerniereModification()?->format('Y-m-d H:i:s'),
            'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d'),
            'roles' => $user->getRoles()
        ];
    }

    private function formatOrganisationInfo(User $user): ?array
    {
        $organisation = $this->userOrganisationService->getUserOrganisation($user);
        
        if (!$organisation) {
            return null;
        }

        return [
            'id' => $organisation->getId(),
            'nom_organisation' => $organisation->getNomOrganisation(),
            'email' => $organisation->getEmail(),
            'telephone' => $organisation->getTelephone(),
            'site_web' => $organisation->getSiteWeb(),
            'siret' => $organisation->getSiret()
        ];
    }

    private function formatServicesInfo(User $user): array
    {
        $servicesData = [];
        
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if ($service) {
                $servicesData[] = [
                    'nom_service' => $service->getNomService(),
                    'niveau_service' => $service->getNiveauService(),
                    'date_debut' => $travailler->getDateDebut()->format('Y-m-d'),
                    'date_fin' => $travailler->getDateFin()?->format('Y-m-d'),
                    'is_principal' => $service->isIsPrincipal()
                ];
            }
        }

        return $servicesData;
    }

    private function formatBadgesInfo(User $user): array
    {
        $badgesData = [];
        
        foreach ($user->getUserBadges() as $userBadge) {
            $badge = $userBadge->getBadge();
            if ($badge) {
                $badgesData[] = [
                    'numero_badge' => $badge->getNumeroBadge(),
                    'type_badge' => $badge->getTypeBadge(),
                    'date_creation' => $badge->getDateCreation()->format('Y-m-d'),
                    'date_expiration' => $badge->getDateExpiration()?->format('Y-m-d')
                ];
            }
        }

        return $badgesData;
    }
}