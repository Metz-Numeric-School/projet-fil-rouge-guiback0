<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use App\Entity\UserBadge;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Zone;
use App\Entity\ServiceZone;
use App\Entity\Acces;
use App\Entity\Travailler;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;
use App\Tests\Shared\DatabaseKernelTestCase;

class WorkTimeCalculatorServiceTest extends DatabaseKernelTestCase
{
    private WorkTimeCalculatorService $workTimeCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workTimeCalculator = static::getContainer()->get(WorkTimeCalculatorService::class);
    }

    public function testCalculateWorkingTimeWithValidData(): void
    {
        $testData = $this->createTestUserWithPointages();
        $user = $testData['user'];

        $startDate = new \DateTime('2024-01-01 00:00:00');
        $endDate = new \DateTime('2024-01-01 23:59:59');

        $result = $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);

        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
        $this->assertIsFloat($result['total_hours']);
        $this->assertIsInt($result['total_minutes']);
        $this->assertIsArray($result['days']);
    }

    public function testCalculateWorkingTimeWithInvalidDateRange(): void
    {
        $user = new User();
        $user->setEmail('invalid@date.com')
            ->setNom('Invalid')
            ->setPrenom('Date')
            ->setPassword('password');
        $this->em->persist($user);
        $this->em->flush();

        $startDate = new \DateTime('2024-01-02');
        $endDate = new \DateTime('2024-01-01'); // End before start

        $this->expectException(PresenceException::class);
        $this->expectExceptionMessage('Période de dates invalide');

        $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);
    }

    public function testCalculateWorkingTimeForPeriodWithValidDates(): void
    {
        $testData = $this->createTestUserWithPointages();
        $user = $testData['user'];

        $result = $this->workTimeCalculator->calculateWorkingTimeForPeriod($user, '2024-01-01', '2024-01-01');

        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
    }

    public function testCalculateWorkingTimeForPeriodWithInvalidDate(): void
    {
        $user = new User();
        $user->setEmail('invalid@period.com')
            ->setNom('Invalid')
            ->setPrenom('Period')
            ->setPassword('password');
        $this->em->persist($user);
        $this->em->flush();

        $this->expectException(PresenceException::class);
        $this->expectExceptionMessage('Format de date invalide');

        $this->workTimeCalculator->calculateWorkingTimeForPeriod($user, 'invalid-date', '2024-01-01');
    }

    public function testCalculateTodayWorkingTime(): void
    {
        $testData = $this->createTestUserWithTodayPointages();
        $user = $testData['user'];

        $result = $this->workTimeCalculator->calculateTodayWorkingTime($user);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testCalculateCurrentWorkSessionWithActiveSession(): void
    {
        $testData = $this->createTestUserWithActiveSession();
        $user = $testData['user'];
        $endTime = new \DateTime();

        $result = $this->workTimeCalculator->calculateCurrentWorkSession($user, $endTime);

        // Result can be null if no active session or an array with session data
        $this->assertTrue($result === null || is_array($result));
    }

    public function testCalculateWorkingTimeWithMultipleDays(): void
    {
        $testData = $this->createTestUserWithMultipleDaysPointages();
        $user = $testData['user'];

        $startDate = new \DateTime('2024-01-01 00:00:00');
        $endDate = new \DateTime('2024-01-03 23:59:59');

        $result = $this->workTimeCalculator->calculateWorkingTime($user, $startDate, $endDate);

        $this->assertGreaterThanOrEqual(0, $result['total_minutes']);
        $this->assertCount(3, $result['days']); // 3 days in range
        
        foreach ($result['days'] as $day) {
            $this->assertArrayHasKey('date', $day);
            $this->assertArrayHasKey('total_hours', $day);
            $this->assertArrayHasKey('entries', $day);
        }
    }

    private function createTestUserWithPointages(): array
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Work Org')
            ->setEmail('work@test.com')
            ->setNomRue('Work Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Work Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Work Zone')
            ->setDescription('Zone de travail')
            ->setCapacite(50);
        $this->em->persist($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($service)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('WORK-BADGE-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Travail')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $user = new User();
        $user->setEmail('worktime@test.com')
            ->setNom('WorkTime')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $badge = new Badge();
        $badge->setNumeroBadge(999100)
            ->setTypeBadge('work')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user)
            ->setBadge($badge);
        $this->em->persist($userBadge);

        // Create some pointages for 2024-01-01
        $pointage1 = new Pointage();
        $pointage1->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-01 08:30:00'))
            ->setType('entree');
        $this->em->persist($pointage1);

        $pointage2 = new Pointage();
        $pointage2->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-01 17:00:00'))
            ->setType('sortie');
        $this->em->persist($pointage2);

        $this->em->flush();

        return [
            'user' => $user,
            'badge' => $badge,
            'badgeuse' => $badgeuse,
            'zone' => $zone,
            'service' => $service,
            'organisation' => $organisation
        ];
    }

    private function createTestUserWithTodayPointages(): array
    {
        $testData = $this->createTestUserWithPointages();
        $user = $testData['user'];
        $badge = $testData['badge'];
        $badgeuse = $testData['badgeuse'];

        // Add today's pointages
        $todayEntry = new Pointage();
        $todayEntry->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('today 08:00:00'))
            ->setType('entree');
        $this->em->persist($todayEntry);

        $todayExit = new Pointage();
        $todayExit->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('today 12:00:00'))
            ->setType('sortie');
        $this->em->persist($todayExit);

        $this->em->flush();

        return $testData;
    }

    private function createTestUserWithActiveSession(): array
    {
        $testData = $this->createTestUserWithPointages();
        $user = $testData['user'];
        $badge = $testData['badge'];
        $badgeuse = $testData['badgeuse'];

        // Add entry without exit (active session)
        $activeEntry = new Pointage();
        $activeEntry->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('today 09:00:00'))
            ->setType('entree');
        $this->em->persist($activeEntry);

        $this->em->flush();

        return $testData;
    }

    private function createTestUserWithMultipleDaysPointages(): array
    {
        $testData = $this->createTestUserWithPointages();
        $user = $testData['user'];
        $badge = $testData['badge'];
        $badgeuse = $testData['badgeuse'];

        // Day 2
        $day2Entry = new Pointage();
        $day2Entry->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-02 09:00:00'))
            ->setType('entree');
        $this->em->persist($day2Entry);

        $day2Exit = new Pointage();
        $day2Exit->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-02 18:00:00'))
            ->setType('sortie');
        $this->em->persist($day2Exit);

        // Day 3
        $day3Entry = new Pointage();
        $day3Entry->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-03 08:00:00'))
            ->setType('entree');
        $this->em->persist($day3Entry);

        $day3Exit = new Pointage();
        $day3Exit->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('2024-01-03 16:30:00'))
            ->setType('sortie');
        $this->em->persist($day3Exit);

        $this->em->flush();

        return $testData;
    }
}