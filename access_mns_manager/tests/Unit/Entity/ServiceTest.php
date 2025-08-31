<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Service;
use App\Entity\Organisation;
use App\Entity\ServiceZone;
use App\Entity\Travailler;
use App\Tests\Shared\DatabaseKernelTestCase;

class ServiceTest extends DatabaseKernelTestCase
{
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new Service();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->service->getId());
        $this->assertNull($this->service->getNomService());
        $this->assertFalse($this->service->isIsPrincipal());
        $this->assertCount(0, $this->service->getServiceZones());
        $this->assertCount(0, $this->service->getTravail());
    }

    public function testCoreSetters(): void
    {
        $org = new Organisation();
        $this->service
            ->setNomService('Direction')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($org);
        $this->assertSame('Direction', $this->service->getNomService());
        $this->assertSame(1, $this->service->getNiveauService());
        $this->assertTrue($this->service->isIsPrincipal());
        $this->assertSame($org, $this->service->getOrganisation());
    }

    public function testServiceZoneCollection(): void
    {
        $sz = new ServiceZone();
        $sz->setService($this->service);
        $this->service->addServiceZone($sz);
        $this->assertCount(1, $this->service->getServiceZones());
        $this->service->addServiceZone($sz);
        $this->assertCount(1, $this->service->getServiceZones());
        $this->service->removeServiceZone($sz);
        $this->assertCount(0, $this->service->getServiceZones());
    }

    public function testTravailCollection(): void
    {
        $tr = new Travailler();
        $tr->setService($this->service);
        $this->service->addTravail($tr);
        $this->assertCount(1, $this->service->getTravail());
        $this->service->addTravail($tr);
        $this->assertCount(1, $this->service->getTravail());
        $this->service->removeTravail($tr);
        $this->assertCount(0, $this->service->getTravail());
    }

    public function testDefaultIsPrincipalFalse(): void
    {
        $s = new Service();
        $this->assertFalse($s->isIsPrincipal());
    }
}