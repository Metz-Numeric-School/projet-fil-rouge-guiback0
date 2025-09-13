<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Organisation;
use App\Entity\Service;
use PHPUnit\Framework\TestCase;

class OrganisationTest extends TestCase
{
    private Organisation $organisation;

    protected function setUp(): void
    {
        $this->organisation = new Organisation();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->organisation->getId());
        $this->assertNull($this->organisation->getNomOrganisation());
        $this->assertCount(0, $this->organisation->getServices());
    }

    public function testCoreSetters(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        $this->organisation
            ->setNomOrganisation('Org')
            ->setTelephone('+331234')
            ->setEmail('contact@org.test')
            ->setSiteWeb('https://org.test')
            ->setDateCreation($date)
            ->setSiret('12345678901234')
            ->setCa(1000.50)
            ->setNumeroRue(10)
            ->setSuffixRue('bis')
            ->setNomRue('Rue X')
            ->setCodePostal('75000')
            ->setVille('Paris')
            ->setPays('France');
        $this->assertSame('Org', $this->organisation->getNomOrganisation());
        $this->assertSame('+331234', $this->organisation->getTelephone());
        $this->assertSame('contact@org.test', $this->organisation->getEmail());
        $this->assertSame('https://org.test', $this->organisation->getSiteWeb());
        $this->assertSame($date, $this->organisation->getDateCreation());
        $this->assertSame('12345678901234', $this->organisation->getSiret());
        $this->assertSame(1000.50, $this->organisation->getCa());
        $this->assertSame(10, $this->organisation->getNumeroRue());
        $this->assertSame('bis', $this->organisation->getSuffixRue());
        $this->assertSame('Rue X', $this->organisation->getNomRue());
        $this->assertSame('75000', $this->organisation->getCodePostal());
        $this->assertSame('Paris', $this->organisation->getVille());
        $this->assertSame('France', $this->organisation->getPays());
    }

    public function testServiceCollection(): void
    {
        $service = new Service();
        if (method_exists($service, 'setNomService')) { $service->setNomService('S1'); }
        if (method_exists($service, 'setOrganisation')) { $service->setOrganisation($this->organisation); }
        $this->organisation->addService($service);
        $this->assertCount(1, $this->organisation->getServices());
        $this->organisation->addService($service); // pas de doublon
        $this->assertCount(1, $this->organisation->getServices());
        $this->organisation->removeService($service);
        $this->assertCount(0, $this->organisation->getServices());
    }

    public function testNullableFieldsRemainNull(): void
    {
        $this->assertNull($this->organisation->getTelephone());
        $this->assertNull($this->organisation->getSiteWeb());
        $this->assertNull($this->organisation->getSiret());
        $this->assertNull($this->organisation->getCa());
    }
}