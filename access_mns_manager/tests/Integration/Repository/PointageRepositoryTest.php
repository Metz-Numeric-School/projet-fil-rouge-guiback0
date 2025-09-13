<?php

namespace App\Tests\Integration\Repository;

use App\Repository\PointageRepository;
use App\Entity\Organisation;
use App\Entity\Badge;
use App\Tests\Shared\DatabaseKernelTestCase;

class PointageRepositoryTest extends DatabaseKernelTestCase
{
    private PointageRepository $pointageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointageRepository = static::getContainer()->get(PointageRepository::class);
    }

    public function testFindByOrganisation(): void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $pointages = $this->pointageRepository->findByOrganisation($organisation->getId());
        $this->assertNotEmpty($pointages);
    }

    public function testFindByOrganisationWithLimit(): void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $pointages = $this->pointageRepository->findByOrganisation($organisation->getId(), 3);
        $this->assertLessThanOrEqual(3, count($pointages));
    }

    public function testPointageRepositoryBasicOperations(): void
    {
        $all = $this->pointageRepository->findAll();
        $this->assertNotEmpty($all);
        $badge = $this->em->getRepository(Badge::class)->findOneBy(['numero_badge' => 200010]);
        $byBadge = $this->pointageRepository->findBy(['badge' => $badge]);
        $this->assertNotEmpty($byBadge);
    }
}