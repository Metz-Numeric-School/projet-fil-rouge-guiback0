<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Badgeuse;
use App\Service\Pointage\PointageValidationService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PointageValidationServiceTest extends DatabaseKernelTestCase
{
    private PointageValidationService $pointageValidationService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointageValidationService = static::getContainer()->get(PointageValidationService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testValidatePointageActionSuccess(): void
    {
        // Arrange
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher);
        $this->em->flush();
        
        $badgeuseId = $completeSetup['badgeuse']->getId();
        
        // Act
        $result = $this->pointageValidationService->validatePointageAction($completeSetup['user'], $badgeuseId);
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_valid']);
        $this->assertTrue($result['can_proceed']);
        $this->assertArrayHasKey('service_type', $result);
    }

    public function testValidatePointageActionBadgeuseNotFound(): void
    {
        // Arrange
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher);
        $this->em->flush();
        
        $invalidBadgeuseId = 99999;
        
        // Act
        $result = $this->pointageValidationService->validatePointageAction($completeSetup['user'], $invalidBadgeuseId);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertFalse($result['is_valid']);
        $this->assertEquals('BADGEUSE_NOT_FOUND', $result['error']);
        $this->assertStringContainsString('Badgeuse non trouvée', $result['message']);
    }

    public function testValidatePointageActionHandlesExceptions(): void
    {
        // Arrange - Utilisateur non persisté
        $user = new User();
        $user->setEmail('invalid@test.com');
        
        // Act
        $result = $this->pointageValidationService->validatePointageAction($user, 1);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertFalse($result['is_valid']);
        $this->assertEquals('INTERNAL_ERROR', $result['error']);
        $this->assertStringContainsString('Erreur lors de la validation', $result['message']);
    }
}
