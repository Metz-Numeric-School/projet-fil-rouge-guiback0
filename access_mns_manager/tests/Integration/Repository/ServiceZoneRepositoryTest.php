<?php

namespace App\Tests\Integration\Repository;

use App\Entity\ServiceZone;
use App\Entity\Service;
use App\Entity\Zone;
use App\Repository\ServiceZoneRepository;
use App\Tests\Shared\DatabaseKernelTestCase;

class ServiceZoneRepositoryTest extends DatabaseKernelTestCase
{
    private ServiceZoneRepository $serviceZoneRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceZoneRepository = static::getContainer()->get(ServiceZoneRepository::class);
    }

    public function testFindZonesByService(): void
    {
        $principalDefense = $this->em->getRepository(Service::class)->findOneBy([
            'nom_service' => 'Direction Générale',
            'niveau_service' => 1
        ]);
        $this->assertNotNull($principalDefense);

        $serviceZones = $this->serviceZoneRepository->findBy(['service' => $principalDefense]);
        $this->assertCount(5, $serviceZones);
    }

    public function testFindServicesByZone(): void
    {
        $zoneBureau = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Bureau']);
        $this->assertNotNull($zoneBureau);
        $zoneServices = $this->serviceZoneRepository->findBy(['zone' => $zoneBureau]);
        $this->assertCount(7, $zoneServices);
    }

    public function testServiceZoneRepositoryBasicOperations(): void
    {
        $all = $this->serviceZoneRepository->findAll();
        $this->assertEquals(20, count($all));

        $securityService = $this->em->getRepository(Service::class)->findOneBy(['nom_service' => 'Service Sécurité']);
        $zoneAlpha = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Alpha']);
        $this->assertNotNull($securityService);
        $this->assertNotNull($zoneAlpha);

        $serviceZone = $this->serviceZoneRepository->findOneBy([
            'service' => $securityService,
            'zone' => $zoneAlpha
        ]);
        $this->assertNotNull($serviceZone);

        $this->assertEquals(20, $this->serviceZoneRepository->count([]));
    }
}