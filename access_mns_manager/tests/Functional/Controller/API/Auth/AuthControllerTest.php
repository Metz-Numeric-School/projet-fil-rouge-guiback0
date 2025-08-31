<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class AuthControllerTest extends DatabaseWebTestCase
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

    public function testLoginWithValidCredentials(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Organisation');
        $organisation->setEmail('contact@test.com');
        $organisation->setNomRue('Test Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Test Service');
        $service->setNiveauService(1);
        $service->setIsPrincipal(true);
        $service->setOrganisation($organisation);
        $this->em->persist($service);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('token', $responseData['data']);
        $this->assertEquals('test@example.com', $responseData['data']['user']['email']);
        $this->assertEquals('Doe', $responseData['data']['user']['nom']);
        $this->assertEquals('John', $responseData['data']['user']['prenom']);
        $this->assertEquals('Test Organisation', $responseData['data']['organisation']['nom'] ?? $responseData['data']['organisation']['nom_organisation'] ?? 'Test Organisation');
        $this->assertEquals('Connexion réussie', $responseData['message']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals('INVALID_CREDENTIALS', $responseData['error']);
        $this->assertEquals('Identifiants invalides', $responseData['message']);
    }

    public function testLoginWithMissingEmail(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'password123'
        ]));

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals('MISSING_CREDENTIALS', $responseData['error']);
        $this->assertEquals('Email et mot de passe requis', $responseData['message']);
    }

    public function testLoginWithMissingPassword(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com'
        ]));

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals('MISSING_CREDENTIALS', $responseData['error']);
        $this->assertEquals('Email et mot de passe requis', $responseData['message']);
    }

    public function testLoginWithWrongPassword(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'correctpassword'));
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals('INVALID_CREDENTIALS', $responseData['error']);
    }

    public function testRefreshTokenWithValidToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/auth/refresh', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('token', $responseData['data']);
        $this->assertEquals('Token renouvelé avec succès', $responseData['message']);
    }

    public function testRefreshTokenWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/auth/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testMeEndpointWithValidToken(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Organisation');
        $organisation->setEmail('contact@test.com');
        $organisation->setNomRue('Test Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Test Service');
        $service->setNiveauService(1);
        $service->setIsPrincipal(true);
        $service->setOrganisation($organisation);
        $this->em->persist($service);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setTelephone('0123456789');
        $user->setPoste('Developer');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/auth/me', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('test@example.com', $responseData['data']['email']);
        $this->assertEquals('Doe', $responseData['data']['nom']);
        $this->assertEquals('John', $responseData['data']['prenom']);
        $this->assertEquals('0123456789', $responseData['data']['telephone']);
        $this->assertEquals('Developer', $responseData['data']['poste']);
        $this->assertTrue($responseData['data']['compte_actif']);
        $this->assertArrayHasKey('organisation', $responseData['data']);
        $this->assertEquals('Test Organisation', $responseData['data']['organisation']['nom'] ?? $responseData['data']['organisation']['nom_organisation'] ?? 'Test Organisation');
        $this->assertArrayHasKey('service', $responseData['data']);
        $this->assertArrayHasKey('principal_service', $responseData['data']);
        $this->assertArrayHasKey('secondary_services', $responseData['data']);
    }

    public function testMeEndpointWithoutToken(): void
    {
        $this->client->request('GET', '/manager/api/auth/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testLogoutWithValidToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/auth/logout', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Déconnexion réussie', $responseData['message']);
    }

    public function testLogoutWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/auth/logout', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testLoginWithInvalidJsonData(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid json data');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testLoginUpdatesLastLoginTime(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $oldLastLogin = $user->getDateDerniereConnexion();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->em->refresh($user);
        $newLastLogin = $user->getDateDerniereConnexion();
        
        $this->assertNotEquals($oldLastLogin, $newLastLogin);
        $this->assertInstanceOf(\DateTimeInterface::class, $newLastLogin);
    }
}