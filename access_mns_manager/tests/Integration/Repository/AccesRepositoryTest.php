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
        $zoneAlpha = $this->em->getRepository(Zone::class)->findOneBy(['nom_zone' => 'Zone Sécurisée Alpha']);
        $accesInZone = $this->accesRepository->findBy(['zone' => $zoneAlpha]);
        $this->assertCount(2, $accesInZone);
    }

    public function testFindByBadgeuse(): void
    {
        $badgeuse = $this->em->getRepository(Badgeuse::class)->findOneBy(['reference' => 'BADGE-ALPHA-001']);
        $accesByBadgeuse = $this->accesRepository->findBy(['badgeuse' => $badgeuse]);
        $this->assertCount(1, $accesByBadgeuse);
    }
}