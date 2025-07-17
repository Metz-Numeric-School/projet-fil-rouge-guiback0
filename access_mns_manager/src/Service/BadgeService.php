<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Zone;
use App\Entity\Acces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Service pour la gestion du système de badgeage
 */
class BadgeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrganisationService $organisationService
    ) {}

    /**
     * Enregistre un badgeage
     */
    public function recordBadgeAction(int $badgeNumber, int $badgeuseId, string $type = 'entree'): array
    {
        try {
            // Vérification du badge
            $badge = $this->entityManager->getRepository(Badge::class)
                ->findOneBy(['numero_badge' => $badgeNumber]);

            if (!$badge) {
                return [
                    'success' => false,
                    'error' => 'BADGE_NOT_FOUND',
                    'message' => 'Badge non trouvé'
                ];
            }

            // Vérification de la badgeuse
            $badgeuse = $this->entityManager->getRepository(Badgeuse::class)
                ->find($badgeuseId);

            if (!$badgeuse) {
                return [
                    'success' => false,
                    'error' => 'BADGEUSE_NOT_FOUND',
                    'message' => 'Badgeuse non trouvée'
                ];
            }

            // Vérification des permissions d'accès à la zone
            $user = $this->getUserFromBadge($badge);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'USER_NOT_FOUND',
                    'message' => 'Utilisateur non trouvé pour ce badge'
                ];
            }

            // Vérification de l'organisation
            $userOrganisation = $this->organisationService->getUserOrganisation($user);
            $currentOrganisation = $this->organisationService->getCurrentUserOrganisation();

            if (
                !$userOrganisation || !$currentOrganisation ||
                $userOrganisation->getId() !== $currentOrganisation->getId()
            ) {
                return [
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé - organisation différente'
                ];
            }

            // Vérification de l'accès à la zone via l'entité Acces
            $zones = $this->getBadgeuseZones($badgeuse);
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
                    'error' => 'ZONE_ACCESS_DENIED',
                    'message' => 'Accès refusé à cette zone'
                ];
            }

            // Validation du type de badgeage
            if (!in_array($type, ['entree', 'sortie'])) {
                return [
                    'success' => false,
                    'error' => 'INVALID_TYPE',
                    'message' => 'Type de badgeage invalide'
                ];
            }

            // Création du pointage
            $pointage = new Pointage();
            $pointage->setBadge($badge);
            $pointage->setBadgeuse($badgeuse);
            $pointage->setHeure(new \DateTime());
            $pointage->setType($type);

            $this->entityManager->persist($pointage);
            $this->entityManager->flush();

            return [
                'success' => true,
                'data' => [
                    'id' => $pointage->getId(),
                    'badge_number' => $badge->getNumero_badge(),
                    'user' => [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom()
                    ],
                    'badgeuse' => [
                        'id' => $badgeuse->getId(),
                        'reference' => $badgeuse->getReference(),
                        'zones' => $this->getBadgeuseZoneNames($badgeuse)
                    ],
                    'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                    'type' => $pointage->getType()
                ],
                'message' => 'Badgeage enregistré avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de l\'enregistrement du badgeage'
            ];
        }
    }

    /**
     * Récupère les zones accessibles via une badgeuse
     */
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

    /**
     * Récupère les noms des zones accessibles via une badgeuse
     */
    public function getBadgeuseZoneNames(Badgeuse $badgeuse): array
    {
        $zones = $this->getBadgeuseZones($badgeuse);
        return array_map(function ($zone) {
            return $zone->getNomZone();
        }, $zones);
    }

    /**
     * Récupère l'utilisateur associé à un badge
     */
    public function getUserFromBadge(Badge $badge): ?User
    {
        $userBadge = $this->entityManager->getRepository(UserBadge::class)
            ->findOneBy(['badge' => $badge, 'date_fin' => null]);

        return $userBadge?->getUtilisateur();
    }

    /**
     * Vérifie si un utilisateur peut accéder à une zone
     */
    public function canAccessZone(User $user, ?Zone $zone): bool
    {
        if (!$zone) {
            return false;
        }

        // Récupération des accès de l'utilisateur
        $access = $this->entityManager->getRepository(Acces::class)
            ->findOneBy([
                'utilisateur' => $user,
                'zone' => $zone,
                'date_fin' => null
            ]);

        return $access !== null;
    }

    /**
     * Récupère l'historique des badgeages d'un utilisateur
     */
    public function getUserBadgeHistory(User $user, \DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $queryBuilder = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('ub.date_fin IS NULL')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user', $user);

        if ($startDate) {
            $queryBuilder->andWhere('p.heure >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $queryBuilder->andWhere('p.heure <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $pointages = $queryBuilder->getQuery()->getResult();

        return array_map(function (Pointage $pointage) {
            return [
                'id' => $pointage->getId(),
                'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                'type' => $pointage->getType(),
                'badgeuse' => [
                    'id' => $pointage->getBadgeuse()->getId(),
                    'reference' => $pointage->getBadgeuse()->getReference()
                ]
            ];
        }, $pointages);
    }

    /**
     * Récupère le statut actuel d'un utilisateur (présent/absent)
     */
    public function getCurrentUserStatus(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $lastPointage = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('ub.date_fin IS NULL')
            ->andWhere('p.heure >= :today')
            ->orderBy('p.heure', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();

        $status = 'absent';
        $lastAction = null;

        if ($lastPointage) {
            $status = $lastPointage->getType() === 'entree' ? 'present' : 'absent';
            $lastAction = [
                'heure' => $lastPointage->getHeure()->format('Y-m-d H:i:s'),
                'type' => $lastPointage->getType(),
                'badgeuse' => $lastPointage->getBadgeuse()->getNom()
            ];
        }

        return [
            'status' => $status,
            'last_action' => $lastAction,
            'date' => $today->format('Y-m-d')
        ];
    }

    /**
     * Calcule le temps de travail d'un utilisateur pour une période donnée
     */
    public function calculateWorkingTime(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        $pointages = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('ub.date_fin IS NULL')
            ->andWhere('p.heure >= :startDate')
            ->andWhere('p.heure <= :endDate')
            ->orderBy('p.heure', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        $workingDays = [];
        $currentEntry = null;
        $totalMinutes = 0;

        foreach ($pointages as $pointage) {
            $date = $pointage->getHeure()->format('Y-m-d');

            if (!isset($workingDays[$date])) {
                $workingDays[$date] = [
                    'date' => $date,
                    'entries' => [],
                    'total_minutes' => 0
                ];
            }

            $workingDays[$date]['entries'][] = [
                'time' => $pointage->getHeure()->format('H:i'),
                'type' => $pointage->getType()
            ];

            if ($pointage->getType() === 'entree') {
                $currentEntry = $pointage->getHeure();
            } elseif ($pointage->getType() === 'sortie' && $currentEntry) {
                $minutes = ($pointage->getHeure()->getTimestamp() - $currentEntry->getTimestamp()) / 60;
                $workingDays[$date]['total_minutes'] += $minutes;
                $totalMinutes += $minutes;
                $currentEntry = null;
            }
        }

        // Conversion en heures et formatage
        foreach ($workingDays as &$day) {
            $day['total_hours'] = round($day['total_minutes'] / 60, 2);
        }

        return [
            'total_hours' => round($totalMinutes / 60, 2),
            'total_minutes' => $totalMinutes,
            'days' => array_values($workingDays)
        ];
    }
}
