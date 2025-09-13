<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Zone;
use App\Entity\Badgeuse;
use App\Entity\Acces;
use App\Entity\ServiceZone;
use App\Exception\BadgeException;
use App\Service\User\UserOrganisationService;
use Doctrine\ORM\EntityManagerInterface;

class ZoneAccessService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserOrganisationService $userOrganisationService
    ) {}

    public function getBadgeuseZones(Badgeuse $badgeuse): array
    {
        $accesses = $this->entityManager->getRepository(Acces::class)
            ->findBy(['badgeuse' => $badgeuse]);

        $zones = [];
        foreach ($accesses as $access) {
            if ($access->getZone()) {
                $zones[] = $access->getZone();
            }
        }

        return $zones;
    }

    public function getBadgeuseZoneNames(Badgeuse $badgeuse): array
    {
        $zones = $this->getBadgeuseZones($badgeuse);
        return array_map(fn($zone) => $zone->getNomZone(), $zones);
    }

    public function canAccessZone(User $user, ?Zone $zone): bool
    {
        if (!$zone) {
            return false;
        }

        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if (!$service) continue;

            $serviceZone = $this->entityManager->getRepository(ServiceZone::class)
                ->findOneBy([
                    'service' => $service,
                    'zone' => $zone
                ]);

            if ($serviceZone !== null) {
                return true;
            }
        }

        return false;
    }

    public function isBadgeuseInPrincipalZone(Badgeuse $badgeuse, User $user): bool
    {
        $principalServices = [];
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if ($service && $service->isIsPrincipal()) {
                $principalServices[] = $service;
            }
        }

        if (empty($principalServices)) {
            return false;
        }

        $zones = $this->getBadgeuseZones($badgeuse);
        
        foreach ($zones as $zone) {
            foreach ($principalServices as $principalService) {
                $serviceZone = $this->entityManager->getRepository(ServiceZone::class)
                    ->findOneBy(['service' => $principalService, 'zone' => $zone]);
                if ($serviceZone) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function validateUserZoneAccess(User $user, Badgeuse $badgeuse): void
    {
        $userOrganisation = $this->userOrganisationService->getUserOrganisation($user);
        $currentOrganisation = $this->userOrganisationService->getCurrentUserOrganisation();

        if (!$userOrganisation || !$currentOrganisation || 
            $userOrganisation->getId() !== $currentOrganisation->getId()) {
            throw new BadgeException(BadgeException::ACCESS_DENIED, 'Accès refusé - organisation différente');
        }

        $zones = $this->getBadgeuseZones($badgeuse);
        if (empty($zones)) {
            throw new BadgeException(BadgeException::NO_ZONES_CONFIGURED);
        }

        $hasAccess = false;
        foreach ($zones as $zone) {
            if ($this->canAccessZone($user, $zone)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            throw new BadgeException(BadgeException::ZONE_ACCESS_DENIED);
        }
    }
}