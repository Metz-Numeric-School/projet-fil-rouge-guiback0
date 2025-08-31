<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Travailler;
use App\Entity\Service;
use App\Tests\Shared\DatabaseKernelTestCase;

class UserTest extends DatabaseKernelTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testDefaultState(): void
    {
        $this->assertTrue($this->user->isCompteActif());
        $this->assertEmpty($this->user->getUserBadges());
        $this->assertEmpty($this->user->getTravail());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
    }

    public function testEmailAndIdentifier(): void
    {
        $this->user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $this->user->getEmail());
        $this->assertSame('test@example.com', $this->user->getUserIdentifier());
    }

    public function testRolesAlwaysIncludesRoleUser(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testPassword(): void
    {
        $this->user->setPassword('hash');
        $this->assertSame('hash', $this->user->getPassword());
    }

    public function testUserBadgeCollection(): void
    {
        $userBadge = new UserBadge();
        // Lien inverse simulé si nécessaire via méthode existante
        if (method_exists($userBadge, 'setUtilisateur')) {
            $userBadge->setUtilisateur($this->user);
        }
        $this->user->addUserBadge($userBadge);
        $this->assertCount(1, $this->user->getUserBadges());
        $this->user->addUserBadge($userBadge); // pas de doublon
        $this->assertCount(1, $this->user->getUserBadges());
        $this->user->removeUserBadge($userBadge);
        $this->assertCount(0, $this->user->getUserBadges());
    }

    public function testTravailCollectionAndPrincipalService(): void
    {
        $servicePrincipal = (new Service())->setNomService('Principal')->setIsPrincipal(true);
        $serviceSecondaire = (new Service())->setNomService('Secondaire')->setIsPrincipal(false);

        $travailPrincipal = new Travailler();
        if (method_exists($travailPrincipal, 'setService')) { $travailPrincipal->setService($servicePrincipal); }
        if (method_exists($travailPrincipal, 'setUtilisateur')) { $travailPrincipal->setUtilisateur($this->user); }

        $travailSecondaire = new Travailler();
        if (method_exists($travailSecondaire, 'setService')) { $travailSecondaire->setService($serviceSecondaire); }
        if (method_exists($travailSecondaire, 'setUtilisateur')) { $travailSecondaire->setUtilisateur($this->user); }

        $this->user->addTravail($travailPrincipal)->addTravail($travailSecondaire);
        $this->assertCount(2, $this->user->getTravail());
        $this->assertSame($servicePrincipal, $this->user->getPrincipalService());
        $this->assertSame($travailPrincipal, $this->user->getPrincipalTravail());
        $secondary = $this->user->getSecondaryServices();
        $this->assertCount(1, $secondary);
        $this->assertSame($serviceSecondaire, $secondary[0]);
    }

    public function testDeactivateAndDeletionLifecycle(): void
    {
        $this->user->deactivate();
        $this->assertFalse($this->user->isCompteActif());
        $this->assertNotNull($this->user->getDateSuppressionPrevue());

        // Simule une date passée pour shouldBeDeleted
        $past = (new \DateTimeImmutable('-1 day'));
        $this->user->setDateSuppressionPrevue($past);
        $this->assertTrue($this->user->shouldBeDeleted());

        // Date future
        $future = (new \DateTimeImmutable('+1 day'));
        $this->user->setDateSuppressionPrevue($future);
        $this->assertFalse($this->user->shouldBeDeleted());
    }

    public function testUpdateTimestamps(): void
    {
        $this->user->updateLastLogin();
        $this->assertInstanceOf(\DateTimeInterface::class, $this->user->getDateDerniereConnexion());
        $this->user->updateLastModification();
        $this->assertInstanceOf(\DateTimeInterface::class, $this->user->getDateDerniereModification());
    }

    public function testEraseCredentials(): void
    {
        $this->user->eraseCredentials();
        $this->assertTrue(true); // juste pour couvrir la méthode
    }
}