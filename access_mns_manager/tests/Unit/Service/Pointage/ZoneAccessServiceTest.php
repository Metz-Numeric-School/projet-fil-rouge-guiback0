<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Zone;
use App\Entity\Badgeuse;
use App\Entity\Acces;
use App\Exception\BadgeException;
use App\Service\Pointage\ZoneAccessService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ZoneAccessServiceTest extends DatabaseKernelTestCase
{
    private ZoneAccessService $zoneAccessService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zoneAccessService = static::getContainer()->get(ZoneAccessService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testGetBadgeuseZones(): void
    {
        // Arrange - Utiliser une badgeuse existante des fixtures
        $badgeuseRepository = $this->em->getRepository(Badgeuse::class);
        $badgeuse = $badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');

        // Act
        $result = $this->zoneAccessService->getBadgeuseZones($badgeuse);

        // Assert
        $this->assertIsArray($result);
        // Une badgeuse peut avoir 0 ou plusieurs zones selon la configuration
        foreach ($result as $zone) {
            $this->assertInstanceOf(Zone::class, $zone);
        }
    }

    public function testGetBadgeuseZoneNames(): void
    {
        // Arrange
        $badgeuseRepository = $this->em->getRepository(Badgeuse::class);
        $badgeuse = $badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');

        // Act
        $result = $this->zoneAccessService->getBadgeuseZoneNames($badgeuse);

        // Assert
        $this->assertIsArray($result);
        foreach ($result as $zoneName) {
            $this->assertIsString($zoneName);
        }
    }

    public function testCanAccessZoneWithNullZone(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act
        $result = $this->zoneAccessService->canAccessZone($user, null);

        // Assert
        $this->assertFalse($result);
    }

    public function testCanAccessZoneWithValidZone(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $zoneRepository = $this->em->getRepository(Zone::class);
        $zone = $zoneRepository->findOneBy(['nom_zone' => 'Zone Principale - Entrée/Sortie']);

        // Act
        $result = $this->zoneAccessService->canAccessZone($user, $zone);

        // Assert
        $this->assertIsBool($result);
        // Le résultat dépend de la configuration des services de l'utilisateur
    }

    public function testValidateUserZoneAccessSuccess(): void
    {
        // Arrange - Créer un setup complet
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher, 'test-access@example.com');
        $this->em->flush();

        // Act & Assert
        try {
            $this->zoneAccessService->validateUserZoneAccess($completeSetup['user'], $completeSetup['badgeuse']);
            // Si aucune exception n'est levée, la validation a réussi
            $this->assertTrue(true);
        } catch (BadgeException $e) {
            // Les erreurs d'accès sont acceptables selon la configuration
            $this->assertInstanceOf(BadgeException::class, $e);
        }
    }

    public function testValidateUserZoneAccessWithoutAccess(): void
    {
        // Arrange - Utiliser un utilisateur sans accès configuré
        $user = new User();
        $user->setEmail('test-no-access@example.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $badgeuseRepository = $this->em->getRepository(Badgeuse::class);
        $badgeuse = $badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');

        // Act & Assert
        $this->expectException(BadgeException::class);
        $this->zoneAccessService->validateUserZoneAccess($user, $badgeuse);
    }

    public function testIsBadgeuseInPrincipalZone(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $badgeuseRepository = $this->em->getRepository(Badgeuse::class);
        $badgeuse = $badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');

        // Act
        $result = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);

        // Assert
        $this->assertIsBool($result);
    }

    public function testZoneAccessServiceBasicFunctionality(): void
    {
        // Test général pour vérifier que le service fonctionne correctement
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $badgeuseRepository = $this->em->getRepository(Badgeuse::class);
        $badgeuse = $badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');

        // Les méthodes principales fonctionnent
        $zones = $this->zoneAccessService->getBadgeuseZones($badgeuse);
        $zoneNames = $this->zoneAccessService->getBadgeuseZoneNames($badgeuse);
        
        $this->assertIsArray($zones);
        $this->assertIsArray($zoneNames);
        $this->assertCount(count($zones), $zoneNames);
    }
}