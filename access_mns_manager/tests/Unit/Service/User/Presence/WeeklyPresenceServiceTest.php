<?php

namespace App\Tests\Unit\Service\User\Presence;

use App\Entity\User;
use App\Service\User\Presence\WeeklyPresenceService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Exception\PresenceException;

class WeeklyPresenceServiceTest extends DatabaseKernelTestCase
{
    private WeeklyPresenceService $weeklyPresenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weeklyPresenceService = static::getContainer()->get(WeeklyPresenceService::class);
    }

    public function testGetWeeklyPresence(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $weekStart = '2024-01-01'; // Lundi
        $result = $this->weeklyPresenceService->getWeeklyPresence($user, $weekStart);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('week', $result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
        
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($weekStart, $result['week']);
        $this->assertIsFloat($result['total_hours']);
        $this->assertIsInt($result['total_minutes']);
        $this->assertIsArray($result['days']);
    }

    public function testGetWeeklyPresenceInvalidDate(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $this->expectException(PresenceException::class);
        $this->weeklyPresenceService->getWeeklyPresence($user, 'invalid-date');
    }
}