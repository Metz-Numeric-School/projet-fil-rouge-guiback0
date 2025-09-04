<?php

namespace App\Tests\Shared;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\CommonFixtures;

abstract class DatabaseWebTestCase extends WebTestCase
{
    use DatabaseFixturesTrait;

    protected EntityManagerInterface $em;
    protected $client; // Client HTTP Symfony

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->loadBaseFixtures([CommonFixtures::class]);
    }
}
