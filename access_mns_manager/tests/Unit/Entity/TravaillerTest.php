<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Travailler;
use App\Entity\User;
use App\Entity\Service;
use App\Tests\Shared\DatabaseKernelTestCase;

class TravaillerTest extends DatabaseKernelTestCase
{
    private Travailler $travailler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travailler = new Travailler();
    }

    private function makeUser(string $email = 'user@example.com'): User
    {
        $u = new User();
        $u->setEmail($email)
          ->setPassword('pass')
          ->setNom('Nom')
          ->setPrenom('Prenom');
        return $u;
    }

    private function makeService(string $nom = 'Service'): Service
    {
        $s = new Service();
        // Supposant existence setters basiques; sinon adapter
        if (method_exists($s, 'setNomService')) {
            $s->setNomService($nom);
        }
        return $s;
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

    public function testUtilisateurGetterSetter(): void
    {
        $user = $this->makeUser();
        $this->travailler->setUtilisateur($user);
        $this->assertSame($user, $this->travailler->getUtilisateur());
    }

    public function testServiceGetterSetter(): void
    {
        $service = $this->makeService();
        $this->travailler->setService($service);
        $this->assertSame($service, $this->travailler->getService());
    }

    public function testDateDebutGetterSetter(): void
    {
        $date = new \DateTime('2024-01-15');
        $this->travailler->setDateDebut($date);
        $this->assertSame($date, $this->travailler->getDateDebut());
    }

    public function testDateFinGetterSetter(): void
    {
        $date = new \DateTime('2024-12-31');
        $this->travailler->setDateFin($date);
        $this->assertSame($date, $this->travailler->getDateFin());
    }

    public function testCompleteTravaillerData(): void
    {
        $user = $this->makeUser('jean.dupont@ministere.gouv.fr');
        $service = $this->makeService('Direction Générale');
        $dateDebut = new \DateTime('2024-01-15');
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
        $this->assertLessThan($this->travailler->getDateFin(), $this->travailler->getDateDebut());
    }

    public function testNullableUserAndService(): void
    {
        $this->travailler->setUtilisateur(null);
        $this->travailler->setService(null);
        $this->assertNull($this->travailler->getUtilisateur());
        $this->assertNull($this->travailler->getService());
    }

    public function testNullableDateFin(): void
    {
        $this->travailler->setDateFin(null);
        $this->assertNull($this->travailler->getDateFin());
    }

    public function testCurrentWork(): void
    {
        $user = $this->makeUser();
        $service = $this->makeService('Actuel');
        $dateDebut = new \DateTime('2024-01-01');

        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut($dateDebut)
            ->setDateFin(null);

        $this->assertSame($user, $this->travailler->getUtilisateur());
        $this->assertSame($service, $this->travailler->getService());
        $this->assertSame($dateDebut, $this->travailler->getDateDebut());
        $this->assertNull($this->travailler->getDateFin());
    }

    public function testPastWork(): void
    {
        $user = $this->makeUser();
        $service = $this->makeService('Ancien');
        $dateDebut = new \DateTime('2023-01-01');
        $dateFin = new \DateTime('2023-12-31');

        $this->travailler
            ->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin);

        $this->assertLessThan($this->travailler->getDateFin(), $this->travailler->getDateDebut());
        $this->assertLessThan(new \DateTime(), $this->travailler->getDateFin());
    }

    public function testMultipleTravaillerAssignments(): void
    {
        $user1 = $this->makeUser('user1@example.com');
        $user2 = $this->makeUser('user2@example.com');
        $serviceIT = $this->makeService('Service IT');
        $serviceRH = $this->makeService('Service RH');

        $travailler1 = (new Travailler())
            ->setUtilisateur($user1)
            ->setService($serviceIT)
            ->setDateDebut(new \DateTime('2024-01-01'));

        $travailler2 = (new Travailler())
            ->setUtilisateur($user2)
            ->setService($serviceRH)
            ->setDateDebut(new \DateTime('2024-02-01'));

        $this->assertSame($user1, $travailler1->getUtilisateur());
        $this->assertSame($serviceIT, $travailler1->getService());
        $this->assertSame($user2, $travailler2->getUtilisateur());
        $this->assertSame($serviceRH, $travailler2->getService());
        $this->assertNotSame($travailler1->getUtilisateur(), $travailler2->getUtilisateur());
        $this->assertNotSame($travailler1->getService(), $travailler2->getService());
    }

    public function testSameUserMultipleServices(): void
    {
        $user = $this->makeUser('manager@example.com');
        $primaryService = $this->makeService('Direction Générale');
        $secondaryService = $this->makeService('Service IT');

        $primaryWork = (new Travailler())
            ->setUtilisateur($user)
            ->setService($primaryService)
            ->setDateDebut(new \DateTime('2024-01-01'));

        $secondaryWork = (new Travailler())
            ->setUtilisateur($user)
            ->setService($secondaryService)
            ->setDateDebut(new \DateTime('2024-02-01'))
            ->setDateFin(new \DateTime('2024-06-30'));

        $this->assertSame($user, $primaryWork->getUtilisateur());
        $this->assertSame($user, $secondaryWork->getUtilisateur());
        $this->assertNotSame($primaryWork->getService(), $secondaryWork->getService());
        $this->assertNull($primaryWork->getDateFin());
        $this->assertNotNull($secondaryWork->getDateFin());
    }

    public function testWorkHistoryTimeline(): void
    {
        $user = $this->makeUser();
        $oldService = $this->makeService('Ancien Service');
        $currentService = $this->makeService('Service Actuel');

        $oldWork = (new Travailler())
            ->setUtilisateur($user)
            ->setService($oldService)
            ->setDateDebut(new \DateTime('2023-01-01'))
            ->setDateFin(new \DateTime('2023-12-31'));

        $currentWork = (new Travailler())
            ->setUtilisateur($user)
            ->setService($currentService)
            ->setDateDebut(new \DateTime('2024-01-01'));

        $this->assertLessThan($currentWork->getDateDebut(), $oldWork->getDateFin());
        $this->assertNotNull($oldWork->getDateFin());
        $this->assertNull($currentWork->getDateFin());
        $this->assertSame($user, $oldWork->getUtilisateur());
        $this->assertSame($user, $currentWork->getUtilisateur());
    }

    public function testServiceTransfer(): void
    {
        $user = $this->makeUser();
        $departments = [
            ['Service RH', '2024-01-01', '2024-03-31'],
            ['Service IT', '2024-04-01', '2024-06-30'],
            ['Direction Générale', '2024-07-01', null]
        ];

        $travailHistory = [];
        foreach ($departments as [$serviceName, $startDate, $endDate]) {
            $service = $this->makeService($serviceName);
            $work = (new Travailler())
                ->setUtilisateur($user)
                ->setService($service)
                ->setDateDebut(new \DateTime($startDate));
            if ($endDate) {
                $work->setDateFin(new \DateTime($endDate));
            }
            $travailHistory[] = $work;
        }

        $this->assertCount(3, $travailHistory);
        for ($i = 0; $i < count($travailHistory) - 1; $i++) {
            if ($travailHistory[$i]->getDateFin()) {
                $this->assertLessThanOrEqual(
                    $travailHistory[$i + 1]->getDateDebut(),
                    $travailHistory[$i]->getDateFin()
                );
            }
        }
        $this->assertNotNull($travailHistory[0]->getDateFin());
        $this->assertNotNull($travailHistory[1]->getDateFin());
        $this->assertNull($travailHistory[2]->getDateFin());
    }

    public function testTemporaryAssignment(): void
    {
        $user = $this->makeUser();
        $temporaryService = $this->makeService('Mission Temporaire');
        $today = new \DateTime();
        $nextMonth = (clone $today)->add(new \DateInterval('P1M'));

        $temporaryWork = (new Travailler())
            ->setUtilisateur($user)
            ->setService($temporaryService)
            ->setDateDebut($today)
            ->setDateFin($nextMonth);

        $this->assertSame($user, $temporaryWork->getUtilisateur());
        $this->assertSame($temporaryService, $temporaryWork->getService());
        $this->assertLessThan($temporaryWork->getDateFin(), $temporaryWork->getDateDebut());
        $this->assertGreaterThan(new \DateTime('-1 second'), $temporaryWork->getDateFin());
    }

    public function testWorkLifecycle(): void
    {
        $user = $this->makeUser();
        $service = $this->makeService('Cycle');

        $this->assertNull($this->travailler->getUtilisateur());
        $this->assertNull($this->travailler->getService());
        $this->assertNull($this->travailler->getDateDebut());
        $this->assertNull($this->travailler->getDateFin());

        $this->travailler->setUtilisateur($user);
        $this->travailler->setService($service);
        $this->travailler->setDateDebut(new \DateTime());

        $this->assertNotNull($this->travailler->getUtilisateur());
        $this->assertNotNull($this->travailler->getService());
        $this->assertNotNull($this->travailler->getDateDebut());
        $this->assertNull($this->travailler->getDateFin());

        $this->travailler->setDateFin(new \DateTime());
        $this->assertNotNull($this->travailler->getDateFin());
    }
}