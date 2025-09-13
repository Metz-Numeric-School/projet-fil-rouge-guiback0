<?php

namespace App\Tests\Functional\Controller\API\User;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use App\Entity\Badge;
use App\Entity\UserBadge;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class ExportDataTest extends DatabaseWebTestCase
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

    public function testExportUserDataWithValidToken(): void
    {
        $user = $this->createTestUserWithData();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/user/export-data', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        
        $response = $this->client->getResponse();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData);
        
        // Vérifier la structure de réponse standard
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('export_timestamp', $responseData);
        $this->assertArrayHasKey('gdpr_notice', $responseData);
        
        $exportData = $responseData['data'];
        $this->assertArrayHasKey('personal_information', $exportData);
        $this->assertArrayHasKey('account_information', $exportData);
        $this->assertArrayHasKey('organisation', $exportData);
        $this->assertArrayHasKey('services', $exportData);
        $this->assertArrayHasKey('badges', $exportData);
        
        $this->assertEquals('User', $exportData['personal_information']['prenom']);
        $this->assertEquals('Test', $exportData['personal_information']['nom']);
        $this->assertEquals('test@example.com', $exportData['personal_information']['email']);
        $this->assertIsArray($exportData['services']);
        $this->assertIsArray($exportData['badges']);
    }

    public function testExportUserDataWithoutToken(): void
    {
        $this->client->request('GET', '/manager/api/user/export-data');

        $this->assertResponseStatusCodeSame(401);
    }

    private function createTestUserWithData(): User
    {
        // Utiliser l'utilisateur de test qui existe déjà dans les fixtures CommonFixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        
        // S'assurer que l'utilisateur a des données complètes pour l'export
        $user->setTelephone('0123456789');
        $user->setDateNaissance(new \DateTime('1990-01-01'));
        $this->em->flush();

        return $user;
    }
}