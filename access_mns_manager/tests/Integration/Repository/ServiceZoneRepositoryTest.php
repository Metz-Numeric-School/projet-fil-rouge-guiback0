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
            'nom_service' => 'Service principal',
            'is_principal' => true
        ]);
        $this->assertNotNull($principalDefense);

        $serviceZones = $this->serviceZoneRepository->findBy(['service' => $principalDefense]);
        $this->assertCount(3, $serviceZones); // d'après les fixtures : principale, defense_bureau, public
    }

    public function testFindServicesByZone(): void
    {
        $zonePrincipale = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Principale - Entrée/Sortie']);
        $this->assertNotNull($zonePrincipale);
        $zoneServices = $this->serviceZoneRepository->findBy(['zone' => $zonePrincipale]);
        // D'après les fixtures, plusieurs services ont accès à la zone principale
        $this->assertGreaterThan(0, count($zoneServices));
    }

    public function testServiceZoneRepositoryBasicOperations(): void
    {
        $all = $this->serviceZoneRepository->findAll();
        // Il y a 24 relations ServiceZone dans les fixtures
        $this->assertEquals(24, count($all));

        $securityService = $this->em->getRepository(Service::class)->findOneBy(['nom_service' => 'Service Sécurité']);
        $zoneAlpha = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Défense Alpha']);
        $this->assertNotNull($securityService);
        $this->assertNotNull($zoneAlpha);

        $serviceZone = $this->serviceZoneRepository->findOneBy([
            'service' => $securityService,
            'zone' => $zoneAlpha
        ]);
        $this->assertNotNull($serviceZone);

        $this->assertEquals(24, $this->serviceZoneRepository->count([]));
    }
}