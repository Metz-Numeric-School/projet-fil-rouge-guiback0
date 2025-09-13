<?php

namespace App\Tests\Unit\Service\User\GDPR;

use App\Entity\User;
use App\Service\User\GDPR\UserDataExporterService;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataExporterServiceTest extends DatabaseKernelTestCase
{
    private UserDataExporterService $userDataExporterService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userDataExporterService = static::getContainer()->get(UserDataExporterService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testExportUserData(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $result = $this->userDataExporterService->exportUserData($user);

        $this->assertIsArray($result);
        
        $expectedKeys = ['personal_information', 'account_information', 'organisation', 'services', 'badges'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        
        $personalInfo = $result['personal_information'];
        $this->assertIsArray($personalInfo);
        $this->assertArrayHasKey('email', $personalInfo);
        $this->assertArrayHasKey('nom', $personalInfo);
        $this->assertArrayHasKey('prenom', $personalInfo);
        $this->assertEquals('test@example.com', $personalInfo['email']);
        
        $accountInfo = $result['account_information'];
        $this->assertIsArray($accountInfo);
        $this->assertArrayHasKey('compte_actif', $accountInfo);
        $this->assertArrayHasKey('roles', $accountInfo);
        $this->assertIsBool($accountInfo['compte_actif']);
        $this->assertIsArray($accountInfo['roles']);
        
        $this->assertIsArray($result['organisation']);
        $this->assertIsArray($result['services']);
        $this->assertIsArray($result['badges']);
    }

    public function testExportUserDataWithCompleteProfile(): void
    {
        $user = new User();
        $user->setEmail('gdpr-export-test@example.com');
        $user->setNom('TestGDPRExport');
        $user->setPrenom('User');
        $user->setTelephone('0123456789');
        $user->setDateNaissance(new \DateTime('1990-01-01'));
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setPoste('Développeur');
        $user->setCompteActif(true);
        $this->em->persist($user);
        $this->em->flush();

        $result = $this->userDataExporterService->exportUserData($user);

        $personalInfo = $result['personal_information'];
        $this->assertEquals('0123456789', $personalInfo['telephone']);
        $this->assertEquals('1990-01-01', $personalInfo['date_naissance']);
        $this->assertEquals('Développeur', $personalInfo['poste']);
        
        $accountInfo = $result['account_information'];
        $this->assertTrue($accountInfo['compte_actif']);
    }

    public function testExportUserDataStructure(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        $result = $this->userDataExporterService->exportUserData($user);

        $personalKeys = ['email', 'nom', 'prenom', 'telephone', 'date_naissance', 'date_inscription', 'poste', 'horraire', 'heure_debut', 'jours_semaine_travaille'];
        foreach ($personalKeys as $key) {
            $this->assertArrayHasKey($key, $result['personal_information']);
        }
        
        $accountKeys = ['compte_actif', 'roles', 'date_derniere_connexion', 'date_derniere_modification', 'date_suppression_prevue'];
        foreach ($accountKeys as $key) {
            $this->assertArrayHasKey($key, $result['account_information']);
        }
        
        $this->assertIsBool($result['account_information']['compte_actif']);
        $this->assertIsArray($result['account_information']['roles']);
    }
}