<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Service;
use App\Entity\Organisation;
use App\Repository\ServiceRepository;
use App\Tests\Shared\DatabaseKernelTestCase;

class ServiceRepositoryTest extends DatabaseKernelTestCase
{
    private ServiceRepository $serviceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serviceRepository = static::getContainer()->get(ServiceRepository::class);
    }

    public function testFindServicesByOrganisation(): void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $services = $this->serviceRepository->findBy(['organisation' => $organisation]);
        $this->assertNotEmpty($services);
    }

    public function testFindPrincipalServices(): void
    {
        $principalServices = $this->serviceRepository->findBy(['is_principal' => true]);

        // TestFixtures creates 3 principal services (one per organisation)
        $this->assertEquals(3, count($principalServices));
        
        $serviceNames = array_map(fn($s) => $s->getNomService(), $principalServices);
        $this->assertContains('Service principal', $serviceNames);
        // All principal services are named 'Service principal' in fixtures
        $uniqueNames = array_unique($serviceNames);
        $this->assertCount(1, $uniqueNames);
        $this->assertEquals('Service principal', $uniqueNames[0]);
    }

    public function testFindServicesByNiveau(): void
    {
        $niveau1Services = $this->serviceRepository->findBy(['niveau_service' => 1]);
        $niveau2Services = $this->serviceRepository->findBy(['niveau_service' => 2]);
        $niveau3Services = $this->serviceRepository->findBy(['niveau_service' => 3]);

        // CommonFixtures: 4 level 1 services (3 principal + RH), 3 level 2 services, 1 level 3 service, 1 level 4 service
        $this->assertEquals(4, count($niveau1Services));
        $this->assertEquals(3, count($niveau2Services));
        $this->assertEquals(1, count($niveau3Services));
        
        // Verify level 2 services contain expected ones from CommonFixtures
        $niveau2Names = array_map(fn($s) => $s->getNomService(), $niveau2Services);
        $this->assertContains('Service Informatique', $niveau2Names);
        $this->assertContains('Service Logistique', $niveau2Names);
        // Service RH is level 1 in CommonFixtures, not level 2
        $this->assertContains('Service Finances Publiques', $niveau2Names);
    }

    public function testFindServicesWithZones(): void
    {
        // Get principal defense service which has multiple zone access
        $principalDefense = $this->serviceRepository->findOneBy(['nom_service' => 'Service principal', 'niveau_service' => 1]);
        $this->assertNotNull($principalDefense);

        $qb = $this->serviceRepository->createQueryBuilder('s');
        $qb->leftJoin('s.serviceZones', 'sz')
           ->leftJoin('sz.zone', 'z')
           ->addSelect('sz', 'z')
           ->where('s.id = :serviceId')
           ->setParameter('serviceId', $principalDefense->getId());

        $result = $qb->getQuery()->getOneOrNullResult();

        $this->assertNotNull($result);
        $this->assertEquals('Service principal', $result->getNomService());
        // CommonFixtures gives principal defense service access to: principale, alpha, beta, bureau, public
        $this->assertCount(5, $result->getServiceZones());
    }

    public function testServiceHierarchy(): void
    {
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $organisation = $organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $this->assertNotNull($organisation);

        $qb = $this->serviceRepository->createQueryBuilder('s');
        $qb->where('s.organisation = :org')
           ->setParameter('org', $organisation)
           ->orderBy('s.niveau_service', 'ASC');

        $hierarchyServices = $qb->getQuery()->getResult();

        $this->assertCount(4, $hierarchyServices); // Defense has 4 services
        $this->assertEquals(1, $hierarchyServices[0]->getNiveauService());
        $this->assertEquals(2, $hierarchyServices[1]->getNiveauService());
        $this->assertEquals(2, $hierarchyServices[2]->getNiveauService());  
        $this->assertEquals(3, $hierarchyServices[3]->getNiveauService());
    }

    public function testCountServicesByOrganisation(): void
    {
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $defenseOrg = $organisationRepository->findOneBy(['nom_organisation' => 'Ministère de la Défense']);
        $interieurOrg = $organisationRepository->findOneBy(['nom_organisation' => 'Ministère de l\'Intérieur']);
        $economieOrg = $organisationRepository->findOneBy(['nom_organisation' => 'Ministère de l\'Économie']);

        $defenseCount = $this->serviceRepository->count(['organisation' => $defenseOrg]);
        $interieurCount = $this->serviceRepository->count(['organisation' => $interieurOrg]);
        $economieCount = $this->serviceRepository->count(['organisation' => $economieOrg]);

        $this->assertEquals(4, $defenseCount);
        $this->assertEquals(3, $interieurCount);
        $this->assertEquals(2, $economieCount);
    }

    public function testServiceRepositoryBasicOperations(): void
    {
        // Test findAll
        $all = $this->serviceRepository->findAll();
        $this->assertEquals(9, count($all)); // CommonFixtures creates 9 total services

        // Test find by name
        $found = $this->serviceRepository->findOneBy(['nom_service' => 'Service Informatique']);
        $this->assertNotNull($found);
        $this->assertEquals('Service Informatique', $found->getNomService());
        $this->assertEquals(2, $found->getNiveauService());

        // Test custom query
        $qb = $this->serviceRepository->createQueryBuilder('s');
        $this->assertInstanceOf(\Doctrine\ORM\QueryBuilder::class, $qb);
        
        // Test total count
        $totalCount = $this->serviceRepository->count([]);
        $this->assertEquals(9, $totalCount);
    }
}