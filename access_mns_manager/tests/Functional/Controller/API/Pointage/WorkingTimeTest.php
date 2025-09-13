<?php

namespace App\Tests\Functional\Controller\API\Pointage;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class WorkingTimeTest extends DatabaseWebTestCase
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

    public function testGetWorkingTimeWithValidPeriod(): void
    {
        $user = $this->createTestUser();
        $token = $this->jwtManager->create($user);

        $startDate = (new \DateTime())->format('Y-m-d');
        $endDate = (new \DateTime())->format('Y-m-d');

        $this->client->request('GET', "/manager/api/pointage/working-time?start_date={$startDate}&end_date={$endDate}", [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('message', $response);
        
        // Vérifier la structure des données de temps de travail
        $data = $response['data'];
        $this->assertArrayHasKey('total_hours', $data);
        $this->assertArrayHasKey('total_minutes', $data);
        $this->assertArrayHasKey('days', $data);
    }

    public function testGetWorkingTimeWithMissingDates(): void
    {
        $user = $this->createTestUser();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/pointage/working-time', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testGetWorkingTimeWithInvalidDateFormat(): void
    {
        $user = $this->createTestUser();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/pointage/working-time?start_date=invalid&end_date=invalid', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testGetWorkingTimeWithoutAuthentication(): void
    {
        $startDate = (new \DateTime())->format('Y-m-d');
        $endDate = (new \DateTime())->format('Y-m-d');

        $this->client->request('GET', "/manager/api/pointage/working-time?start_date={$startDate}&end_date={$endDate}");

        $this->assertResponseStatusCodeSame(401);
    }

    private function createTestUser(): User
    {
        // Utiliser l'utilisateur de test qui existe déjà dans les fixtures CommonFixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        return $user;
    }
}