<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Travailler;
use App\Tests\Shared\TestEntityFactory;
use PHPUnit\Framework\TestCase;

class TravaillerTest extends TestCase
{
    private Travailler $travailler;

    protected function setUp(): void
    {
        $this->travailler = new Travailler();
    }

    public function testTravaillerCreation(): void
    {
        $this->assertInstanceOf(Travailler::class, $this->travailler);
        $this->assertNull($this->travailler->getId());
        $this->assertNull($this->travailler->getUtilisateur());
        $this->assertNull($this->travailler->getService());
        $this->assertNull($this->travailler->getDateDebut());
        $this->assertNull($this->travailler->getDateFin());
    }

    public function testGettersAndSetters(): void
    {
        $organisation = TestEntityFactory::createTestOrganisation();
        $service = TestEntityFactory::createTestService($organisation);
        $user = $this->createUser();
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-12-31');

        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin);

        $this->assertSame($user, $this->travailler->getUtilisateur());
        $this->assertSame($service, $this->travailler->getService());
        $this->assertSame($dateDebut, $this->travailler->getDateDebut());
        $this->assertSame($dateFin, $this->travailler->getDateFin());
    }

    public function testNullableFields(): void
    {
        $this->travailler->setUtilisateur(null);
        $this->travailler->setService(null);
        $this->travailler->setDateFin(null);

        $this->assertNull($this->travailler->getUtilisateur());
        $this->assertNull($this->travailler->getService());
        $this->assertNull($this->travailler->getDateFin());
    }

    public function testCurrentWorkAssignment(): void
    {
        $organisation = TestEntityFactory::createTestOrganisation();
        $service = TestEntityFactory::createTestService($organisation);
        $user = $this->createUser();

        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime('2024-01-01'))
            ->setDateFin(null); // Travail en cours

        $this->assertSame($user, $this->travailler->getUtilisateur());
        $this->assertSame($service, $this->travailler->getService());
        $this->assertNull($this->travailler->getDateFin());
    }

    public function testCompletedWorkAssignment(): void
    {
        $organisation = TestEntityFactory::createTestOrganisation();
        $service = TestEntityFactory::createTestService($organisation);
        $user = $this->createUser();
        $dateDebut = new \DateTime('2023-01-01');
        $dateFin = new \DateTime('2023-12-31');

        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin);

        $this->assertLessThan($dateFin, $dateDebut);
        $this->assertLessThan(new \DateTime(), $dateFin);
    }

    public function testMultipleServiceAssignments(): void
    {
        $organisation = TestEntityFactory::createTestOrganisation();
        $serviceIT = TestEntityFactory::createTestService($organisation);
        $serviceRH = TestEntityFactory::createTestService($organisation, false);
        $user = $this->createUser();

        $workIT = (new Travailler())
            ->setUtilisateur($user)
            ->setService($serviceIT)
            ->setDateDebut(new \DateTime('2024-01-01'));

        $workRH = (new Travailler())
            ->setUtilisateur($user)
            ->setService($serviceRH)
            ->setDateDebut(new \DateTime('2024-02-01'))
            ->setDateFin(new \DateTime('2024-06-30'));

        $this->assertSame($user, $workIT->getUtilisateur());
        $this->assertSame($user, $workRH->getUtilisateur());
        $this->assertNotSame($workIT->getService(), $workRH->getService());
        $this->assertNull($workIT->getDateFin());
        $this->assertNotNull($workRH->getDateFin());
    }

    public function testWorkLifecycle(): void
    {
        $organisation = TestEntityFactory::createTestOrganisation();
        $service = TestEntityFactory::createTestService($organisation);
        $user = $this->createUser();

        // État initial
        $this->assertNull($this->travailler->getUtilisateur());
        
        // Début du travail
        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        
        $this->assertNotNull($this->travailler->getUtilisateur());
        $this->assertNull($this->travailler->getDateFin());
        
        // Fin du travail
        $this->travailler->setDateFin(new \DateTime());
        $this->assertNotNull($this->travailler->getDateFin());
    }

    // Helper method minimal - TestEntityFactory s'occupe du reste
    private function createUser(string $email = 'user@example.com'): \App\Entity\User
    {
        $user = new \App\Entity\User();
        $user->setEmail($email)
             ->setPassword('password')
             ->setNom('Nom')
             ->setPrenom('Prenom');
        return $user;
    }
}