<?php

namespace App\Tests\Integration\Repository;

use App\Repository\BadgeRepository;
use App\Tests\Shared\DatabaseKernelTestCase;

class BadgeRepositoryTest extends DatabaseKernelTestCase
{
    private BadgeRepository $badgeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeRepository = static::getContainer()->get(BadgeRepository::class);
    }

    public function testFindByNumeroBadge(): void
    {
        $found = $this->badgeRepository->findOneBy(['numero_badge' => 200001]);

        $this->assertNotNull($found);
        $this->assertEquals(200001, $found->getNumeroBadge());
        $this->assertEquals('administrateur', $found->getTypeBadge());
    }

    public function testFindByTypeBadge(): void
    {
        $adminBadges = $this->badgeRepository->findBy(['type_badge' => 'administrateur']);
        $permanentBadges = $this->badgeRepository->findBy(['type_badge' => 'permanent']);
        $desactiveBadges = $this->badgeRepository->findBy(['type_badge' => 'desactive']);

        // TestFixtures has 3 admin badges, 6 permanent badges, and 1 desactive badge
        $this->assertEquals(3, count($adminBadges));
        $this->assertEquals(6, count($permanentBadges));
        $this->assertEquals(1, count($desactiveBadges));
    }

    public function testFindActiveBadges(): void
    {
        $qb = $this->badgeRepository->createQueryBuilder('b');
        $qb->where('b.date_expiration IS NULL OR b.date_expiration > :now')
           ->setParameter('now', new \DateTime());

        $activeBadges = $qb->getQuery()->getResult();

        // TestFixtures creates badges without expiration (permanent badges)
        $this->assertGreaterThanOrEqual(9, count($activeBadges)); // All badges except desactive ones
        
        $activeNumbers = array_map(fn($b) => $b->getNumeroBadge(), $activeBadges);
        $this->assertContains(200001, $activeNumbers); // Super admin badge
        $this->assertContains(200010, $activeNumbers); // Test user badge
    }

    public function testFindBadgesWithUsers(): void
    {
        $qb = $this->badgeRepository->createQueryBuilder('b');
        $qb->leftJoin('b.userBadges', 'ub')
           ->leftJoin('ub.Utilisateur', 'u')
           ->addSelect('ub', 'u')
           ->where('b.numero_badge = :numero')
           ->setParameter('numero', 200001); // Super admin badge

        $result = $qb->getQuery()->getOneOrNullResult();

        $this->assertNotNull($result);
        $this->assertEquals(200001, $result->getNumeroBadge());
        $this->assertNotEmpty($result->getUserBadges());
        $this->assertEquals('superadmin@test.com', $result->getUserBadges()->first()->getUtilisateur()->getEmail());
    }

    public function testFindBadgesByDateRange(): void
    {
        $qb = $this->badgeRepository->createQueryBuilder('b');
        $qb->where('b.date_creation >= :date')
           ->setParameter('date', new \DateTime('2020-12-31')); // All test badges are created in 2021-01-01

        $recentBadges = $qb->getQuery()->getResult();

        // All TestFixtures badges should be found
        $this->assertEquals(10, count($recentBadges));
        $recentNumbers = array_map(fn($b) => $b->getNumeroBadge(), $recentBadges);
        $this->assertContains(200001, $recentNumbers);
        $this->assertContains(200010, $recentNumbers);
    }

    public function testBadgeRepositoryBasicOperations(): void
    {
        // Test findAll
        $all = $this->badgeRepository->findAll();
        $this->assertEquals(10, count($all)); // TestFixtures loads 10 badges

        // Test find by badge number
        $found = $this->badgeRepository->findOneBy(['numero_badge' => 200005]);
        $this->assertNotNull($found);
        $this->assertEquals(200005, $found->getNumeroBadge());
        $this->assertEquals('permanent', $found->getTypeBadge());

        // Test count by type
        $permanentCount = $this->badgeRepository->count(['type_badge' => 'permanent']);
        $this->assertEquals(6, $permanentCount); // 6 permanent badges in fixtures
    }
}