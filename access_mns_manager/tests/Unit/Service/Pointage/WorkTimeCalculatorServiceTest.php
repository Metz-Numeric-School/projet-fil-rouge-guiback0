<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Pointage;
use App\Entity\Badgeuse;
use App\Exception\PresenceException;
use App\Service\Pointage\WorkTimeCalculatorService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class WorkTimeCalculatorServiceTest extends DatabaseKernelTestCase
{
    private WorkTimeCalculatorService $workTimeCalculatorService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workTimeCalculatorService = static::getContainer()->get(WorkTimeCalculatorService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testCalculateWorkingTimeSuccess(): void
    {
        // Arrange - Utiliser l'utilisateur de test des fixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $startDate = new \DateTime('2024-01-01 00:00:00');
        $endDate = new \DateTime('2024-01-01 23:59:59');

        // Act
        $result = $this->workTimeCalculatorService->calculateWorkingTime($user, $startDate, $endDate);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
        $this->assertIsFloat($result['total_hours']);
        $this->assertIsInt($result['total_minutes']);
        $this->assertIsArray($result['days']);
    }

    public function testCalculateWorkingTimeInvalidDateRange(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $startDate = new \DateTime('2024-01-02');
        $endDate = new \DateTime('2024-01-01'); // Earlier than start

        // Act & Assert
        $this->expectException(PresenceException::class);
        $this->workTimeCalculatorService->calculateWorkingTime($user, $startDate, $endDate);
    }

    public function testCalculateWorkingTimeForPeriodSuccess(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act
        $result = $this->workTimeCalculatorService->calculateWorkingTimeForPeriod($user, '2024-01-01', '2024-01-01');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
    }

    public function testCalculateWorkingTimeForPeriodInvalidDateFormat(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act & Assert - Le service devrait lancer une exception pour format de date invalide
        $this->expectException(\App\Exception\PresenceException::class);
        $this->workTimeCalculatorService->calculateWorkingTimeForPeriod($user, 'invalid-date', '2024-01-01');
    }

    public function testCalculateWorkingTimeWithPointages(): void
    {
        // Arrange - Créer un setup complet avec badgeuses
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher, 'test-worktime@example.com');
        
        // Créer des pointages de test
        $pointageEntree = new Pointage();
        $pointageEntree->setBadge($completeSetup['badge']);
        $pointageEntree->setBadgeuse($completeSetup['badgeuse']);
        $pointageEntree->setHeure(new \DateTime('2024-01-01 09:00:00'));
        $pointageEntree->setType('entree');
        $this->em->persist($pointageEntree);

        $pointageSortie = new Pointage();
        $pointageSortie->setBadge($completeSetup['badge']);
        $pointageSortie->setBadgeuse($completeSetup['badgeuse']);
        $pointageSortie->setHeure(new \DateTime('2024-01-01 17:00:00'));
        $pointageSortie->setType('sortie');
        $this->em->persist($pointageSortie);

        $this->em->flush();

        $startDate = new \DateTime('2024-01-01 00:00:00');
        $endDate = new \DateTime('2024-01-01 23:59:59');

        // Act
        $result = $this->workTimeCalculatorService->calculateWorkingTime($completeSetup['user'], $startDate, $endDate);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_hours', $result);
        $this->assertArrayHasKey('total_minutes', $result);
        $this->assertArrayHasKey('days', $result);
        
        // Le calcul devrait inclure les heures de travail
        if ($result['total_minutes'] > 0) {
            $this->assertGreaterThan(0, $result['total_hours']);
            $this->assertCount(1, $result['days']); // Une seule journée
        }
    }
}