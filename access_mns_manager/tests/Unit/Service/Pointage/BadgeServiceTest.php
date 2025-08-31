<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Badgeuse;
use App\Entity\Zone;
use App\Service\Pointage\BadgeService;
use App\Service\Pointage\PointageService;
use App\Service\Pointage\ZoneAccessService;
use App\Service\Pointage\UserStatusService;
use App\Service\Pointage\WorkTimeCalculatorService;
use App\Service\Pointage\BadgeValidatorService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BadgeServiceTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject */
    private $em;
    /** @var PointageService&MockObject */
    private $pointageService;
    /** @var ZoneAccessService&MockObject */
    private $zoneAccessService;
    /** @var UserStatusService&MockObject */
    private $userStatusService;
    /** @var WorkTimeCalculatorService&MockObject */
    private $workTimeCalculator;
    /** @var BadgeValidatorService&MockObject */
    private $badgeValidator;
    /** @var UserService&MockObject */
    private $userService;

    private BadgeService $badgeService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->pointageService = $this->createMock(PointageService::class);
        $this->zoneAccessService = $this->createMock(ZoneAccessService::class);
        $this->userStatusService = $this->createMock(UserStatusService::class);
        $this->workTimeCalculator = $this->createMock(WorkTimeCalculatorService::class);
        $this->badgeValidator = $this->createMock(BadgeValidatorService::class);
        $this->userService = $this->createMock(UserService::class);

        $this->badgeService = new BadgeService(
            $this->em,
            $this->pointageService,
            $this->zoneAccessService,
            $this->userStatusService,
            $this->workTimeCalculator,
            $this->badgeValidator,
            $this->userService
        );
    }

    public function testRecordBadgeActionDelegatesToPointageService(): void
    {
        $badgeNumber = 200001;
        $badgeuseId = 1;
        $type = 'entree';
        $expected = ['success' => true, 'message' => 'OK'];

        $this->pointageService
            ->expects($this->once())
            ->method('recordBadgeAction')
            ->with($badgeNumber, $badgeuseId, $type)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->recordBadgeAction($badgeNumber, $badgeuseId, $type));
    }

    public function testRecordBadgeActionDefaultType(): void
    {
        $badgeNumber = 200002;
        $badgeuseId = 2;
        $expected = ['success' => true];

        $this->pointageService
            ->expects($this->once())
            ->method('recordBadgeAction')
            ->with($badgeNumber, $badgeuseId, 'entree')
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->recordBadgeAction($badgeNumber, $badgeuseId));
    }

    public function testGetBadgeuseZonesDelegates(): void
    {
        $badgeuse = new Badgeuse();
        $zones = [new Zone()];

        $this->zoneAccessService
            ->expects($this->once())
            ->method('getBadgeuseZones')
            ->with($badgeuse)
            ->willReturn($zones);

        $this->assertSame($zones, $this->badgeService->getBadgeuseZones($badgeuse));
    }

    public function testGetBadgeuseZoneNamesDelegates(): void
    {
        $badgeuse = new Badgeuse();
        $names = ['Zone Alpha', 'Zone Beta'];

        $this->zoneAccessService
            ->expects($this->once())
            ->method('getBadgeuseZoneNames')
            ->with($badgeuse)
            ->willReturn($names);

        $this->assertSame($names, $this->badgeService->getBadgeuseZoneNames($badgeuse));
    }

    public function testCanAccessZoneDelegates(): void
    {
        $user = new User();
        $zone = new Zone();

        $this->zoneAccessService
            ->expects($this->once())
            ->method('canAccessZone')
            ->with($user, $zone)
            ->willReturn(true);

        $this->assertTrue($this->badgeService->canAccessZone($user, $zone));
    }

    public function testGetUserBadgeHistoryDelegates(): void
    {
        $user = new User();
        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-31');
        $expected = ['history' => 'data'];

        $this->badgeValidator
            ->expects($this->once())
            ->method('getUserBadgeHistory')
            ->with($user, $start, $end)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->getUserBadgeHistory($user, $start, $end));
    }

    public function testGetUserBadgeHistoryWithNullDates(): void
    {
        $user = new User();
        $expected = ['history' => 'all'];

        $this->badgeValidator
            ->expects($this->once())
            ->method('getUserBadgeHistory')
            ->with($user, null, null)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->getUserBadgeHistory($user));
    }

    public function testGetCurrentUserStatusDelegates(): void
    {
        $user = new User();
        $status = ['status' => 'present'];

        $this->userStatusService
            ->expects($this->once())
            ->method('getCurrentUserStatus')
            ->with($user)
            ->willReturn($status);

        $this->assertSame($status, $this->badgeService->getCurrentUserStatus($user));
    }

    public function testCalculateWorkingTimeDelegates(): void
    {
        $user = new User();
        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-31');
        $expected = ['working_time' => '8h'];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->with($user, $start, $end)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->calculateWorkingTime($user, $start, $end));
    }

    public function testPerformPointageWithValidationDelegates(): void
    {
        $user = new User();
        $badgeuseId = 5;
        $expected = ['success' => true];

        $this->pointageService
            ->expects($this->once())
            ->method('performPointageWithValidation')
            ->with($user, $badgeuseId, false)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->performPointageWithValidation($user, $badgeuseId));
    }

    public function testPerformPointageWithValidationForceTrue(): void
    {
        $user = new User();
        $badgeuseId = 7;
        $expected = ['success' => true, 'forced' => true];

        $this->pointageService
            ->expects($this->once())
            ->method('performPointageWithValidation')
            ->with($user, $badgeuseId, true)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->performPointageWithValidation($user, $badgeuseId, true));
    }

    public function testGetUserActiveBadgesDelegates(): void
    {
        $user = new User();
        $badges = [['badge' => 'data']];

        $this->badgeValidator
            ->expects($this->once())
            ->method('getUserActiveBadges')
            ->with($user)
            ->willReturn($badges);

        $this->assertSame($badges, $this->badgeService->getUserActiveBadges($user));
    }

    public function testCalculateWorkingTimeForPeriodDelegates(): void
    {
        $user = new User();
        $start = '2024-01-01';
        $end = '2024-01-31';
        $expected = ['total_time' => '160h'];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTimeForPeriod')
            ->with($user, $start, $end)
            ->willReturn($expected);

        $this->assertSame($expected, $this->badgeService->calculateWorkingTimeForPeriod($user, $start, $end));
    }

    public function testGetUserWorkingStatusDelegates(): void
    {
        $user = new User();
        $status = ['working_status' => 'active'];

        $this->userStatusService
            ->expects($this->once())
            ->method('getUserWorkingStatus')
            ->with($user)
            ->willReturn($status);

        $this->assertSame($status, $this->badgeService->getUserWorkingStatus($user));
    }
}