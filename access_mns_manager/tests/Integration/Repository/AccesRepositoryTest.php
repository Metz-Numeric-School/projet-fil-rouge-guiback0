<?php

namespace App\Tests\Integration\Repository;

use App\Repository\AccesRepository;
use App\Entity\Zone;
use App\Entity\Badgeuse;
use App\Tests\Shared\DatabaseKernelTestCase;

class AccesRepositoryTest extends DatabaseKernelTestCase
{
    private AccesRepository $accesRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accesRepository = static::getContainer()->get(AccesRepository::class);
    }

    public function testFindByZone(): void
    {
        $zoneAlpha = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Défense Alpha']);
        $this->assertNotNull($zoneAlpha, 'Zone should exist in fixtures');
        $accesInZone = $this->accesRepository->findBy(['zone' => $zoneAlpha]);
        $this->assertCount(1, $accesInZone); // Une seule badgeuse par zone dans les fixtures
    }

    public function testFindByBadgeuse(): void
    {
        $badgeuse = $this->em->getRepository(Badgeuse::class)->findOneBy(['reference' => 'BADGE-DEFENSE-ALPHA-001']);
        $this->assertNotNull($badgeuse, 'Badgeuse should exist in fixtures');
        $accesByBadgeuse = $this->accesRepository->findBy(['badgeuse' => $badgeuse]);
        $this->assertCount(1, $accesByBadgeuse);
    }
}