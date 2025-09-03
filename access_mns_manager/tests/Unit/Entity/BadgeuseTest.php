<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Badgeuse;
use App\Entity\Acces;
use App\Entity\Pointage;
use PHPUnit\Framework\TestCase;

class BadgeuseTest extends TestCase
{
    private Badgeuse $badgeuse;

    protected function setUp(): void
    {
        $this->badgeuse = new Badgeuse();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->badgeuse->getId());
        $this->assertNull($this->badgeuse->getReference());
        $this->assertNull($this->badgeuse->getDateInstallation());
        $this->assertCount(0, $this->badgeuse->getAcces());
        $this->assertCount(0, $this->badgeuse->getPointages());
    }

    public function testCoreSetters(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        $this->badgeuse
            ->setReference('BADGE-XYZ-001')
            ->setDateInstallation($date);
        $this->assertSame('BADGE-XYZ-001', $this->badgeuse->getReference());
        $this->assertSame($date, $this->badgeuse->getDateInstallation());
    }

    public function testAccesCollection(): void
    {
        $a1 = new Acces();
        $a2 = new Acces();
        $this->badgeuse->addAcce($a1)->addAcce($a2)->addAcce($a1);
        $this->assertCount(2, $this->badgeuse->getAcces());
        $this->badgeuse->removeAcce($a1);
        $this->assertCount(1, $this->badgeuse->getAcces());
    }

    public function testPointageCollection(): void
    {
        $p1 = new Pointage();
        $p2 = new Pointage();
        $this->badgeuse->addPointage($p1)->addPointage($p2)->addPointage($p1);
        $this->assertCount(2, $this->badgeuse->getPointages());
        $this->badgeuse->removePointage($p2);
        $this->assertCount(1, $this->badgeuse->getPointages());
    }

    public function testDateInstallationRequired(): void
    {
        $this->assertNull($this->badgeuse->getDateInstallation());
        $date = new \DateTimeImmutable();
        $this->badgeuse->setDateInstallation($date);
        $this->assertSame($date, $this->badgeuse->getDateInstallation());
    }
}