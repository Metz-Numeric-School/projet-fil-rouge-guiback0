<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Zone;
use App\Entity\Acces;
use App\Entity\ServiceZone;
use App\Tests\Shared\DatabaseKernelTestCase;

class ZoneTest extends DatabaseKernelTestCase
{
    private Zone $zone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zone = new Zone();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->zone->getId());
        $this->assertNull($this->zone->getNomZone());
        $this->assertNull($this->zone->getDescription());
        $this->assertNull($this->zone->getCapacite());
        $this->assertCount(0, $this->zone->getAcces());
        $this->assertCount(0, $this->zone->getServiceZones());
    }

    public function testCoreSetters(): void
    {
        $this->zone
            ->setNomZone('Zone Alpha')
            ->setDescription('Zone ultra-restreinte')
            ->setCapacite(5);

        $this->assertSame('Zone Alpha', $this->zone->getNomZone());
        $this->assertSame('Zone ultra-restreinte', $this->zone->getDescription());
        $this->assertSame(5, $this->zone->getCapacite());
    }

    public function testAccesCollectionAddRemove(): void
    {
        $acces = new Acces();
        $this->zone->addAcce($acces);
        $this->assertCount(1, $this->zone->getAcces());
        $this->assertSame($this->zone, $acces->getZone());
        $this->zone->removeAcce($acces);
        $this->assertCount(0, $this->zone->getAcces());
        $this->assertNull($acces->getZone());
    }

    public function testAccesNotDuplicated(): void
    {
        $acces = new Acces();
        $this->zone->addAcce($acces);
        $this->zone->addAcce($acces);
        $this->assertCount(1, $this->zone->getAcces());
    }

    public function testServiceZoneAddRemove(): void
    {
        $serviceZone = new ServiceZone();
        $this->zone->addServiceZone($serviceZone);
        $this->assertCount(1, $this->zone->getServiceZones());
        $this->assertSame($this->zone, $serviceZone->getZone());
        $this->zone->removeServiceZone($serviceZone);
        $this->assertCount(0, $this->zone->getServiceZones());
        $this->assertNull($serviceZone->getZone());
    }

    public function testServiceZoneNotDuplicated(): void
    {
        $serviceZone = new ServiceZone();
        $this->zone->addServiceZone($serviceZone);
        $this->zone->addServiceZone($serviceZone);
        $this->assertCount(1, $this->zone->getServiceZones());
    }

    public function testNullableFields(): void
    {
        $this->zone->setDescription(null)->setCapacite(null);
        $this->assertNull($this->zone->getDescription());
        $this->assertNull($this->zone->getCapacite());
    }
}