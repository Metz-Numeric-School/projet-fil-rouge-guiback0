<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Gerer;
use App\Entity\User;
use App\Tests\Shared\DatabaseKernelTestCase;

class GererTest extends DatabaseKernelTestCase
{
    private Gerer $gerer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gerer = new Gerer();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->gerer->getId());
        $this->assertNull($this->gerer->getManageur());
        $this->assertNull($this->gerer->getEmploye());
    }

    public function testSetters(): void
    {
        $manager = new User();
        $employee = new User();
        $this->gerer->setManageur($manager)->setEmploye($employee);
        $this->assertSame($manager, $this->gerer->getManageur());
        $this->assertSame($employee, $this->gerer->getEmploye());
    }

    public function testNullable(): void
    {
        $this->gerer->setManageur(null)->setEmploye(null);
        $this->assertNull($this->gerer->getManageur());
        $this->assertNull($this->gerer->getEmploye());
    }

    public function testChangeManager(): void
    {
        $m1 = new User();
        $m2 = new User();
        $e = new User();
        $this->gerer->setManageur($m1)->setEmploye($e);
        $this->assertSame($m1, $this->gerer->getManageur());
        $this->gerer->setManageur($m2);
        $this->assertSame($m2, $this->gerer->getManageur());
        $this->assertSame($e, $this->gerer->getEmploye());
    }
}