<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use App\Service\User\UserOrganisationService;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserOrganisationServiceTest extends DatabaseKernelTestCase
{
    private UserOrganisationService $userOrganisationService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userOrganisationService = static::getContainer()->get(UserOrganisationService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testGetUserOrganisation(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $result = $this->userOrganisationService->getUserOrganisation($user);

        if ($result) {
            $this->assertInstanceOf(Organisation::class, $result);
            $this->assertNotEmpty($result->getNomOrganisation());
        } else {
            $this->assertNull($result);
        }
    }

    public function testGetUserOrganisationWithoutService(): void
    {
        $user = new User();
        $user->setEmail('no-service-org@example.com');
        $user->setNom('NoServiceOrg');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $result = $this->userOrganisationService->getUserOrganisation($user);
        $this->assertNull($result);
    }

    public function testGetOrganisationUsers(): void
    {
        $result = $this->userOrganisationService->getOrganisationUsers();
        $this->assertIsArray($result);
    }

    public function testUserBelongsToOrganisation(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Belongs Org');
        $organisation->setEmail('contact@belongs-org.com');
        $organisation->setNomRue('Test Street');
        $this->em->persist($organisation);
        $this->em->flush();

        $result = $this->userOrganisationService->userBelongsToOrganisation($organisation);
        $this->assertIsBool($result);
    }

    public function testAddOrganisationFilterWithoutOrganisation(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')->from(User::class, 'u');

        $this->userOrganisationService->addOrganisationFilter($qb, 'u');

        $query = $qb->getQuery();
        $this->assertStringContainsString('1 = 0', $query->getDQL());
    }

    public function testGetCurrentUserOrganisation(): void
    {
        $result = $this->userOrganisationService->getCurrentUserOrganisation();
        $this->assertTrue($result === null || $result instanceof Organisation);
    }
}