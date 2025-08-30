<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Badgeuse;
use App\Entity\Badge;
use App\Entity\Pointage;
use App\Entity\UserBadge;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service principal pour la gestion du système de badgeage
 */
class BadgeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PointageService $pointageService,
        private ZoneAccessService $zoneAccessService,
        private UserStatusService $userStatusService,
        private WorkTimeCalculatorService $workTimeCalculator,
        private BadgeValidatorService $badgeValidator,
        private UserService $userService
    ) {}

    public function recordBadgeAction(int $badgeNumber, int $badgeuseId, string $type = 'entree'): array
    {
        return $this->pointageService->recordBadgeAction($badgeNumber, $badgeuseId, $type);
    }

    public function getBadgeuseZones(Badgeuse $badgeuse): array
    {
        return $this->zoneAccessService->getBadgeuseZones($badgeuse);
    }

    public function getBadgeuseZoneNames(Badgeuse $badgeuse): array
    {
        return $this->zoneAccessService->getBadgeuseZoneNames($badgeuse);
    }

    public function canAccessZone(User $user, $zone): bool
    {
        return $this->zoneAccessService->canAccessZone($user, $zone);
    }

    public function getUserBadgeHistory(User $user, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        return $this->badgeValidator->getUserBadgeHistory($user, $startDate, $endDate);
    }

    public function getCurrentUserStatus(User $user): array
    {
        return $this->userStatusService->getCurrentUserStatus($user);
    }

    public function calculateWorkingTime(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);
    }

    public function performPointageWithValidation(User $user, int $badgeuseId, bool $force = false): array
    {
        return $this->pointageService->performPointageWithValidation($user, $badgeuseId, $force);
    }

    public function getUserActiveBadges(User $user): array
    {
        return $this->badgeValidator->getUserActiveBadges($user);
    }

    public function calculateWorkingTimeForPeriod(User $user, string $startDate, string $endDate): array
    {
        return $this->workTimeCalculator->calculateWorkingTimeForPeriod($user, $startDate, $endDate);
    }

    public function getUserWorkingStatus(User $user): array
    {
        return $this->userStatusService->getUserWorkingStatus($user);
    }

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
            $userStatus = $this->getCurrentUserStatus($user);
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

    public function validatePointageAction(User $user, int $badgeuseId): array
    {
        try {
            $badgeuse = $this->entityManager->getRepository(Badgeuse::class)->find($badgeuseId);
            if (!$badgeuse) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'BADGEUSE_NOT_FOUND',
                    'message' => 'Badgeuse non trouvée'
                ];
            }

            // Vérification du badge actif
            $activeBadge = $this->getUserActiveBadge($user);
            if (!$activeBadge) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'NO_ACTIVE_BADGE',
                    'message' => 'Aucun badge actif trouvé'
                ];
            }

            // Vérification du statut utilisateur et type de service
            $userStatus = $this->getCurrentUserStatus($user);
            $isPrincipalService = $this->isBadgeuseInPrincipalZone($badgeuse, $user);

            // RÈGLE PRINCIPALE: Vérification d'accès à la zone
            $zones = $this->getBadgeuseZones($badgeuse);
            if (empty($zones)) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'NO_ZONES_CONFIGURED',
                    'message' => 'Aucune zone configurée pour cette badgeuse'
                ];
            }

            $hasAccess = false;
            foreach ($zones as $zone) {
                if ($this->canAccessZone($user, $zone)) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'ZONE_ACCESS_DENIED',
                    'message' => 'Vous n\'avez pas accès à cette zone'
                ];
            }

            // Vérification de la logique métier pour les services secondaires
            if (!$isPrincipalService && $userStatus['status'] !== 'present') {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'SECONDARY_ACCESS_DENIED',
                    'message' => 'Vous devez d\'abord pointer dans votre service principal pour accéder aux services secondaires',
                    'requires_principal' => true
                ];
            }

            return [
                'success' => true,
                'is_valid' => true,
                'can_proceed' => true,
                'message' => $isPrincipalService ? 'Pointage principal autorisé' : 'Accès secondaire autorisé',
                'service_type' => $isPrincipalService ? 'principal' : 'secondaire'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'is_valid' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la validation'
            ];
        }
    }

    private function getUserActiveBadge(User $user): ?Badge
    {
        return $this->badgeValidator->getUserActiveBadges($user)[0]['badge'] ?? null;
    }

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

    private function isBadgeuseInPrincipalZone(Badgeuse $badgeuse, User $user): bool
    {
        return $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);
    }

    private function determineBadgeuseStatus(array $badgeuse, bool $isUserPresent, ?Pointage $lastPointage, $principalZone): array
    {
        // Logic for determining badgeuse status based on user presence and last pointage
        $badgeuse['user_status'] = $isUserPresent ? 'present' : 'absent';
        $badgeuse['last_pointage'] = $lastPointage ? $lastPointage->getHeure()->format('Y-m-d H:i:s') : null;
        
        return $badgeuse;
    }

}
