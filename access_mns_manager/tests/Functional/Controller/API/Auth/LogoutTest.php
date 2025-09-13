<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LogoutTest extends DatabaseWebTestCase
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

    public function testLogoutWithValidToken(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/auth/logout', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Déconnexion réussie', $response['message']);
    }

    public function testLogoutWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/auth/logout');

        $this->assertResponseStatusCodeSame(401);
    }

}