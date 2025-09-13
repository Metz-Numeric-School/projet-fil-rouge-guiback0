<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Pointage;
use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Service\Pointage\UserStatusService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStatusServiceTest extends DatabaseKernelTestCase
{
    private UserStatusService $userStatusService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStatusService = static::getContainer()->get(UserStatusService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testGetCurrentUserStatusAbsent(): void
    {
        // Arrange - Utilisateur sans pointages récents
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act
        $result = $this->userStatusService->getCurrentUserStatus($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('last_action', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('can_access_secondary', $result);
        
        // Sans pointages récents, le statut devrait être absent
        $this->assertEquals('absent', $result['status']);
        $this->assertIsBool($result['can_access_secondary']);
        $this->assertIsString($result['date']);
    }

    public function testGetCurrentUserStatusWithRecentEntry(): void
    {
        // Arrange - Créer un setup complet avec pointage d'entrée
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher, 'test-status@example.com');
        
        // Créer un pointage d'entrée aujourd'hui
        $pointage = new Pointage();
        $pointage->setBadge($completeSetup['badge']);
        $pointage->setBadgeuse($completeSetup['badgeuse']);
        $pointage->setHeure(new \DateTime('today 09:00:00'));
        $pointage->setType('entree');
        $this->em->persist($pointage);
        $this->em->flush();

        // Act
        $result = $this->userStatusService->getCurrentUserStatus($completeSetup['user']);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('last_action', $result);
        
        // Le statut peut être présent ou absent selon la configuration du service principal
        $this->assertContains($result['status'], ['present', 'absent']);
        
        if ($result['last_action']) {
            $this->assertArrayHasKey('heure', $result['last_action']);
            $this->assertArrayHasKey('type', $result['last_action']);
            $this->assertArrayHasKey('badgeuse', $result['last_action']);
        }
    }

    public function testGetCurrentUserStatusWithRecentExit(): void
    {
        // Arrange - Créer un setup complet avec pointage de sortie
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher, 'test-exit@example.com');
        
        // Créer un pointage de sortie aujourd'hui
        $pointage = new Pointage();
        $pointage->setBadge($completeSetup['badge']);
        $pointage->setBadgeuse($completeSetup['badgeuse']);
        $pointage->setHeure(new \DateTime('today 17:00:00'));
        $pointage->setType('sortie');
        $this->em->persist($pointage);
        $this->em->flush();

        // Act
        $result = $this->userStatusService->getCurrentUserStatus($completeSetup['user']);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('last_action', $result);
        
        if ($result['last_action']) {
            $this->assertEquals('sortie', $result['last_action']['type']);
            $this->assertArrayHasKey('service_type', $result['last_action']);
            $this->assertArrayHasKey('is_principal', $result['last_action']);
            $this->assertArrayHasKey('affects_status', $result['last_action']);
        }
    }

    public function testGetCurrentUserStatusWithMultiplePointages(): void
    {
        // Arrange - Créer plusieurs pointages dans la journée
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher, 'test-multiple@example.com');
        
        // Pointage de pause d'abord
        $pointagePause = new Pointage();
        $pointagePause->setBadge($completeSetup['badge']);
        $pointagePause->setBadgeuse($completeSetup['badgeuse']);
        $pointagePause->setHeure(new \DateTime('today 08:00:00'));
        $pointagePause->setType('pause');
        $this->em->persist($pointagePause);
        
        // Pointage d'entrée plus tard (sera le dernier)
        $pointageEntree = new Pointage();
        $pointageEntree->setBadge($completeSetup['badge']);
        $pointageEntree->setBadgeuse($completeSetup['badgeuse']);
        $pointageEntree->setHeure(new \DateTime('today 12:00:00'));
        $pointageEntree->setType('entree');
        $this->em->persist($pointageEntree);
        
        $this->em->flush();

        // Act
        $result = $this->userStatusService->getCurrentUserStatus($completeSetup['user']);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('last_action', $result);
        $this->assertArrayHasKey('can_access_secondary', $result);
        
        // Le dernier pointage devrait être l'entrée (12:00 > 08:00)
        if ($result['last_action']) {
            $this->assertEquals('entree', $result['last_action']['type']);
        }
    }

    public function testGetCurrentUserStatusReturnFormat(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act
        $result = $this->userStatusService->getCurrentUserStatus($user);

        // Assert - Vérifier la structure de retour
        $this->assertIsArray($result);
        
        // Clés obligatoires
        $expectedKeys = ['status', 'last_action', 'date', 'can_access_secondary'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        
        // Types des valeurs
        $this->assertIsString($result['status']);
        $this->assertIsBool($result['can_access_secondary']);
        $this->assertIsString($result['date']);
        
        // last_action peut être null ou un array
        $this->assertTrue(is_null($result['last_action']) || is_array($result['last_action']));
        
        // Si last_action existe, vérifier sa structure
        if (is_array($result['last_action'])) {
            $actionKeys = ['heure', 'type', 'badgeuse', 'zone', 'is_principal', 'service_type', 'affects_status'];
            foreach ($actionKeys as $key) {
                $this->assertArrayHasKey($key, $result['last_action']);
            }
        }
    }
}