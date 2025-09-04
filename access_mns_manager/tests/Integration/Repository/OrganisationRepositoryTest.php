<?php

namespace App\Tests\Integration\Repository;

use App\Repository\OrganisationRepository;
use App\Entity\Organisation;
use App\Tests\Shared\DatabaseKernelTestCase;

class OrganisationRepositoryTest extends DatabaseKernelTestCase
{
    private OrganisationRepository $organisationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organisationRepository = static::getContainer()->get(OrganisationRepository::class);
    }

    public function testFindOrganisationByName(): void
    {
        $found = $this->organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);

        $this->assertNotNull($found);
        $this->assertEquals('Ministère de la Défense', $found->getNomOrganisation());
        $this->assertEquals('contact@defense.gouv.fr', $found->getEmail());
    }

    public function testFindOrganisationByEmail(): void
    {
        $found = $this->organisationRepository->findOneBy(['email' => 'contact@interieur.gouv.fr']);

        $this->assertNotNull($found);
        $this->assertEquals('Ministère de l\'Intérieur', $found->getNomOrganisation());
        $this->assertEquals('contact@interieur.gouv.fr', $found->getEmail());
    }

    public function testCountOrganisations(): void
    {
        $count = $this->organisationRepository->count([]);

        // TestFixtures loads 3 organisations (Défense, Intérieur, Économie)
        $this->assertEquals(3, $count);
    }

    public function testFindOrganisationsWithServices(): void
    {
        $organisation = $this->organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $this->assertNotNull($organisation);

        $qb = $this->organisationRepository->createQueryBuilder('o');
        $qb->leftJoin('o.services', 's')
           ->addSelect('s')
           ->where('o.id = :orgId')
           ->setParameter('orgId', $organisation->getId());

        $result = $qb->getQuery()->getOneOrNullResult();

        $this->assertNotNull($result);
        $this->assertEquals('Ministère de la Défense', $result->getNomOrganisation());
        // TestFixtures loads 3 services for Défense: Direction Générale, Service IT, Service Sécurité, Service Logistique
        $this->assertCount(4, $result->getServices());
    }

    public function testFindOrganisationsWithUsers(): void
    {
        $organisation = $this->organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $this->assertNotNull($organisation);

        $qb = $this->organisationRepository->createQueryBuilder('o');
        $qb->leftJoin('o.services', 's')
           ->leftJoin('s.travail', 't')
           ->leftJoin('t.Utilisateur', 'u') // propriété réellement nommée Utilisateur
           ->addSelect('s', 't', 'u')
           ->where('o.id = :orgId')
           ->setParameter('orgId', $organisation->getId());

        $result = $qb->getQuery()->getOneOrNullResult();

        $this->assertNotNull($result);
        $this->assertEquals('Ministère de la Défense', $result->getNomOrganisation());
        $this->assertNotEmpty($result->getServices());
    }

    public function testOrganisationRepositoryBasicOperations(): void
    {
        // Test findAll
        $all = $this->organisationRepository->findAll();
        $this->assertEquals(3, count($all));

        // Test find by existing data from fixtures
        $defenseOrg = $this->organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $this->assertNotNull($defenseOrg);
        $this->assertEquals('Ministère de la Défense', $defenseOrg->getNomOrganisation());

        // Test findBy with criteria
        $byCity = $this->organisationRepository->findBy(['ville' => 'Paris']);
        $this->assertEquals(3, count($byCity)); // All 3 organisations are in Paris in fixtures
    }

    public function testFindOrganisationsBySiret(): void
    {
        $found = $this->organisationRepository->findOneBy(['siret' => '12345678901234']);

        $this->assertNotNull($found);
        $this->assertEquals('Ministère de la Défense', $found->getNomOrganisation());
        $this->assertEquals('12345678901234', $found->getSiret());
    }
}