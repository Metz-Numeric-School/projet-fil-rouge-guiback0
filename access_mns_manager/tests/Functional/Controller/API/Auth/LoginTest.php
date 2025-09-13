<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Service\Security\LoginAttemptService;
use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends DatabaseWebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Clear rate limiting state for clean tests
        $loginAttemptService = static::getContainer()->get(LoginAttemptService::class);
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginAttemptService->resetAttempts($request);
    }

    public function testLoginWithValidCredentials(): void
    {
        // Utiliser l'utilisateur de test qui existe déjà dans les fixtures CommonFixtures
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'TestUser123!'
        ]));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('token', $response['data']);
        $this->assertArrayHasKey('user', $response['data']);
        $this->assertEquals('test@example.com', $response['data']['user']['email']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginWithMissingEmail(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'password' => 'password123'
        ]));

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
    }

    public function testLoginWithMissingPassword(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com'
        ]));

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
    }

    public function testLoginWithWrongPassword(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'correctpassword'));
        $this->em->flush();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginWithInvalidJsonData(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], 'invalid json');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginUpdatesLastLoginTime(): void
    {
        // Récupérer l'utilisateur de test qui existe déjà dans les fixtures
        $userRepository = $this->em->getRepository(\App\Entity\User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        $originalLastLogin = $user->getDateDerniereConnexion();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'TestUser123!'
        ]));

        $this->assertResponseIsSuccessful();
        
        $this->em->refresh($user);
        $this->assertNotEquals($originalLastLogin, $user->getDateDerniereConnexion());
    }
}