<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Badge;
use App\Entity\Pointage;
use App\Entity\UserBadge;
use PHPUnit\Framework\TestCase;

class BadgeTest extends TestCase
{
    private Badge $badge;

    protected function setUp(): void
    {
        $this->badge = new Badge();
        $this->badge->setTypeBadge('permanent');
        $this->badge->setDateCreation(new \DateTime());
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->badge->getId());
        $this->assertCount(0, $this->badge->getPointages());
        $this->assertCount(0, $this->badge->getUserBadges());
    }

    public function testCoreSetters(): void
    {
        $creation = new \DateTimeImmutable('2024-01-01');
        $expiration = new \DateTimeImmutable('2025-01-01');
        $this->badge
            ->setNumeroBadge(123456)
            ->setTypeBadge('administrateur')
            ->setDateCreation($creation)
            ->setDateExpiration($expiration);
        $this->assertSame(123456, $this->badge->getNumeroBadge());
        $this->assertSame('administrateur', $this->badge->getTypeBadge());
        $this->assertSame($creation, $this->badge->getDateCreation());
        $this->assertSame($expiration, $this->badge->getDateExpiration());
    }

    public function testPointageCollection(): void
    {
        $p1 = new Pointage();
        $p2 = new Pointage();
        $this->badge->addPointage($p1)->addPointage($p2)->addPointage($p1); // pas de doublon
        $this->assertCount(2, $this->badge->getPointages());
        $this->badge->removePointage($p1);
        $this->assertCount(1, $this->badge->getPointages());
    }

    public function testUserBadgeCollection(): void
    {
        $ub1 = new UserBadge();
        $ub2 = new UserBadge();
        $this->badge->addUserBadge($ub1)->addUserBadge($ub2)->addUserBadge($ub1);
        $this->assertCount(2, $this->badge->getUserBadges());
        $this->badge->removeUserBadge($ub2);
        $this->assertCount(1, $this->badge->getUserBadges());
    }

    public function testNullableExpiration(): void
    {
        $this->badge->setDateExpiration(null);
        $this->assertNull($this->badge->getDateExpiration());
    }
}