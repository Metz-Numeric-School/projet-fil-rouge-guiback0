<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Badgeuse;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour valider les actions de pointage selon les règles métier
 */
class PointageValidationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneAccessService $zoneAccessService,
        private UserStatusService $userStatusService,
        private BadgeValidatorService $badgeValidator,
    ) {}

    /**
     * Valide si une action de pointage est autorisée pour un utilisateur et une badgeuse
     */
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
            $activeBadge = $this->badgeValidator->getUserActiveBadge($user);
            if (!$activeBadge) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'NO_ACTIVE_BADGE',
                    'message' => 'Aucun badge actif trouvé'
                ];
            }

            // Vérification du statut utilisateur et type de service
            $userStatus = $this->userStatusService->getCurrentUserStatus($user);
            $isPrincipalService = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);

            // RÈGLE PRINCIPALE: Vérification d'accès à la zone
            $zones = $this->zoneAccessService->getBadgeuseZones($badgeuse);
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
                if ($this->zoneAccessService->canAccessZone($user, $zone)) {
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
}
