<?php

namespace App\Tests\Unit\Service\User\Presence;

use App\Service\User\Presence\PresenceReportService;
use App\Tests\Shared\DatabaseKernelTestCase;

class PresenceReportServiceTest extends DatabaseKernelTestCase
{
    private PresenceReportService $presenceReportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenceReportService = static::getContainer()->get(PresenceReportService::class);
    }

    public function testGenerateCSVReport(): void
    {
        $presences = [
            'organisation' => 'Test Organisation',
            'period' => ['start' => '2024-01-01', 'end' => '2024-01-07'],
            'users' => [
                [
                    'user' => [
                        'id' => 1,
                        'nom' => 'Dupont',
                        'prenom' => 'Jean',
                        'email' => 'jean.dupont@test.com'
                    ],
                    'presence' => [
                        'daily_details' => [
                            [
                                'date' => '2024-01-01',
                                'total_hours' => 8.0,
                                'entries' => [
                                    ['type' => 'entree', 'heure' => '09:00'],
                                    ['type' => 'sortie', 'heure' => '17:00']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->presenceReportService->generateCSVReport($presences);

        $this->assertIsString($result);
        $this->assertStringContainsString('Date,Nom,Prénom,Email,Heures Travaillées,Statut', $result);
        $this->assertStringContainsString('2024-01-01,Dupont,Jean,jean.dupont@test.com,8.00,absent', $result);
    }

    public function testGenerateCSVReportWithSpecialCharacters(): void
    {
        $presences = [
            'organisation' => 'Test Organisation',
            'period' => ['start' => '2024-01-01', 'end' => '2024-01-01'],
            'users' => [
                [
                    'user' => [
                        'id' => 1,
                        'nom' => 'Dupont, Jr.',
                        'prenom' => 'Jean "Johnny"',
                        'email' => 'jean@test.com'
                    ],
                    'presence' => [
                        'daily_details' => [
                            [
                                'date' => '2024-01-01',
                                'total_hours' => 8.0,
                                'entries' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->presenceReportService->generateCSVReport($presences);

        $this->assertIsString($result);
        $this->assertStringContainsString('"Dupont, Jr."', $result);
        $this->assertStringContainsString('"Jean ""Johnny"""', $result);
    }
}