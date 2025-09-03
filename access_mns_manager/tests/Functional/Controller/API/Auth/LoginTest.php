<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends DatabaseWebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testLoginWithValidCredentials(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
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
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();

        $originalLastLogin = $user->getDateDerniereConnexion();

        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        
        $this->em->refresh($user);
        $this->assertNotEquals($originalLastLogin, $user->getDateDerniereConnexion());
    }
}