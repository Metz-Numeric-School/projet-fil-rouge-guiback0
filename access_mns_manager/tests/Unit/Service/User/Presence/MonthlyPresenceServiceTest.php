<?php

namespace App\Tests\Unit\Service\User\Presence;

use App\Entity\User;
use App\Service\User\Presence\MonthlyPresenceService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Exception\PresenceException;

class MonthlyPresenceServiceTest extends DatabaseKernelTestCase
{
    private MonthlyPresenceService $monthlyPresenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monthlyPresenceService = static::getContainer()->get(MonthlyPresenceService::class);
    }

    public function testGetMonthlyPresence(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $monthYear = '2024-01';
        $result = $this->monthlyPresenceService->getMonthlyPresence($user, $monthYear);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('month', $result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
        $this->assertArrayHasKey('statistics', $result);
        
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($monthYear, $result['month']);
        $this->assertIsFloat($result['total_hours']);
        $this->assertIsInt($result['total_minutes']);
        $this->assertIsArray($result['days']);
        $this->assertIsArray($result['statistics']);
        
        $stats = $result['statistics'];
        $this->assertArrayHasKey('total_working_days', $stats);
        $this->assertArrayHasKey('average_hours_per_day', $stats);
        $this->assertArrayHasKey('weekly_hours', $stats);
        $this->assertArrayHasKey('max_daily_hours', $stats);
        $this->assertArrayHasKey('min_daily_hours', $stats);
    }

    public function testGetMonthlyPresenceInvalidMonth(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $this->expectException(PresenceException::class);
        $this->monthlyPresenceService->getMonthlyPresence($user, 'invalid-month');
    }
}