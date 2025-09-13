<?php

namespace App\Service\Pointage;

use App\Entity\User;
use App\Entity\Pointage;
use App\Exception\PresenceException;
use Doctrine\ORM\EntityManagerInterface;

class WorkTimeCalculatorService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneAccessService $zoneAccessService
    ) {}

    public function calculateWorkingTime(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        if ($startDate > $endDate) {
            throw new PresenceException(PresenceException::INVALID_DATE_RANGE);
        }

        $allPointages = $this->getAllPointages($user, $startDate, $endDate);
        $workTimePointages = $this->filterPrincipalPointages($allPointages, $user);

        $workingDays = [];
        $currentEntries = [];
        $totalMinutes = 0;

        $this->processAllPointagesForDisplay($allPointages, $workingDays, $user);
        $totalMinutes = $this->calculateWorkTime($workTimePointages, $workingDays, $currentEntries);
        $totalMinutes = $this->handleOngoingSessions($currentEntries, $workingDays, $totalMinutes, $endDate);

        $this->convertToHours($workingDays);

        return [
            'total_hours' => round($totalMinutes / 60, 2),
            'total_minutes' => $totalMinutes,
            'days' => array_values($workingDays)
        ];
    }

    public function calculateWorkingTimeForPeriod(User $user, string $startDate, string $endDate): array
    {
        try {
            $start = new \DateTime($startDate . ' 00:00:00');
            $end = new \DateTime($endDate . ' 23:59:59');

            return $this->calculateWorkingTime($user, $start, $end);
        } catch (\Exception $e) {
            throw new PresenceException(PresenceException::INVALID_DATE_FORMAT);
        }
    }

    public function calculateTodayWorkingTime(User $user): int
    {
        $today = new \DateTime();
        $todayStart = clone $today;
        $todayStart->setTime(0, 0, 0);
        $todayEnd = clone $today;
        $todayEnd->setTime(23, 59, 59);

        $workingTime = $this->calculateWorkingTime($user, $todayStart, $todayEnd);
        return (int)$workingTime['total_minutes'];
    }

    public function calculateCurrentWorkSession(User $user, \DateTime $endTime): ?array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $startPointage = $this->entityManager->getRepository(Pointage::class)
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
            ->andWhere('p.heure < :endTime')
            ->andWhere('p.type = :entree')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setParameter('today', $today)
            ->setParameter('endTime', $endTime)
            ->setParameter('entree', 'entree')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$startPointage) {
            return null;
        }

        $durationMinutes = ($endTime->getTimestamp() - $startPointage->getHeure()->getTimestamp()) / 60;

        return [
            'start_time' => $startPointage->getHeure()->format(\DateTime::ATOM),
            'end_time' => $endTime->format(\DateTime::ATOM),
            'duration_minutes' => (int)$durationMinutes
        ];
    }

    private function getAllPointages(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('p.heure >= :startDate')
            ->andWhere('p.heure <= :endDate')
            ->orderBy('p.heure', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    private function filterPrincipalPointages(array $allPointages, User $user): array
    {
        $workTimePointages = [];
        foreach ($allPointages as $pointage) {
            $badgeuse = $pointage->getBadgeuse();
            $isPrincipalZone = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);

            if ($isPrincipalZone) {
                $workTimePointages[] = $pointage;
            }
        }

        return $workTimePointages;
    }

    private function processAllPointagesForDisplay(array $allPointages, array &$workingDays, User $user): void
    {
        foreach ($allPointages as $pointage) {
            $date = $pointage->getHeure()->format('Y-m-d');

            if (!isset($workingDays[$date])) {
                $workingDays[$date] = [
                    'date' => $date,
                    'entries' => [],
                    'total_minutes' => 0
                ];
            }

            $badgeuse = $pointage->getBadgeuse();
            $isPrincipalZone = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);

            $workingDays[$date]['entries'][] = [
                'time' => $pointage->getHeure()->format('H:i'),
                'type' => $pointage->getType(),
                'zone' => $isPrincipalZone ? 'principal' : 'secondaire'
            ];
        }
    }

    private function calculateWorkTime(array $workTimePointages, array &$workingDays, array &$currentEntries): int
    {
        $totalMinutes = 0;

        foreach ($workTimePointages as $pointage) {
            $date = $pointage->getHeure()->format('Y-m-d');

            if ($pointage->getType() === 'entree') {
                if (!isset($currentEntries[$date]) || $currentEntries[$date] === null) {
                    $currentEntries[$date] = $pointage->getHeure();
                }
            } elseif ($pointage->getType() === 'sortie') {
                $currentEntry = $currentEntries[$date] ?? null;
                if ($currentEntry instanceof \DateTime) {
                    $minutes = ($pointage->getHeure()->getTimestamp() - $currentEntry->getTimestamp()) / 60;
                    $workingDays[$date]['total_minutes'] += $minutes;
                    $totalMinutes += $minutes;
                    $currentEntries[$date] = null;
                }
            }
        }

        return $totalMinutes;
    }

    private function handleOngoingSessions(array $currentEntries, array &$workingDays, int $totalMinutes, \DateTime $endDate): int
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');

        foreach ($currentEntries as $date => $currentEntry) {
            if ($currentEntry instanceof \DateTime) {
                if ($date === $today && $endDate >= $now) {
                    $minutesSinceEntry = ($now->getTimestamp() - $currentEntry->getTimestamp()) / 60;
                    $workingDays[$date]['total_minutes'] += $minutesSinceEntry;
                    $totalMinutes += $minutesSinceEntry;

                    $workingDays[$date]['entries'][] = [
                        'time' => 'En cours',
                        'type' => 'session_active',
                        'since' => $currentEntry->format('H:i')
                    ];
                }
            }
        }

        return $totalMinutes;
    }

    private function convertToHours(array &$workingDays): void
    {
        foreach ($workingDays as &$day) {
            $day['total_hours'] = round($day['total_minutes'] / 60, 2);
        }
    }
}
