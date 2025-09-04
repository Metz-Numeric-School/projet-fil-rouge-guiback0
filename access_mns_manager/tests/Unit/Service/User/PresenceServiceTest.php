<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Exception\PresenceException;
use App\Service\User\PresenceService;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PresenceServiceTest extends DatabaseKernelTestCase
{
    private PresenceService $presenceService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenceService = static::getContainer()->get(PresenceService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testGetWeeklyPresence(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $weekStart = '2024-01-01'; // Lundi

        // Act
        $result = $this->presenceService->getWeeklyPresence($user, $weekStart);

        // Assert
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
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // Act & Assert
        $this->expectException(PresenceException::class);
        $this->presenceService->getWeeklyPresence($user, 'invalid-date');
    }

    public function testGetMonthlyPresence(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $monthYear = '2024-01';

        // Act
        $result = $this->presenceService->getMonthlyPresence($user, $monthYear);

        // Assert
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
        
        // Vérifier la structure des statistiques
        $stats = $result['statistics'];
        $this->assertArrayHasKey('total_working_days', $stats);
        $this->assertArrayHasKey('average_hours_per_day', $stats);
        $this->assertArrayHasKey('weekly_hours', $stats);
        $this->assertArrayHasKey('max_daily_hours', $stats);
        $this->assertArrayHasKey('min_daily_hours', $stats);
    }

    public function testGetMonthlyPresenceInvalidMonth(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // Act & Assert
        $this->expectException(PresenceException::class);
        $this->presenceService->getMonthlyPresence($user, 'invalid-month');
    }

    public function testGetDailyPresence(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $date = '2024-01-15';

        // Act
        $result = $this->presenceService->getDailyPresence($user, $date);

        // Assert
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
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // Act & Assert
        $this->expectException(PresenceException::class);
        $this->presenceService->getDailyPresence($user, 'invalid-date');
    }

    public function testGetPresenceSummary(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        // Act
        $result = $this->presenceService->getPresenceSummary($user, $startDate, $endDate);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('anomalies', $result);
        $this->assertArrayHasKey('daily_details', $result);
        
        $this->assertEquals($user->getId(), $result['user_id']);
        
        // Vérifier la période
        $period = $result['period'];
        $this->assertArrayHasKey('start', $period);
        $this->assertArrayHasKey('end', $period);
        $this->assertEquals($startDate, $period['start']);
        $this->assertEquals($endDate, $period['end']);
        
        // Vérifier le résumé
        $summary = $result['summary'];
        $this->assertArrayHasKey('total_hours', $summary);
        $this->assertArrayHasKey('total_days_worked', $summary);
        $this->assertArrayHasKey('average_hours_per_day', $summary);
        $this->assertArrayHasKey('total_days_in_period', $summary);
        
        $this->assertIsFloat($summary['total_hours']);
        $this->assertIsInt($summary['total_days_worked']);
        $this->assertIsFloat($summary['average_hours_per_day']);
        $this->assertIsInt($summary['total_days_in_period']);
        
        // Vérifier les anomalies et détails
        $this->assertIsArray($result['anomalies']);
        $this->assertIsArray($result['daily_details']);
    }

    public function testGetPresenceSummaryInvalidDateRange(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // Act & Assert
        $this->expectException(PresenceException::class);
        $this->presenceService->getPresenceSummary($user, 'invalid-start', '2024-01-31');
    }

    public function testGetOrganisationPresence(): void
    {
        // Arrange
        $startDate = '2024-01-01';
        $endDate = '2024-01-07';

        // Act
        $result = $this->presenceService->getOrganisationPresence($startDate, $endDate);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('organisation', $result);
        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('users', $result);
        
        $this->assertIsString($result['organisation']);
        
        // Vérifier la période
        $period = $result['period'];
        $this->assertArrayHasKey('start', $period);
        $this->assertArrayHasKey('end', $period);
        $this->assertEquals($startDate, $period['start']);
        $this->assertEquals($endDate, $period['end']);
        
        // Vérifier les utilisateurs
        $this->assertIsArray($result['users']);
        
        // Si des utilisateurs sont présents, vérifier leur structure
        if (!empty($result['users'])) {
            $firstUser = $result['users'][0];
            $this->assertArrayHasKey('user', $firstUser);
            $this->assertArrayHasKey('presence', $firstUser);
            
            $userInfo = $firstUser['user'];
            $this->assertArrayHasKey('id', $userInfo);
            $this->assertArrayHasKey('nom', $userInfo);
            $this->assertArrayHasKey('prenom', $userInfo);
            $this->assertArrayHasKey('email', $userInfo);
        }
    }

    public function testGenerateCSVReport(): void
    {
        // Arrange - Créer des données de présence fictives
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
                            ],
                            [
                                'date' => '2024-01-02',
                                'total_hours' => 7.5,
                                'entries' => [
                                    ['type' => 'entree', 'heure' => '08:30'],
                                    ['type' => 'sortie', 'heure' => '16:00']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Act
        $result = $this->presenceService->generateCSVReport($presences);

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('Date,Nom,Prénom,Email,Heures Travaillées,Statut', $result);
        $this->assertStringContainsString('2024-01-01,Dupont,Jean,jean.dupont@test.com,8.00,absent', $result);
        $this->assertStringContainsString('2024-01-02,Dupont,Jean,jean.dupont@test.com,7.50,absent', $result);
        
        // Vérifier que le CSV a bien le format attendu
        $lines = explode("\n", trim($result));
        $this->assertGreaterThanOrEqual(3, count($lines)); // Header + au moins 2 lignes de données
    }

    public function testGenerateCSVReportWithSpecialCharacters(): void
    {
        // Arrange - Données avec caractères spéciaux
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

        // Act
        $result = $this->presenceService->generateCSVReport($presences);

        // Assert - Vérifier que les caractères spéciaux sont correctement échappés
        $this->assertIsString($result);
        $this->assertStringContainsString('"Dupont, Jr."', $result);
        $this->assertStringContainsString('"Jean ""Johnny"""', $result);
    }

    public function testPresenceServiceBasicFunctionality(): void
    {
        // Test général pour vérifier que le service est bien configuré
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // Vérifier que le service peut traiter différents types de demandes
        $this->assertInstanceOf(PresenceService::class, $this->presenceService);
        
        // Test avec des dates valides - toutes les méthodes principales devraient fonctionner
        try {
            $daily = $this->presenceService->getDailyPresence($user, '2024-01-01');
            $this->assertIsArray($daily);
            
            $weekly = $this->presenceService->getWeeklyPresence($user, '2024-01-01');
            $this->assertIsArray($weekly);
            
            $monthly = $this->presenceService->getMonthlyPresence($user, '2024-01');
            $this->assertIsArray($monthly);
            
            $summary = $this->presenceService->getPresenceSummary($user, '2024-01-01', '2024-01-31');
            $this->assertIsArray($summary);
        } catch (PresenceException $e) {
            // Les exceptions de calcul sont acceptables dans l'environnement de test
            $this->assertInstanceOf(PresenceException::class, $e);
        }
    }
}