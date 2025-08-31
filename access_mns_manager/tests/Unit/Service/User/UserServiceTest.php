<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Travailler;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;
use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\Common\Collections\ArrayCollection;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private EntityManagerInterface&MockObject $entityManager;
    private SecurityBundle&MockObject $security;
    private UserRepository&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(SecurityBundle::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->userService = new UserService(
            $this->entityManager,
            $this->security
        );
    }

    public function testGetCurrentUserOrganisationWithValidUser(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(1);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($organisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $result = $this->userService->getCurrentUserOrganisation();

        $this->assertEquals($organisation, $result);
    }

    public function testGetCurrentUserOrganisationWithNoUser(): void
    {
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $result = $this->userService->getCurrentUserOrganisation();

        $this->assertNull($result);
    }

    public function testGetCurrentUserOrganisationWithInvalidUser(): void
    {
        $invalidUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($invalidUser);

        $result = $this->userService->getCurrentUserOrganisation();

        $this->assertNull($result);
    }

    public function testUserBelongsToOrganisationTrue(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(1);

        $currentOrganisation = $this->createMock(Organisation::class);
        $currentOrganisation->method('getId')->willReturn(1);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($currentOrganisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $result = $this->userService->userBelongsToOrganisation($organisation);

        $this->assertTrue($result);
    }

    public function testUserBelongsToOrganisationFalse(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $organisation->method('getId')->willReturn(1);

        $currentOrganisation = $this->createMock(Organisation::class);
        $currentOrganisation->method('getId')->willReturn(2);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($currentOrganisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $result = $this->userService->userBelongsToOrganisation($organisation);

        $this->assertFalse($result);
    }

    public function testUserBelongsToOrganisationWithNoCurrentOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $result = $this->userService->userBelongsToOrganisation($organisation);

        $this->assertFalse($result);
    }

    public function testCanAccessUserDataSameUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn(1);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($currentUser);

        $result = $this->userService->canAccessUserData($user);

        $this->assertTrue($result);
    }

    public function testCanAccessUserDataDifferentUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn(2);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($currentUser);

        $result = $this->userService->canAccessUserData($user);

        $this->assertFalse($result);
    }

    public function testCanAccessUserDataNoCurrentUser(): void
    {
        $user = $this->createMock(User::class);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $result = $this->userService->canAccessUserData($user);

        $this->assertFalse($result);
    }

    public function testGetUserOrganisationWithPrincipalService(): void
    {
        $organisation = $this->createMock(Organisation::class);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($organisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $result = $this->userService->getUserOrganisation($user);

        $this->assertEquals($organisation, $result);
    }

    public function testGetUserOrganisationWithoutPrincipalServiceButWithActiveTravail(): void
    {
        $organisation = $this->createMock(Organisation::class);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($organisation);

        $travail = $this->createMock(Travailler::class);
        $travail->method('getDateFin')->willReturn(null);
        $travail->method('getService')->willReturn($service);

        $travailCollection = [$travail];

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn(null);
        $user->method('getTravail')->willReturn(new ArrayCollection($travailCollection));

        $result = $this->userService->getUserOrganisation($user);

        $this->assertEquals($organisation, $result);
    }

    public function testGetUserOrganisationWithNoServices(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn(null);
        $user->method('getTravail')->willReturn(new ArrayCollection([]));

        $result = $this->userService->getUserOrganisation($user);

        $this->assertNull($result);
    }

    public function testAddOrganisationFilterWithOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($organisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('e.organisation = :organisation')
            ->willReturnSelf();
        $queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('organisation', $organisation)
            ->willReturnSelf();

        $this->userService->addOrganisationFilter($queryBuilder, 'e');
    }

    public function testAddOrganisationFilterWithoutOrganisation(): void
    {
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('1 = 0');

        $this->userService->addOrganisationFilter($queryBuilder);
    }

    public function testGetOrganisationUsersWithOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);

        $service = $this->createMock(Service::class);
        $service->method('getOrganisation')->willReturn($organisation);

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn($service);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        $expectedUsers = [$this->createMock(User::class)];

        $queryBuilder
            ->expects($this->exactly(2))
            ->method('join')
            ->withConsecutive(
                ['u.travail', 't'],
                ['t.service', 's']
            )
            ->willReturnSelf();
        $queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with('s.organisation = :organisation')
            ->willReturnSelf();
        $queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('t.date_fin IS NULL')
            ->willReturnSelf();
        $queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('organisation', $organisation)
            ->willReturnSelf();
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($expectedUsers);

        $this->userRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('u')
            ->willReturn($queryBuilder);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        $result = $this->userService->getOrganisationUsers();

        $this->assertEquals($expectedUsers, $result);
    }

    public function testGetOrganisationUsersWithoutOrganisation(): void
    {
        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $result = $this->userService->getOrganisationUsers();

        $this->assertEquals([], $result);
    }

    public function testGetUserOrganisationWithFinishedTravail(): void
    {
        $travail = $this->createMock(Travailler::class);
        $travail->method('getDateFin')->willReturn(new \DateTime());
        $travail->method('getService')->willReturn($this->createMock(Service::class));

        $travailCollection = [$travail];

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn(null);
        $user->method('getTravail')->willReturn(new ArrayCollection($travailCollection));

        $result = $this->userService->getUserOrganisation($user);

        $this->assertNull($result);
    }

    public function testGetUserOrganisationWithTravailButNoService(): void
    {
        $travail = $this->createMock(Travailler::class);
        $travail->method('getDateFin')->willReturn(null);
        $travail->method('getService')->willReturn(null);

        $travailCollection = [$travail];

        $user = $this->createMock(User::class);
        $user->method('getPrincipalService')->willReturn(null);
        $user->method('getTravail')->willReturn(new ArrayCollection($travailCollection));

        $result = $this->userService->getUserOrganisation($user);

        $this->assertNull($result);
    }
}