<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Entity\Badge;
use App\Repository\UserBadgeRepository;
use App\Tests\Shared\DatabaseKernelTestCase;

class UserBadgeRepositoryTest extends DatabaseKernelTestCase
{
    private UserBadgeRepository $userBadgeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->userBadgeRepository = $container->get(UserBadgeRepository::class);
    }

    public function testFindBadgesByUser(): void
    {
        // Get existing user from fixtures
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'superadmin@access-mns.fr']);
        $this->assertNotNull($user);

        $userBadges = $this->userBadgeRepository->findBy(['Utilisateur' => $user]);

        // CommonFixtures assigns one badge per user
        $this->assertCount(1, $userBadges);
        $badgeNumbers = array_map(fn($ub) => $ub->getBadge()->getNumeroBadge(), $userBadges);
        $this->assertContains(200001, $badgeNumbers); // Super admin badge number
    }

    public function testFindUsersByBadge(): void
    {
        // Get existing badge from fixtures
        $badgeRepository = $this->em->getRepository(Badge::class);
        $badge = $badgeRepository->findOneBy(['numero_badge' => 200004]);
        $this->assertNotNull($badge);

        $badgeUsers = $this->userBadgeRepository->findBy(['badge' => $badge]);

        // CommonFixtures assigns each badge to exactly one user
        $this->assertCount(1, $badgeUsers);
        $userEmails = array_map(fn($ub) => $ub->getUtilisateur()->getEmail(), $badgeUsers);
        $this->assertContains('j.dupont@defense.gouv.fr', $userEmails); // user_defense_1 has badge 200004
    }

    public function testUserBadgeRepositoryBasicOperations(): void
    {
        // Test findAll
        $all = $this->userBadgeRepository->findAll();
        $this->assertEquals(10, count($all)); // CommonFixtures creates 10 user-badge relationships

        // Test find specific user-badge relationship
        $badgeRepository = $this->em->getRepository(Badge::class);
        $userRepository = $this->em->getRepository(User::class);
        
        $testUser = $userRepository->findOneBy(['email' => 'test@example.com']);
        $testBadge = $badgeRepository->findOneBy(['numero_badge' => 200010]);
        
        $this->assertNotNull($testUser);
        $this->assertNotNull($testBadge);
        
        $userBadge = $this->userBadgeRepository->findOneBy([
            'Utilisateur' => $testUser,
            'badge' => $testBadge
        ]);
        $this->assertNotNull($userBadge);

        // Test count
        $count = $this->userBadgeRepository->count([]);
        $this->assertEquals(10, $count);
    }
}