<?php

namespace App\Tests\Shared;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\DataFixtures\CommonFixtures;

abstract class DatabaseKernelTestCase extends KernelTestCase
{
    use DatabaseFixturesTrait;

    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        // Charge les fixtures de base (adapter si certaines classes spécifiques par test)
        $this->loadBaseFixtures([CommonFixtures::class]);
    }
}
