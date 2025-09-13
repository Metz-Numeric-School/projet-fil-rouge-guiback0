<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Organisation;
use App\Repository\ZoneRepository;
use App\Tests\Shared\DatabaseKernelTestCase;

class ZoneRepositoryTest extends DatabaseKernelTestCase
{
    private ZoneRepository $zoneRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zoneRepository = static::getContainer()->get(ZoneRepository::class);
    }

    public function testFindByOrganisation(): void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $zones = $this->zoneRepository->findByOrganisation($organisation);
        $this->assertNotEmpty($zones);
    }
}