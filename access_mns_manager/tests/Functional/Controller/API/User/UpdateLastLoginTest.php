<?php

namespace App\Tests\Functional\Controller\API\User;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdateLastLoginTest extends DatabaseWebTestCase
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

    public function testUpdateLastLoginWithValidToken(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        $originalLastLogin = $user->getDateDerniereConnexion();
        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/user/update-last-login', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        // Vérifier la structure de réponse standard
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Dernière connexion mise à jour', $response['message']);
        
        $this->em->refresh($user);
        $this->assertNotEquals($originalLastLogin, $user->getDateDerniereConnexion());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getDateDerniereConnexion());
    }

    public function testUpdateLastLoginWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/user/update-last-login');

        $this->assertResponseStatusCodeSame(401);
    }

}