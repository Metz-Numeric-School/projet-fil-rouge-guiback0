<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MeTest extends DatabaseWebTestCase
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

    public function testMeEndpointWithValidToken(): void
    {
        // Utiliser l'utilisateur de test qui existe déjà dans les fixtures
        $userRepository = $this->em->getRepository(\App\Entity\User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/auth/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('id', $response['data']);
        $this->assertArrayHasKey('email', $response['data']);
        $this->assertArrayHasKey('nom', $response['data']);
        $this->assertArrayHasKey('prenom', $response['data']);
        
        $this->assertEquals('test@example.com', $response['data']['email']);
        $this->assertEquals('TEST', $response['data']['nom']);
        $this->assertEquals('User', $response['data']['prenom']);
    }

    public function testMeEndpointWithoutToken(): void
    {
        $this->client->request('GET', '/manager/api/auth/me');

        $this->assertResponseStatusCodeSame(401);
    }

}