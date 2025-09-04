<?php

namespace App\Tests\Functional\Controller\API\User;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class DeactivateTest extends DatabaseWebTestCase
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

    public function testDeactivateAccountWithValidToken(): void
    {
        $user = $this->createTestUser();
        $this->assertTrue($user->isCompteActif());
        $token = $this->jwtManager->create($user);

        $this->client->request('POST', '/manager/api/user/deactivate', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Compte désactivé avec succès. Vos données seront automatiquement supprimées après 5 ans de conservation.', $response['message']);
        
        $this->em->refresh($user);
        $this->assertFalse($user->isCompteActif());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getDateSuppressionPrevue());
    }

    public function testDeactivateAccountWithoutToken(): void
    {
        $this->client->request('POST', '/manager/api/user/deactivate');

        $this->assertResponseStatusCodeSame(401);
    }

    private function createTestUser(): User
    {
        // Utiliser l'utilisateur de test qui existe déjà dans les fixtures CommonFixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // S'assurer que le compte est actif pour les tests
        $user->setCompteActif(true);
        $this->em->flush();

        return $user;
    }
}