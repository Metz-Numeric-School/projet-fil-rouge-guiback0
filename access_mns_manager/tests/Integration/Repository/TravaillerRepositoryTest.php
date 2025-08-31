<?php

namespace App\Tests\Integration\Repository;

use App\Repository\TravaillerRepository;
use App\Entity\Service;
use App\Entity\User;
use App\Tests\Shared\DatabaseKernelTestCase;

class TravaillerRepositoryTest extends DatabaseKernelTestCase
{
    private TravaillerRepository $travaillerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travaillerRepository = static::getContainer()->get(TravaillerRepository::class);
    }

    public function testFindActiveWorkRelationships(): void
    {
        $active = $this->travaillerRepository->findBy(['date_fin' => null]);
        $this->assertNotEmpty($active);
    }

    public function testFindByUserAndService(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'jean.dupont@defense.test.com']);
        $service = $this->em->getRepository(Service::class)->findOneBy(['nom_service' => 'Service IT']);
        $found = $this->travaillerRepository->findOneBy(['Utilisateur' => $user, 'service' => $service]);
        $this->assertNotNull($found);
    }
}