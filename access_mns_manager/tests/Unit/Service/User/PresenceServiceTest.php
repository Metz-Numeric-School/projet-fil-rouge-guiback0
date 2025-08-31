<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\User\PresenceService;
use App\Service\User\UserService;
use App\Service\Pointage\WorkTimeCalculatorService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PresenceServiceTest extends TestCase
{
    private PresenceService $presenceService;
    private WorkTimeCalculatorService&MockObject $workTimeCalculator;
    private UserService&MockObject $userService;

    protected function setUp(): void
    {
        $this->workTimeCalculator = $this->createMock(WorkTimeCalculatorService::class);
        $this->userService = $this->createMock(UserService::class);
        
        $this->presenceService = new PresenceService(
            $this->workTimeCalculator,
            $this->userService
        );
    }

    public function testGetWeeklyPresenceSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 40.5,
            'total_minutes' => 2430,
            'days' => [
                ['date' => '2024-01-01', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-02', 'total_hours' => 8.5, 'entries' => []]
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->with(
                $user,
                $this->callback(fn($date) => $date->format('Y-m-d') === '2024-01-01'),
                $this->callback(fn($date) => $date->format('Y-m-d') === '2024-01-06')
            )
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getWeeklyPresence($user, '2024-01-01');

        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2024-01-01', $result['week']);
        $this->assertEquals(40.5, $result['total_hours']);
        $this->assertEquals(2430, $result['total_minutes']);
        $this->assertCount(2, $result['days']);
    }

    public function testGetWeeklyPresenceWithInvalidDate(): void
    {
        $user = $this->createMock(User::class);

        $this->expectException(PresenceException::class);
        $this->expectExceptionMessage('Erreur lors du calcul de la présence hebdomadaire');

        $this->presenceService->getWeeklyPresence($user, 'invalid-date');
    }

    public function testGetWeeklyPresenceWithCalculatorException(): void
    {
        $user = $this->createMock(User::class);

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willThrowException(new \Exception('Calculator error'));

        $this->expectException(PresenceException::class);
        $this->expectExceptionMessage('Erreur lors du calcul de la présence hebdomadaire');

        $this->presenceService->getWeeklyPresence($user, '2024-01-01');
    }

    public function testGetMonthlyPresenceSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 160.0,
            'total_minutes' => 9600,
            'days' => [
                ['date' => '2024-01-01', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-02', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-03', 'total_hours' => 0, 'entries' => []],
                ['date' => '2024-01-04', 'total_hours' => 8.0, 'entries' => []]
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getMonthlyPresence($user, '2024-01');

        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2024-01', $result['month']);
        $this->assertEquals(160.0, $result['total_hours']);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertEquals(3, $result['statistics']['total_working_days']);
        $this->assertEquals(8.0, $result['statistics']['max_daily_hours']);
    }

    public function testGetDailyPresenceSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 8.5,
            'total_minutes' => 510,
            'days' => [
                [
                    'date' => '2024-01-01',
                    'total_hours' => 8.5,
                    'entries' => [
                        ['type' => 'entree', 'heure' => '08:30'],
                        ['type' => 'sortie', 'heure' => '17:00']
                    ]
                ]
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getDailyPresence($user, '2024-01-01');

        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2024-01-01', $result['date']);
        $this->assertEquals(8.5, $result['total_hours']);
        $this->assertCount(2, $result['entries']);
        $this->assertEquals('absent', $result['status']); // Last entry is 'sortie'
    }

    public function testGetDailyPresenceWithEmptyDay(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 0,
            'total_minutes' => 0,
            'days' => []
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getDailyPresence($user, '2024-01-01');

        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('2024-01-01', $result['date']);
        $this->assertEquals(0, $result['total_hours']);
        $this->assertEquals([], $result['entries']);
        $this->assertEquals('absent', $result['status']);
    }

    public function testGetPresenceSummaryWithAnomalies(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 50.0,
            'total_minutes' => 3000,
            'days' => [
                ['date' => '2024-01-01', 'total_hours' => 15.0, 'entries' => ['type' => 'entree']], // Long day
                ['date' => '2024-01-02', 'total_hours' => 1.5, 'entries' => ['type' => 'entree']], // Short day
                ['date' => '2024-01-03', 'total_hours' => 8.0, 'entries' => ['type' => 'entree', 'type' => 'sortie']],
                ['date' => '2024-01-04', 'total_hours' => 0, 'entries' => []],
                ['date' => '2024-01-05', 'total_hours' => 8.0, 'entries' => array_fill(0, 12, ['type' => 'entree'])] // Too many badges
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getPresenceSummary($user, '2024-01-01', '2024-01-05');

        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals(50.0, $result['summary']['total_hours']);
        $this->assertEquals(4, $result['summary']['total_days_worked']); // Days with hours > 0
        $this->assertEquals(12.5, $result['summary']['average_hours_per_day']); // 50/4
        $this->assertGreaterThan(0, count($result['anomalies']));
        
        // Check for specific anomalies
        $anomalyTypes = array_column($result['anomalies'], 'type');
        $this->assertContains('LONG_DAY', $anomalyTypes);
        $this->assertContains('SHORT_DAY', $anomalyTypes);
        $this->assertContains('INCOMPLETE_BADGE', $anomalyTypes);
        $this->assertContains('TOO_MANY_BADGES', $anomalyTypes);
    }

    public function testGetOrganisationPresenceSuccess(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user1->method('getNom')->willReturn('Doe');
        $user1->method('getPrenom')->willReturn('John');
        $user1->method('getEmail')->willReturn('john@test.com');

        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user2->method('getNom')->willReturn('Smith');
        $user2->method('getPrenom')->willReturn('Jane');
        $user2->method('getEmail')->willReturn('jane@test.com');

        $this->userService
            ->expects($this->once())
            ->method('getOrganisationUsers')
            ->willReturn([$user1, $user2]);

        $organisation = $this->createMock(\App\Entity\Organisation::class);
        $organisation->method('getNomOrganisation')->willReturn('Test Org');

        $this->userService
            ->expects($this->once())
            ->method('getCurrentUserOrganisation')
            ->willReturn($organisation);

        // Mock calculator responses for both users
        $this->workTimeCalculator
            ->expects($this->exactly(2))
            ->method('calculateWorkingTime')
            ->willReturnOnConsecutiveCalls(
                ['total_hours' => 40.0, 'total_minutes' => 2400, 'days' => []],
                ['total_hours' => 35.0, 'total_minutes' => 2100, 'days' => []]
            );

        $result = $this->presenceService->getOrganisationPresence('2024-01-01', '2024-01-07');

        $this->assertEquals('Test Org', $result['organisation']);
        $this->assertEquals('2024-01-01', $result['period']['start']);
        $this->assertEquals('2024-01-07', $result['period']['end']);
        $this->assertCount(2, $result['users']);
        $this->assertEquals('john@test.com', $result['users'][0]['user']['email']);
        $this->assertEquals(40.0, $result['users'][0]['presence']['summary']['total_hours']);
    }

    public function testGetOrganisationPresenceWithNoUsers(): void
    {
        $this->userService
            ->expects($this->once())
            ->method('getOrganisationUsers')
            ->willReturn([]);

        $this->userService
            ->expects($this->once())
            ->method('getCurrentUserOrganisation')
            ->willReturn(null);

        $result = $this->presenceService->getOrganisationPresence('2024-01-01', '2024-01-07');

        $this->assertEquals('Organisation inconnue', $result['organisation']);
        $this->assertEquals([], $result['users']);
    }

    public function testGenerateCSVReportWithValidData(): void
    {
        $presenceData = [
            'organisation' => 'Test Org',
            'period' => ['start' => '2024-01-01', 'end' => '2024-01-07'],
            'users' => [
                [
                    'user' => [
                        'id' => 1,
                        'nom' => 'Doe',
                        'prenom' => 'John',
                        'email' => 'john@test.com'
                    ],
                    'presence' => [
                        'daily_details' => [
                            [
                                'date' => '2024-01-01',
                                'total_hours' => 8.0,
                                'entries' => [
                                    ['type' => 'entree', 'heure' => '08:30'],
                                    ['type' => 'sortie', 'heure' => '17:00']
                                ]
                            ],
                            [
                                'date' => '2024-01-02',
                                'total_hours' => 7.5,
                                'entries' => [
                                    ['type' => 'entree', 'heure' => '09:00'],
                                    ['type' => 'sortie', 'heure' => '16:30']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $csv = $this->presenceService->generateCSVReport($presenceData);

        $this->assertStringContainsString('Date,Nom,Prénom,Email,Heures Travaillées,Statut', $csv);
        $this->assertStringContainsString('2024-01-01,Doe,John,john@test.com,8.00,absent', $csv);
        $this->assertStringContainsString('2024-01-02,Doe,John,john@test.com,7.50,absent', $csv);
    }

    public function testGenerateCSVReportWithSpecialCharacters(): void
    {
        $presenceData = [
            'users' => [
                [
                    'user' => [
                        'nom' => 'D\'Artagnan',
                        'prenom' => 'Jean, Paul',
                        'email' => 'test"quote@test.com'
                    ],
                    'presence' => [
                        'daily_details' => [
                            [
                                'date' => '2024-01-01',
                                'total_hours' => 8.0,
                                'entries' => [['type' => 'sortie']]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $csv = $this->presenceService->generateCSVReport($presenceData);

        $this->assertStringContainsString('"Jean, Paul"', $csv); // Comma escaped
        $this->assertStringContainsString('"test""quote@test.com"', $csv); // Quote escaped
        $this->assertStringContainsString('D\'Artagnan', $csv); // Apostrophe preserved
    }

    public function testCalculateMonthlyStatsPrivateMethod(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 88.0,
            'total_minutes' => 5280,
            'days' => [
                ['date' => '2024-01-01', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-02', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-03', 'total_hours' => 0, 'entries' => []],     // Weekend/absent
                ['date' => '2024-01-04', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-05', 'total_hours' => 12.0, 'entries' => []],  // Long day
                ['date' => '2024-01-08', 'total_hours' => 8.0, 'entries' => []],   // Next week
                ['date' => '2024-01-09', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-10', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-11', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-12', 'total_hours' => 8.0, 'entries' => []],
                ['date' => '2024-01-15', 'total_hours' => 8.0, 'entries' => []],   // Week 3
                ['date' => '2024-01-16', 'total_hours' => 8.0, 'entries' => []]
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getMonthlyPresence($user, '2024-01');

        $stats = $result['statistics'];
        $this->assertEquals(11, $stats['total_working_days']); // Days with hours > 0
        $this->assertEquals(8.0, $stats['average_hours_per_day']); // 88/11
        $this->assertEquals(12.0, $stats['max_daily_hours']);
        $this->assertEquals(8.0, $stats['min_daily_hours']);
        $this->assertArrayHasKey('weekly_hours', $stats);
    }

    public function testGetDayStatusWithPresentUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 4.0,
            'total_minutes' => 240,
            'days' => [
                [
                    'date' => '2024-01-01',
                    'total_hours' => 4.0,
                    'entries' => [
                        ['type' => 'entree', 'heure' => '08:30'],
                        ['type' => 'sortie', 'heure' => '12:30'],
                        ['type' => 'entree', 'heure' => '13:30'] // Still present
                    ]
                ]
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getDailyPresence($user, '2024-01-01');

        $this->assertEquals('present', $result['status']); // Last entry is 'entree'
    }

    public function testDetectAnomaliesInPresenceSummary(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $mockWorkingTime = [
            'total_hours' => 25.5,
            'total_minutes' => 1530,
            'days' => [
                ['date' => '2024-01-01', 'total_hours' => 15.0, 'entries' => ['type' => 'entree', 'type' => 'sortie']], // Long day
                ['date' => '2024-01-02', 'total_hours' => 1.5, 'entries' => ['type' => 'entree']], // Short day + incomplete
                ['date' => '2024-01-03', 'total_hours' => 9.0, 'entries' => array_fill(0, 15, ['type' => 'entree'])] // Too many badges
            ]
        ];

        $this->workTimeCalculator
            ->expects($this->once())
            ->method('calculateWorkingTime')
            ->willReturn($mockWorkingTime);

        $result = $this->presenceService->getPresenceSummary($user, '2024-01-01', '2024-01-03');

        $anomalies = $result['anomalies'];
        $this->assertGreaterThan(0, count($anomalies));
        
        $anomalyTypes = array_column($anomalies, 'type');
        $this->assertContains('LONG_DAY', $anomalyTypes);
        $this->assertContains('SHORT_DAY', $anomalyTypes);
        $this->assertContains('INCOMPLETE_BADGE', $anomalyTypes);
        $this->assertContains('TOO_MANY_BADGES', $anomalyTypes);
    }
}