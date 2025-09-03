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
        
        $this->assertEquals('John', $exportData['personal_information']['prenom']);
        $this->assertEquals('Doe', $exportData['personal_information']['nom']);
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
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Organisation');
        $organisation->setEmail('contact@test.com');
        $organisation->setNomRue('Test Street');
        $organisation->setNumeroRue('123');
        $organisation->setVille('Test City');
        $organisation->setCodePostal('12345');
        $organisation->setPays('France');
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
        $user->setTelephone('0123456789');
        $user->setDateNaissance(new \DateTime('1990-01-01'));
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        // Date already set by setDateDebut() above
        $this->em->persist($travailler);

        $badge = new Badge();
        $badge->setNumeroBadge(123456);
        $badge->setTypeBadge('permanent');
        $badge->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user);
        $userBadge->setBadge($badge);
        $userBadge->setDateAttribution(new \DateTime());
        $this->em->persist($userBadge);

        $this->em->flush();

        return $user;
    }
}