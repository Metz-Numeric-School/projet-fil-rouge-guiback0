<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ServiceZone;
use App\Entity\Service;
use App\Entity\Zone;
use PHPUnit\Framework\TestCase;

class ServiceZoneTest extends TestCase
{
    private ServiceZone $serviceZone;

    protected function setUp(): void
    {
        $this->serviceZone = new ServiceZone();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->serviceZone->getId());
        $this->assertNull($this->serviceZone->getService());
        $this->assertNull($this->serviceZone->getZone());
    }

    public function testSetters(): void
    {
        $service = (new Service())->setNomService('Direction');
        $zone = (new Zone())->setNomZone('Zone A');
        $this->serviceZone->setService($service)->setZone($zone);
        $this->assertSame($service, $this->serviceZone->getService());
        $this->assertSame($zone, $this->serviceZone->getZone());
    }

    public function testNullable(): void
    {
        $this->serviceZone->setService(null)->setZone(null);
        $this->assertNull($this->serviceZone->getService());
        $this->assertNull($this->serviceZone->getZone());
    }
}