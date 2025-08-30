<?php

namespace App\Service\Pointage;

use App\Entity\Badge;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Pointage;
use App\Exception\BadgeException;
use Doctrine\ORM\EntityManagerInterface;

class BadgeValidatorService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getUserFromBadge(Badge $badge): ?User
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

    public function getUserActiveBadge(User $user): ?Badge
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

    public function getUserActiveBadges(User $user): array
    {
        $userBadges = $this->entityManager->getRepository(UserBadge::class)
            ->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->where('ub.Utilisateur = :user')
            ->andWhere('b.date_expiration IS NULL OR b.date_expiration > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        return array_map(function (UserBadge $userBadge) {
            $badge = $userBadge->getBadge();
            return [
                'id' => $badge->getId(),
                'numero_badge' => $badge->getNumeroBadge(),
                'type_badge' => $badge->getTypeBadge(),
                'is_active' => true
            ];
        }, $userBadges);
    }

    public function getUserBadgeHistory(User $user, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $queryBuilder = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
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

    public function isRecentEntry(Pointage $pointage): bool
    {
        if ($pointage->getType() !== 'entree') {
            return false;
        }

        $now = new \DateTime();
        $pointageTime = $pointage->getHeure();
        $hoursDiff = ($now->getTimestamp() - $pointageTime->getTimestamp()) / 3600;

        return $hoursDiff < 8;
    }
}