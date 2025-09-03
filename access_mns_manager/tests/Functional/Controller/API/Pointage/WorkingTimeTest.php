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
        $user->setCompteActif(true);
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        return $user;
    }
}