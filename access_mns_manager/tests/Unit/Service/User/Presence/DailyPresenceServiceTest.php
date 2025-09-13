<?php

namespace App\Tests\Unit\Service\User\Presence;

use App\Entity\User;
use App\Service\User\Presence\DailyPresenceService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Exception\PresenceException;

class DailyPresenceServiceTest extends DatabaseKernelTestCase
{
    private DailyPresenceService $dailyPresenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dailyPresenceService = static::getContainer()->get(DailyPresenceService::class);
    }

    public function testGetDailyPresence(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $date = '2024-01-15';
        $result = $this->dailyPresenceService->getDailyPresence($user, $date);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('entries', $result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('status', $result);
        
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($date, $result['date']);
        $this->assertIsArray($result['entries']);
        $this->assertIsNumeric($result['total_hours']);
        $this->assertIsString($result['status']);
        $this->assertContains($result['status'], ['absent', 'present']);
    }

    public function testGetDailyPresenceInvalidDate(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $this->expectException(PresenceException::class);
        $this->dailyPresenceService->getDailyPresence($user, 'invalid-date');
    }

    public function testGetDayStatusEmpty(): void
    {
        $status = $this->dailyPresenceService->getDayStatus([]);
        $this->assertEquals('absent', $status);
    }

    public function testGetDayStatusWithEntries(): void
    {
        $entries = [
            ['type' => 'entree', 'heure' => '09:00'],
            ['type' => 'sortie', 'heure' => '17:00']
        ];
        $status = $this->dailyPresenceService->getDayStatus($entries);
        $this->assertEquals('absent', $status);

        $entriesPresent = [
            ['type' => 'entree', 'heure' => '09:00']
        ];
        $status = $this->dailyPresenceService->getDayStatus($entriesPresent);
        $this->assertEquals('present', $status);
    }
}