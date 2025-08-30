<?php

namespace App\Service\Pointage;

use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Exception\BadgeException;
use Doctrine\ORM\EntityManagerInterface;

class PointageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneAccessService $zoneAccessService,
        private UserStatusService $userStatusService
    ) {}

    public function recordBadgeAction(int $badgeNumber, int $badgeuseId, string $type = 'entree'): array
    {
        try {
            $this->entityManager->beginTransaction();

            $type = $this->normalizeType($type);
            $this->validateType($type);

            $badge = $this->findBadge($badgeNumber);
            $badgeuse = $this->findBadgeuse($badgeuseId);
            $user = $this->getUserFromBadge($badge);

            $this->zoneAccessService->validateUserZoneAccess($user, $badgeuse);

            $pointage = $this->createPointage($badge, $badgeuse, $type);
            
            $this->entityManager->persist($pointage);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->buildSuccessResponse($pointage, $user, $badgeuse);

        } catch (BadgeException $e) {
            $this->entityManager->rollback();
            return $e->toArray();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new BadgeException('INTERNAL_ERROR', 'Erreur lors de l\'enregistrement du badgeage');
        }
    }

    public function performPointageWithValidation(User $user, int $badgeuseId, bool $force = false): array
    {
        try {
            $this->entityManager->beginTransaction();
            
            $badgeuse = $this->findBadgeuse($badgeuseId);
            $activeBadge = $this->getUserActiveBadge($user);
            
            if (!$activeBadge) {
                throw new BadgeException(BadgeException::NO_ACTIVE_BADGE);
            }

            if (!$force) {
                $this->validatePointageAction($user, $badgeuse);
            }

            $userStatus = $this->userStatusService->getCurrentUserStatus($user);
            $isPrincipalService = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);
            
            $pointageType = $this->determinePointageType($userStatus, $isPrincipalService);
            $pointage = $this->createPointage($activeBadge, $badgeuse, $pointageType);

            $this->entityManager->persist($pointage);
            $this->entityManager->flush();

            $newStatus = $this->userStatusService->getCurrentUserStatus($user);
            
            $this->entityManager->commit();

            return [
                'success' => true,
                'data' => [
                    'pointage' => $this->formatPointageData($pointage, $activeBadge, $badgeuse),
                    'new_status' => $newStatus,
                    'message' => $this->getSuccessMessage($pointageType, $isPrincipalService, $userStatus)
                ]
            ];

        } catch (BadgeException $e) {
            $this->entityManager->rollback();
            return $e->toArray();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new BadgeException('INTERNAL_ERROR', 'Erreur lors de l\'enregistrement du pointage');
        }
    }

    private function normalizeType(string $type): string
    {
        $type = str_replace(['é','É'], 'e', $type);
        return mb_strtolower($type);
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, ['entree', 'sortie', 'acces'])) {
            throw new BadgeException(BadgeException::INVALID_TYPE);
        }
    }

    private function findBadge(int $badgeNumber): Badge
    {
        $badge = $this->entityManager->getRepository(Badge::class)
            ->findOneBy(['numero_badge' => $badgeNumber]);

        if (!$badge) {
            throw new BadgeException(BadgeException::BADGE_NOT_FOUND);
        }

        return $badge;
    }

    private function findBadgeuse(int $badgeuseId): Badgeuse
    {
        $badgeuse = $this->entityManager->getRepository(Badgeuse::class)
            ->find($badgeuseId);

        if (!$badgeuse) {
            throw new BadgeException(BadgeException::BADGEUSE_NOT_FOUND);
        }

        return $badgeuse;
    }

    private function createPointage(Badge $badge, Badgeuse $badgeuse, string $type): Pointage
    {
        $pointage = new Pointage();
        $pointage->setBadge($badge);
        $pointage->setBadgeuse($badgeuse);
        $pointage->setHeure(new \DateTime());
        $pointage->setType($type);

        return $pointage;
    }

    private function validatePointageAction(User $user, Badgeuse $badgeuse): void
    {
        $this->zoneAccessService->validateUserZoneAccess($user, $badgeuse);

        $userStatus = $this->userStatusService->getCurrentUserStatus($user);
        $isPrincipalService = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);

        if (!$isPrincipalService && $userStatus['status'] !== 'present') {
            throw new BadgeException(BadgeException::SECONDARY_ACCESS_DENIED);
        }
    }

    private function determinePointageType(array $userStatus, bool $isPrincipalService): string
    {
        if (!$isPrincipalService) {
            return 'acces';
        }

        return $userStatus['status'] === 'present' ? 'sortie' : 'entree';
    }

    private function buildSuccessResponse(Pointage $pointage, User $user, Badgeuse $badgeuse): array
    {
        return [
            'success' => true,
            'data' => [
                'id' => $pointage->getId(),
                'badge_number' => $pointage->getBadge()->getNumeroBadge(),
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom()
                ],
                'badgeuse' => [
                    'id' => $badgeuse->getId(),
                    'reference' => $badgeuse->getReference(),
                    'zones' => $this->zoneAccessService->getBadgeuseZoneNames($badgeuse)
                ],
                'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                'type' => $pointage->getType()
            ],
            'message' => 'Badgeage enregistré avec succès'
        ];
    }

    private function formatPointageData(Pointage $pointage, Badge $badge, Badgeuse $badgeuse): array
    {
        return [
            'id' => $pointage->getId(),
            'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
            'type' => $pointage->getType(),
            'badge' => [
                'id' => $badge->getId(),
                'numero_badge' => $badge->getNumeroBadge(),
                'type_badge' => $badge->getTypeBadge()
            ],
            'badgeuse' => [
                'id' => $badgeuse->getId(),
                'reference' => $badgeuse->getReference(),
                'zones' => $this->zoneAccessService->getBadgeuseZoneNames($badgeuse)
            ]
        ];
    }

    private function getSuccessMessage(string $type, bool $isPrincipalService, array $userStatus): string
    {
        if ($isPrincipalService) {
            return match ($type) {
                'entree' => 'Entrée dans le service principal enregistrée - Statut : Présent',
                'sortie' => 'Sortie du service principal enregistrée - Statut : Absent',
                default => 'Accès au service principal enregistré'
            };
        }

        $currentStatus = $userStatus['status'] === 'present' ? 'Présent' : 'Absent';
        return "Accès au service secondaire enregistré - Statut principal maintenu : {$currentStatus}";
    }

    private function getUserFromBadge(Badge $badge): ?User
    {
        if ($badge->getDateExpiration() && $badge->getDateExpiration() < new \DateTime()) {
            throw new BadgeException(BadgeException::BADGE_EXPIRED);
        }

        $userBadge = $this->entityManager->getRepository(UserBadge::class)
            ->findOneBy(['badge' => $badge]);

        if (!$userBadge) {
            throw new BadgeException(BadgeException::USER_NOT_FOUND);
        }

        return $userBadge->getUtilisateur();
    }

    private function getUserActiveBadge(User $user): ?Badge
    {
        $userBadge = $this->entityManager->getRepository(UserBadge::class)
            ->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->where('ub.Utilisateur = :user')
            ->andWhere('b.date_expiration IS NULL OR b.date_expiration > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $userBadge?->getBadge();
    }
}