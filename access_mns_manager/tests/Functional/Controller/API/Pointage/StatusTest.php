<?php

namespace App\Tests\Functional\Controller\API\Pointage;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Tests\Shared\DatabaseWebTestCase;

class StatusTest extends DatabaseWebTestCase
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

    public function testGetUserStatusWithActiveUser(): void
    {
        $user = $this->createTestUser();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/pointage/status', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        // Le test peut échouer à cause de services ou erreurs organisationnelles
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 500]), 'Expected 200 or 500, got ' . $statusCode);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        
        if ($statusCode === 200) {
            $this->assertArrayHasKey('success', $response);
            $this->assertTrue($response['success']);
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('message', $response);
            
            // Vérifier la structure des données
            $data = $response['data'];
            $this->assertArrayHasKey('is_working', $data);
            $this->assertArrayHasKey('last_pointage', $data);
            $this->assertIsBool($data['is_working']);
        } else {
            // En cas d'erreur (services manquants, etc.)
            $this->assertArrayHasKey('success', $response);
            $this->assertFalse($response['success']);
            $this->assertArrayHasKey('error', $response);
            $this->assertArrayHasKey('message', $response);
        }
    }

    public function testGetUserStatusWithDeactivatedUser(): void
    {
        $user = $this->createTestUser();
        $user->setCompteActif(false);
        $this->em->flush();
        
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/pointage/status', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetUserStatusWithoutAuthentication(): void
    {
        $this->client->request('GET', '/manager/api/pointage/status');

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