<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Pointage;
use Doctrine\ORM\EntityManagerInterface;

class UserStatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneAccessService $zoneAccessService,
        private WorkTimeCalculatorService $workTimeCalculator
    ) {}

    public function getCurrentUserStatus(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $lastPrincipalPointage = $this->getLastPrincipalPointage($user, $today);
        $lastGlobalPointage = $this->getLastGlobalPointage($user, $today);

        $status = 'absent';
        if ($lastPrincipalPointage) {
            $status = $lastPrincipalPointage->getType() === 'entree' ? 'present' : 'absent';
        }

        $lastAction = null;
        if ($lastGlobalPointage) {
            $isPrincipalService = $this->zoneAccessService->isBadgeuseInPrincipalZone($lastGlobalPointage->getBadgeuse(), $user);
            $serviceType = $isPrincipalService ? 'principal' : 'secondaire';

            $lastAction = [
                'heure' => $lastGlobalPointage->getHeure()->format(\DateTime::ATOM),
                'timestamp' => $lastGlobalPointage->getHeure()->format(\DateTime::ATOM),
                'type' => $lastGlobalPointage->getType(),
                'badgeuse' => $lastGlobalPointage->getBadgeuse()->getReference(),
                'zone' => implode(', ', $this->zoneAccessService->getBadgeuseZoneNames($lastGlobalPointage->getBadgeuse())),
                'is_principal' => $isPrincipalService,
                'service_type' => $serviceType,
                'affects_status' => $isPrincipalService && in_array($lastGlobalPointage->getType(), ['entree', 'sortie'])
            ];
        }

        return [
            'status' => $status,
            'last_action' => $lastAction,
            'date' => $today->format('Y-m-d'),
            'can_access_secondary' => $status === 'present'
        ];
    }

    public function getUserWorkingStatus(User $user): array
    {
        $currentStatus = $this->getCurrentUserStatus($user);

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $lastPrincipalPointage = $this->getLastPrincipalPointage($user);

        $status = $currentStatus['status'];
        $isInPrincipalZone = false;
        $currentWorkStart = null;

        if ($lastPrincipalPointage) {
            $isInPrincipalZone = $this->zoneAccessService->isBadgeuseInPrincipalZone($lastPrincipalPointage->getBadgeuse(), $user);

            if ($status === 'present') {
                $currentWorkStart = $this->findTodayWorkStart($user);
            }
        }

        $lastAction = $currentStatus['last_action'];

        // Calculate today's total working time (in minutes) across principal zone sessions
        $workingTimeToday = $this->workTimeCalculator->calculateTodayWorkingTime($user);

        return [
            'status' => $status,
            'is_in_principal_zone' => $isInPrincipalZone,
            'current_work_start' => $currentWorkStart ? $currentWorkStart->format(\DateTime::ATOM) : null,
            'last_action' => $lastAction,
            'date' => $today->format('Y-m-d'),
            'working_time_today' => $workingTimeToday
        ];
    }

    private function getLastPrincipalPointage(User $user, ?\DateTime $since = null): ?Pointage
    {
        $queryBuilder = $this->entityManager->getRepository(Pointage::class)
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
            ->setMaxResults(1)
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user);

        if ($since) {
            $queryBuilder->andWhere('p.heure >= :since')
                ->setParameter('since', $since);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    private function getLastGlobalPointage(User $user, \DateTime $since): ?Pointage
    {
        return $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('p.heure >= :since')
            ->andWhere('p.type IN (:types)')
            ->orderBy('p.heure', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->setParameter('types', ['entree', 'sortie'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function findTodayWorkStart(User $user): ?\DateTime
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $firstEntry = $this->entityManager->getRepository(Pointage::class)
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
            ->andWhere('p.heure >= :today')
            ->andWhere('p.type = :entree')
            ->orderBy('p.heure', 'ASC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setParameter('today', $today)
            ->setParameter('entree', 'entree')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $firstEntry?->getHeure();
    }
}
