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