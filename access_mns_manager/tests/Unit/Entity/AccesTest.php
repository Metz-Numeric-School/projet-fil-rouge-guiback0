<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Acces;
use App\Entity\Zone;
use App\Entity\Badgeuse;
use App\Tests\Shared\DatabaseKernelTestCase;

class AccesTest extends DatabaseKernelTestCase
{
    private Acces $acces;

    protected function setUp(): void
    {
        parent::setUp();
        $this->acces = new Acces();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->acces->getId());
        $this->assertNull($this->acces->getNomAcces());
        $this->assertNull($this->acces->getDateInstallation());
        $this->assertNull($this->acces->getZone());
        $this->assertNull($this->acces->getBadgeuse());
    }

    public function testSetters(): void
    {
        $date = new \DateTimeImmutable();
        $zone = (new Zone())->setNomZone('Zone Test');
        $badgeuse = (new Badgeuse())->setReference('BADGE-TEST-001')->setDateInstallation(new \DateTimeImmutable());

        $this->acces
            ->setNomAcces('Accès Principal')
            ->setDateInstallation($date)
            ->setZone($zone)
            ->setBadgeuse($badgeuse);

        $this->assertSame('Accès Principal', $this->acces->getNomAcces());
        $this->assertSame($date, $this->acces->getDateInstallation());
        $this->assertSame($zone, $this->acces->getZone());
        $this->assertSame($badgeuse, $this->acces->getBadgeuse());
    }

    public function testNullableRelations(): void
    {
        $this->acces->setZone(null)->setBadgeuse(null);
        $this->assertNull($this->acces->getZone());
        $this->assertNull($this->acces->getBadgeuse());
    }
}