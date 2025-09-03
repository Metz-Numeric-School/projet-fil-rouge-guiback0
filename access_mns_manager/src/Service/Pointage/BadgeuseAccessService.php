<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer l'accès aux badgeuses et leur récupération
 */
class BadgeuseAccessService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneAccessService $zoneAccessService,
        private UserStatusService $userStatusService,
    ) {}

    /**
     * Récupère toutes les badgeuses accessibles à un utilisateur avec leur statut
     */
    public function getUserBadgeusesWithStatus(User $user): array
    {
        try {
            // Récupération du service principal de l'utilisateur
            $principalService = $user->getPrincipalService();
            if (!$principalService) {
                return [
                    'success' => false,
                    'error' => 'NO_PRINCIPAL_SERVICE',
                    'message' => 'Aucun service principal trouvé pour cet utilisateur'
                ];
            }

            $badgeuses = [];
            $principalZone = null;

            // Récupérer TOUS les services de l'utilisateur (principal + secondaires)
            $allUserServices = [];
            foreach ($user->getTravail() as $travailler) {
                $service = $travailler->getService();
                if ($service) {
                    $allUserServices[] = $service;
                }
            }

            // Parcourir toutes les zones de tous les services de l'utilisateur
            foreach ($allUserServices as $service) {
                foreach ($service->getServiceZones() as $serviceZone) {
                    $zone = $serviceZone->getZone();
                    if (!$zone) continue;

                    // Identifier si c'est un service principal
                    $isServicePrincipal = $service->isIsPrincipal();
                    if ($isServicePrincipal) {
                        $principalZone = $zone;
                    }

                    // Récupération de toutes les badgeuses dans cette zone
                    foreach ($zone->getAcces() as $access) {
                        $badgeuse = $access->getBadgeuse();
                        if (!$badgeuse) continue;

                        // Éviter les doublons
                        $badgeuseId = $badgeuse->getId();
                        if (isset($badgeuses[$badgeuseId])) {
                            // Vérifier si cette zone n'est pas déjà ajoutée
                            $zoneExists = false;
                            foreach ($badgeuses[$badgeuseId]['zones'] as $existingZone) {
                                if ($existingZone['id'] === $zone->getId()) {
                                    $zoneExists = true;
                                    break;
                                }
                            }
                            
                            if (!$zoneExists) {
                                // Ajouter la zone à la badgeuse existante
                                $badgeuses[$badgeuseId]['zones'][] = [
                                    'id' => $zone->getId(),
                                    'nom_zone' => $zone->getNomZone(),
                                    'is_principal' => $isServicePrincipal,
                                    'service_id' => $service->getId(),
                                    'service_name' => $service->getNomService()
                                ];
                            }
                            
                            // Marquer comme principale si au moins un service est principal
                            if ($isServicePrincipal) {
                                $badgeuses[$badgeuseId]['is_principal'] = true;
                                $badgeuses[$badgeuseId]['service_type'] = 'mixed'; // Mixte si elle a à la fois principal et secondaire
                            }
                            continue;
                        }

                        $badgeuses[$badgeuseId] = [
                            'id' => $badgeuse->getId(),
                            'reference' => $badgeuse->getReference(),
                            'date_installation' => $badgeuse->getDateInstallation()->format('Y-m-d'),
                            'is_principal' => $isServicePrincipal,
                            'is_accessible' => true,
                            'service_type' => $isServicePrincipal ? 'principal' : 'secondaire',
                            'zones' => [[
                                'id' => $zone->getId(),
                                'nom_zone' => $zone->getNomZone(),
                                'is_principal' => $isServicePrincipal,
                                'service_id' => $service->getId(),
                                'service_name' => $service->getNomService()
                            ]]
                        ];
                    }
                }
            }

            // Détermination du statut de chaque badgeuse selon les règles métier
            $userStatus = $this->userStatusService->getCurrentUserStatus($user);
            $lastPointage = $this->getLastUserPointage($user);
            $isUserPresent = $userStatus['status'] === 'present';

            foreach ($badgeuses as &$badgeuse) {
                $badgeuse = $this->determineBadgeuseStatus($badgeuse, $isUserPresent, $lastPointage, $principalZone);
            }

            return [
                'success' => true,
                'data' => array_values($badgeuses),
                'user_status' => $userStatus
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la récupération des badgeuses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupère le dernier pointage d'un utilisateur dans son service principal
     */
    private function getLastUserPointage(User $user): ?Pointage
    {
        return $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->join('p.badgeuse', 'bdg')
            ->join('bdg.acces', 'a')
            ->join('a.zone', 'z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->join('s.travail', 't')
            ->where('ub.Utilisateur = :user_badge')
            ->andWhere('t.Utilisateur = :user_travail')
            ->andWhere('s.is_principal = true')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Détermine le statut d'une badgeuse selon les règles métier
     */
    private function determineBadgeuseStatus(array $badgeuse, bool $isUserPresent, ?Pointage $lastPointage, $principalZone): array
    {
        $badgeuse['user_status'] = $isUserPresent ? 'present' : 'absent';
        $badgeuse['last_pointage'] = $lastPointage ? $lastPointage->getHeure()->format('Y-m-d H:i:s') : null;
        
        return $badgeuse;
    }
}
