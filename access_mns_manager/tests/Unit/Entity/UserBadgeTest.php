<?php

namespace App\Tests\Unit\Entity;

use App\Entity\UserBadge;
use App\Entity\User;
use App\Entity\Badge;
use PHPUnit\Framework\TestCase;

class UserBadgeTest extends TestCase
{
    private UserBadge $userBadge;

    protected function setUp(): void
    {
        $this->userBadge = new UserBadge();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->userBadge->getId());
        $this->assertNull($this->userBadge->getUtilisateur());
        $this->assertNull($this->userBadge->getBadge());
    }

    public function testSetters(): void
    {
        $user = new User();
        $badge = new Badge();
        $badge->setTypeBadge('permanent');
        $badge->setDateCreation(new \DateTime());
        $this->userBadge->setUtilisateur($user)->setBadge($badge);
        $this->assertSame($user, $this->userBadge->getUtilisateur());
        $this->assertSame($badge, $this->userBadge->getBadge());
    }

    public function testNullable(): void
    {
        $this->userBadge->setUtilisateur(null)->setBadge(null);
        $this->assertNull($this->userBadge->getUtilisateur());
        $this->assertNull($this->userBadge->getBadge());
    }

    public function testReassignment(): void
    {
        $u1 = new User();
        $u2 = new User();
        $b = new Badge();
        $b->setTypeBadge('permanent');
        $b->setDateCreation(new \DateTime());
        $this->userBadge->setUtilisateur($u1)->setBadge($b);
        $this->assertSame($u1, $this->userBadge->getUtilisateur());
        $this->userBadge->setUtilisateur($u2);
        $this->assertSame($u2, $this->userBadge->getUtilisateur());
        $this->assertSame($b, $this->userBadge->getBadge());
    }
}