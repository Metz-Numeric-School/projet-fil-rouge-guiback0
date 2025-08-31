<?php

namespace App\Tests\Functional\Controller\API\User;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Zone;
use App\Entity\Badge;
use App\Entity\UserBadge;
use App\Entity\Travailler;
use App\Entity\ServiceZone;
use App\Entity\Acces;
use App\Entity\Badgeuse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class UserControllerTest extends DatabaseWebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->jwtManager = $container->get(JWTTokenManagerInterface::class);
    }

    public function testGetCompleteProfileWithCompleteData(): void
    {
        $user = $this->createCompleteTestUser();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/user/profile/complete', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('user', $responseData['data']);
        $this->assertArrayHasKey('organisation', $responseData['data']);
        $this->assertArrayHasKey('services', $responseData['data']);
        $this->assertArrayHasKey('zones_accessibles', $responseData['data']);
        $this->assertArrayHasKey('badges', $responseData['data']);
        $this->assertArrayHasKey('acces_autorises', $responseData['data']);
        $this->assertArrayHasKey('badgeuses_autorisees', $responseData['data']);

        // Test user data
        $userData = $responseData['data']['user'];
        $this->assertEquals('complete.test@example.com', $userData['email']);
        $this->assertEquals('Doe', $userData['nom']);
        $this->assertEquals('John', $userData['prenom']);
        $this->assertEquals('0123456789', $userData['telephone']);
        $this->assertTrue($userData['compte_actif']);

        // Test organisation data
        $organisationData = $responseData['data']['organisation'];
        $this->assertNotNull($organisationData);
        $this->assertEquals('Test Organisation', $organisationData['nom_organisation']);
        $this->assertEquals('contact@test.com', $organisationData['email']);

        // Test services data
        $servicesData = $responseData['data']['services'];
        $this->assertNotNull($servicesData['current']);
        $this->assertEquals('Test Service', $servicesData['current']['nom_service']);
        $this->assertTrue($servicesData['current']['is_current']);
        $this->assertIsArray($servicesData['history']);

        // Test zones
        $zonesData = $responseData['data']['zones_accessibles'];
        $this->assertIsArray($zonesData);
        $this->assertNotEmpty($zonesData);

        // Test badges
        $badgesData = $responseData['data']['badges'];
        $this->assertIsArray($badgesData);
        $this->assertNotEmpty($badgesData);
        $this->assertEquals(200001, $badgesData[0]['numero_badge']);

        // Test access and badgeuses
        $accesData = $responseData['data']['acces_autorises'];
        $badgeusesData = $responseData['data']['badgeuses_autorisees'];
        $this->assertIsArray($accesData);
        $this->assertIsArray($badgeusesData);
    }

    public function testGetCompleteProfileWithoutToken(): void
    {
        $this->client->request('GET', '/manager/api/user/profile/complete', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateLastLoginWithValidToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/user/update-last-login', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Dernière connexion mise à jour', $responseData['message']);

        // Verify last login was updated
        $this->em->refresh($user);
        $this->assertNotNull($user->getDateDerniereConnexion());
    }

    public function testUpdateLastLoginWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/user/update-last-login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testDeactivateAccountWithValidToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $this->assertTrue($user->isCompteActif());

        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/user/deactivate', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertStringContainsString('Compte désactivé avec succès', $responseData['message']);
        $this->assertArrayHasKey('date_suppression_prevue', $responseData['data']);

        // Verify account was deactivated
        $this->em->refresh($user);
        $this->assertFalse($user->isCompteActif());
        $this->assertNotNull($user->getDateSuppressionPrevue());
    }

    public function testDeactivateAccountWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/user/deactivate', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testExportUserDataWithValidToken(): void
    {
        $user = $this->createCompleteTestUser();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/user/export-data', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Données personnelles exportées avec succès', $responseData['message']);
        $this->assertArrayHasKey('export_timestamp', $responseData);
        $this->assertArrayHasKey('gdpr_notice', $responseData);

        // Test exported data structure
        $exportedData = $responseData['data'];
        $this->assertArrayHasKey('personal_information', $exportedData);
        $this->assertArrayHasKey('account_information', $exportedData);
        $this->assertArrayHasKey('organisation', $exportedData);
        $this->assertArrayHasKey('services', $exportedData);
        $this->assertArrayHasKey('badges', $exportedData);

        // Test personal information
        $personalInfo = $exportedData['personal_information'];
        $this->assertEquals('complete.test@example.com', $personalInfo['email']);
        $this->assertEquals('Doe', $personalInfo['nom']);
        $this->assertEquals('John', $personalInfo['prenom']);

        // Test account information
        $accountInfo = $exportedData['account_information'];
        $this->assertTrue($accountInfo['compte_actif']);
        $this->assertContains('ROLE_USER', $accountInfo['roles']);

        // Test GDPR notice
        $this->assertStringContainsString('RGPD', $responseData['gdpr_notice']);
        $this->assertStringContainsString('portabilité des données', $responseData['gdpr_notice']);
    }

    public function testExportUserDataWithoutToken(): void
    {
        $this->client->request('GET', '/manager/api/user/export-data', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testGetCompleteProfileWithMinimalData(): void
    {
        $user = new User();
        $user->setEmail('minimal@example.com');
        $user->setNom('Minimal');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/user/profile/complete', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);

        // Test with minimal data
        $userData = $responseData['data']['user'];
        $this->assertEquals('minimal@example.com', $userData['email']);
        $this->assertEquals('Minimal', $userData['nom']);
        $this->assertEquals('User', $userData['prenom']);
        $this->assertNull($userData['telephone']);
        $this->assertNull($userData['poste']);

        // These should be null or empty arrays
        $this->assertNull($responseData['data']['organisation']);
        $this->assertNull($responseData['data']['services']['current']);
        $this->assertEmpty($responseData['data']['zones_accessibles']);
        $this->assertEmpty($responseData['data']['badges']);
        $this->assertEmpty($responseData['data']['acces_autorises']);
        $this->assertEmpty($responseData['data']['badgeuses_autorisees']);
    }

    private function createCompleteTestUser(): User
    {
        // Create organisation
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Organisation');
        $organisation->setEmail('contact@test.com');
        $organisation->setNomRue('Test Street');
        $organisation->setTelephone('0123456789');
        $this->em->persist($organisation);

        // Create service
        $service = new Service();
        $service->setNomService('Test Service');
        $service->setNiveauService(1);
        $service->setIsPrincipal(true);
        $service->setOrganisation($organisation);
        $this->em->persist($service);

        // Create zone
        $zone = new Zone();
        $zone->setNomZone('Test Zone');
        $zone->setDescription('Test zone description');
        $zone->setCapacite(50);
        $this->em->persist($zone);

        // Create service-zone relationship
        $serviceZone = new ServiceZone();
        $serviceZone->setService($service);
        $serviceZone->setZone($zone);
        $this->em->persist($serviceZone);

        // Create badgeuse
        $badgeuse = new Badgeuse();
        $badgeuse->setReference('BADGE-TEST-001');
        $badgeuse->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        // Create access
        $acces = new Acces();
        $acces->setNomAcces('Test Access');
        $acces->setDateInstallation(new \DateTime());
        $acces->setZone($zone);
        $acces->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        // Create user
        $user = new User();
        $user->setEmail('complete.test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setTelephone('0123456789');
        $user->setPoste('Developer');
        $user->setDateNaissance(new \DateTime('1990-01-01'));
        $this->em->persist($user);

        // Create travailler relationship
        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        // Create badge
        $badge = new Badge();
        $badge->setNumeroBadge(200001);
        $badge->setTypeBadge('RFID');
        $badge->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        // Create user-badge relationship
        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user);
        $userBadge->setBadge($badge);
        $this->em->persist($userBadge);

        $this->em->flush();
        
        // Refresh entities to ensure relations are loaded
        $this->em->refresh($user);
        $this->em->refresh($service);
        $this->em->refresh($organisation);

        return $user;
    }
}