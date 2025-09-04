<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Service\User\GDPRService;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GDPRServiceTest extends DatabaseKernelTestCase
{
    private GDPRService $gdprService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gdprService = static::getContainer()->get(GDPRService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testDeactivateAccount(): void
    {
        // Arrange - Utiliser l'utilisateur de test des fixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        
        // S'assurer que le compte est actif au départ
        $user->setCompteActif(true);
        $this->em->flush();
        $this->assertTrue($user->isCompteActif());

        // Act
        $result = $this->gdprService->deactivateAccount($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_suppression_prevue', $result);
        $this->assertFalse($user->isCompteActif());
        $this->assertNotNull($user->getDateSuppressionPrevue());
        
        // Vérifier que la date de suppression prévue est dans le futur
        $this->assertGreaterThan(new \DateTime(), $user->getDateSuppressionPrevue());
        
        // Vérifier le format de la date retournée
        if ($result['date_suppression_prevue']) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['date_suppression_prevue']);
        }
    }

    public function testExportUserData(): void
    {
        // Arrange - Utiliser l'utilisateur de test des fixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        // Act
        $result = $this->gdprService->exportUserData($user);

        // Assert - Vérifier la structure des données exportées
        $this->assertIsArray($result);
        
        $expectedKeys = ['personal_information', 'account_information', 'organisation', 'services', 'badges'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        
        // Vérifier les informations personnelles
        $personalInfo = $result['personal_information'];
        $this->assertIsArray($personalInfo);
        $this->assertArrayHasKey('email', $personalInfo);
        $this->assertArrayHasKey('nom', $personalInfo);
        $this->assertArrayHasKey('prenom', $personalInfo);
        $this->assertEquals('test@example.com', $personalInfo['email']);
        $this->assertEquals('TEST', $personalInfo['nom']);
        $this->assertEquals('User', $personalInfo['prenom']);
        
        // Vérifier les informations de compte
        $accountInfo = $result['account_information'];
        $this->assertIsArray($accountInfo);
        $this->assertArrayHasKey('compte_actif', $accountInfo);
        $this->assertArrayHasKey('roles', $accountInfo);
        $this->assertIsBool($accountInfo['compte_actif']);
        $this->assertIsArray($accountInfo['roles']);
        
        // Vérifier les autres sections
        $this->assertIsArray($result['organisation']);
        $this->assertIsArray($result['services']);
        $this->assertIsArray($result['badges']);
    }

    public function testExportUserDataWithCompleteProfile(): void
    {
        // Arrange - Créer un utilisateur avec un profil complet
        $user = new User();
        $user->setEmail('gdpr-test@example.com');
        $user->setNom('TestGDPR');
        $user->setPrenom('User');
        $user->setTelephone('0123456789');
        $user->setDateNaissance(new \DateTime('1990-01-01'));
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setPoste('Développeur');
        $user->setCompteActif(true);
        $this->em->persist($user);
        $this->em->flush();

        // Act
        $result = $this->gdprService->exportUserData($user);

        // Assert - Vérifier que les données supplémentaires sont exportées
        $personalInfo = $result['personal_information'];
        $this->assertEquals('0123456789', $personalInfo['telephone']);
        $this->assertEquals('1990-01-01', $personalInfo['date_naissance']);
        $this->assertEquals('Développeur', $personalInfo['poste']);
        
        $accountInfo = $result['account_information'];
        $this->assertTrue($accountInfo['compte_actif']);
    }

    public function testExportUserDataStructure(): void
    {
        // Arrange
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        // Act
        $result = $this->gdprService->exportUserData($user);

        // Assert - Vérifier la structure détaillée de chaque section
        
        // Section personal_information
        $personalKeys = ['email', 'nom', 'prenom', 'telephone', 'date_naissance', 'date_inscription', 'poste', 'horraire', 'heure_debut', 'jours_semaine_travaille'];
        foreach ($personalKeys as $key) {
            $this->assertArrayHasKey($key, $result['personal_information']);
        }
        
        // Section account_information
        $accountKeys = ['compte_actif', 'roles', 'date_derniere_connexion', 'date_derniere_modification', 'date_suppression_prevue'];
        foreach ($accountKeys as $key) {
            $this->assertArrayHasKey($key, $result['account_information']);
        }
        
        // Vérifier les types de données
        $this->assertIsBool($result['account_information']['compte_actif']);
        $this->assertIsArray($result['account_information']['roles']);
    }

    public function testDeactivateAccountMultipleTimes(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('deactivate-test@example.com');
        $user->setNom('TestDeactivate');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setCompteActif(true);
        $this->em->persist($user);
        $this->em->flush();

        // Act - Désactiver le compte une première fois
        $result1 = $this->gdprService->deactivateAccount($user);
        $dateSuppressionPrevue1 = $user->getDateSuppressionPrevue();
        
        // Désactiver le compte une deuxième fois
        $result2 = $this->gdprService->deactivateAccount($user);
        $dateSuppressionPrevue2 = $user->getDateSuppressionPrevue();

        // Assert
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertFalse($user->isCompteActif());
        
        // La date de suppression peut légèrement changer selon l'implémentation
        // On vérifie surtout que les deux dates sont proches (quelques millisecondes d'écart max)
        $this->assertLessThan(10, abs($dateSuppressionPrevue1->getTimestamp() - $dateSuppressionPrevue2->getTimestamp()));
    }

    public function testExportUserDataWithMissingInformation(): void
    {
        // Arrange - Utilisateur avec informations minimales
        $user = new User();
        $user->setEmail('minimal-test@example.com');
        $user->setNom('Minimal');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        // Ne pas définir telephone, date_naissance, poste, etc.
        $this->em->persist($user);
        $this->em->flush();

        // Act
        $result = $this->gdprService->exportUserData($user);

        // Assert - Le service ne devrait pas planter avec des informations manquantes
        $this->assertIsArray($result);
        $this->assertArrayHasKey('personal_information', $result);
        
        $personalInfo = $result['personal_information'];
        $this->assertEquals('minimal-test@example.com', $personalInfo['email']);
        $this->assertEquals('Minimal', $personalInfo['nom']);
        $this->assertEquals('User', $personalInfo['prenom']);
        
        // Les champs optionnels peuvent être null
        $this->assertArrayHasKey('telephone', $personalInfo);
        $this->assertArrayHasKey('date_naissance', $personalInfo);
        $this->assertArrayHasKey('poste', $personalInfo);
    }
}