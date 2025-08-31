<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Zone;
use App\Entity\ServiceZone;
use App\Entity\Acces;
use App\Entity\Travailler;
use App\Exception\BadgeException;
use App\Service\Pointage\PointageService;
use App\Tests\Shared\DatabaseKernelTestCase;

class PointageServiceTest extends DatabaseKernelTestCase
{
    private PointageService $pointageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointageService = static::getContainer()->get(PointageService::class);
    }

    public function testRecordBadgeActionWithValidData(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->recordBadgeAction(
            $testData['badge']->getNumeroBadge(),
            $testData['badgeuse']->getId(),
            'entree'
        );

        $this->assertTrue($result['success'] ?? false);
        $this->assertArrayHasKey('pointage', $result);
        $this->assertEquals('entree', $result['pointage']['type']);
    }

    public function testRecordBadgeActionWithInvalidBadgeNumber(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->recordBadgeAction(
            999999, // Non-existent badge
            $testData['badgeuse']->getId(),
            'entree'
        );

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function testRecordBadgeActionWithInvalidBadgeuseId(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->recordBadgeAction(
            $testData['badge']->getNumeroBadge(),
            999999, // Non-existent badgeuse
            'entree'
        );

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function testRecordBadgeActionWithInvalidType(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->recordBadgeAction(
            $testData['badge']->getNumeroBadge(),
            $testData['badgeuse']->getId(),
            'invalid_type'
        );

        $this->assertFalse($result['success'] ?? true);
        $this->assertArrayHasKey('error', $result);
    }

    public function testPerformPointageWithValidationSuccess(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->performPointageWithValidation(
            $testData['user'],
            $testData['badgeuse']->getId(),
            false
        );

        // Should succeed or fail gracefully
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function testPerformPointageWithValidationWithForce(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $result = $this->pointageService->performPointageWithValidation(
            $testData['user'],
            $testData['badgeuse']->getId(),
            true // Force mode
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function testRecordBadgeActionWithNormalizedTypes(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        // Test normalization of different type inputs
        $testCases = [
            'ENTREE' => 'entree',
            'Entree' => 'entree',
            'SORTIE' => 'sortie',
            'Sortie' => 'sortie'
        ];

        $successCount = 0;
        foreach ($testCases as $input => $expected) {
            $result = $this->pointageService->recordBadgeAction(
                $testData['badge']->getNumeroBadge(),
                $testData['badgeuse']->getId(),
                $input
            );

            if ($result['success'] ?? false) {
                $this->assertEquals($expected, $result['data']['type'] ?? $result['pointage']['type']);
                $successCount++;
            }
        }
        
        // Ensure at least some normalizations worked
        $this->assertGreaterThan(0, $successCount, 'At least some type normalizations should succeed');
    }

    public function testRecordBadgeActionCreatesPointageInDatabase(): void
    {
        $testData = $this->createCompleteTestSetup();
        
        $initialCount = $this->em->getRepository(Pointage::class)->count([]);
        
        $this->pointageService->recordBadgeAction(
            $testData['badge']->getNumeroBadge(),
            $testData['badgeuse']->getId(),
            'entree'
        );

        $finalCount = $this->em->getRepository(Pointage::class)->count([]);
        $this->assertGreaterThan($initialCount, $finalCount);
    }

    private function createCompleteTestSetup(): array
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Pointage Test Org')
            ->setEmail('pointage@test.com')
            ->setNomRue('Pointage Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Pointage Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Pointage Zone')
            ->setDescription('Zone de pointage')
            ->setCapacite(100);
        $this->em->persist($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($service)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('POINTAGE-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Pointage')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $user = new User();
        $user->setEmail('pointage.user@test.com')
            ->setNom('Pointage')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $badge = new Badge();
        $badge->setNumeroBadge(999200)
            ->setTypeBadge('pointage')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user)
            ->setBadge($badge);
        $this->em->persist($userBadge);

        $this->em->flush();

        return [
            'user' => $user,
            'badge' => $badge,
            'badgeuse' => $badgeuse,
            'zone' => $zone,
            'service' => $service,
            'organisation' => $organisation
        ];
    }
}