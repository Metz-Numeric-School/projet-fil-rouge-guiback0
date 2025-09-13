<?php

namespace App\Tests\Integration\Repository;

use App\Repository\BadgeuseRepository;
use App\Entity\Badgeuse;
use App\Tests\Shared\DatabaseKernelTestCase;

class BadgeuseRepositoryTest extends DatabaseKernelTestCase
{
    private BadgeuseRepository $badgeuseRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeuseRepository = static::getContainer()->get(BadgeuseRepository::class);
    }

    public function testFindByReference(): void
    {
        $found = $this->badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);

        $this->assertNotNull($found);
        $this->assertEquals('BADGE-DEFENSE-ALPHA-001', $found->getReference());
        $this->assertInstanceOf(\DateTimeInterface::class, $found->getDateInstallation());
    }

    public function testFindBadgeusesWithAcces(): void
    {
        $qb = $this->badgeuseRepository->createQueryBuilder('b');
        $qb->leftJoin('b.acces', 'a')
           ->addSelect('a')
           ->where('b.reference = :ref')
           ->setParameter('ref', 'BADGE-DEFENSE-ALPHA-001');

        $result = $qb->getQuery()->getOneOrNullResult();

        $this->assertNotNull($result);
        $this->assertEquals('BADGE-DEFENSE-ALPHA-001', $result->getReference());
        // TestFixtures creates one access per badgeuse
        $this->assertCount(1, $result->getAcces());
        $this->assertEquals('Accès Zone Alpha Défense', $result->getAcces()->first()->getNomAcces());
    }

    public function testFindByInstallationDateRange(): void
    {
        $qb = $this->badgeuseRepository->createQueryBuilder('b');
        $qb->where('b.date_installation >= :date')
           ->setParameter('date', new \DateTime('2019-12-31')); // All test badgeuses are installed in 2020-01-01

        $recentBadgeuses = $qb->getQuery()->getResult();

        // All CommonFixtures badgeuses should be found (15 badgeuses in fixtures)
        $this->assertEquals(15, count($recentBadgeuses));
        $recentRefs = array_map(fn($b) => $b->getReference(), $recentBadgeuses);
        $this->assertContains('BADGE-DEFENSE-ALPHA-001', $recentRefs);
        $this->assertContains('BADGE-DEFENSE-BETA-001', $recentRefs);
    }

    public function testBadgeuseRepositoryBasicOperations(): void
    {
        // Test findAll
        $all = $this->badgeuseRepository->findAll();
        $this->assertEquals(15, count($all)); // CommonFixtures loads 15 badgeuses

        // Test find by reference
        $found = $this->badgeuseRepository->findOneBy(['reference' => 'BADGE-DEFENSE-BETA-001']);
        $this->assertNotNull($found);
        $this->assertEquals('BADGE-DEFENSE-BETA-001', $found->getReference());

        // Test count
        $count = $this->badgeuseRepository->count([]);
        $this->assertEquals(15, $count);
    }

    public function testUniqueReferenceConstraint(): void
    {
        $found = $this->badgeuseRepository->findOneBy(['reference' => 'BADGE-PUBLIC-001']);
        $this->assertNotNull($found);
        $this->assertEquals('BADGE-PUBLIC-001', $found->getReference());

        // Test that there's only one badgeuse with this reference
        $duplicates = $this->badgeuseRepository->findBy(['reference' => 'BADGE-PUBLIC-001']);
        $this->assertCount(1, $duplicates);
    }
}