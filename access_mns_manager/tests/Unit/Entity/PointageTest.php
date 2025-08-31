<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Pointage;
use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Tests\Shared\DatabaseKernelTestCase;

class PointageTest extends DatabaseKernelTestCase
{
    private Pointage $pointage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointage = new Pointage();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->pointage->getId());
        $this->assertNull($this->pointage->getBadge());
        $this->assertNull($this->pointage->getBadgeuse());
        $this->assertNull($this->pointage->getHeure());
        $this->assertNull($this->pointage->getType());
    }

    public function testCoreSetters(): void
    {
        $badge = new Badge();
        $badgeuse = new Badgeuse();
        $heure = new \DateTimeImmutable('2024-01-15 09:30:00');
        $this->pointage
            ->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure($heure)
            ->setType('entree');
        $this->assertSame($badge, $this->pointage->getBadge());
        $this->assertSame($badgeuse, $this->pointage->getBadgeuse());
        $this->assertSame($heure, $this->pointage->getHeure());
        $this->assertSame('entree', $this->pointage->getType());
    }

    public function testNullableRelations(): void
    {
        $this->pointage->setBadge(null)->setBadgeuse(null);
        $this->assertNull($this->pointage->getBadge());
        $this->assertNull($this->pointage->getBadgeuse());
    }

    public function testDifferentTypes(): void
    {
        $entree = (new Pointage())->setType('entree');
        $sortie = (new Pointage())->setType('sortie');
        $acces = (new Pointage())->setType('acces');
        $this->assertSame('entree', $entree->getType());
        $this->assertSame('sortie', $sortie->getType());
        $this->assertSame('acces', $acces->getType());
    }
}