<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Badge;
use App\Entity\Pointage;
use App\Exception\BadgeException;
use App\Service\Pointage\BadgeValidatorService;
use App\Tests\Shared\DatabaseKernelTestCase;
use App\Tests\Shared\TestEntityFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BadgeValidatorServiceTest extends DatabaseKernelTestCase
{
    private BadgeValidatorService $badgeValidatorService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeValidatorService = static::getContainer()->get(BadgeValidatorService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testGetUserFromBadgeSuccess(): void
    {
        $userWithBadge = TestEntityFactory::createTestUserWithBadge($this->em, $this->passwordHasher);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserFromBadge($userWithBadge['badge']);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userWithBadge['user']->getEmail(), $result->getEmail());
    }

    public function testGetUserFromBadgeExpiredThrowsException(): void
    {
        $userWithBadge = TestEntityFactory::createTestUserWithBadge($this->em, $this->passwordHasher);
        $userWithBadge['badge']->setDateExpiration(new \DateTime('-1 day'));
        $this->em->flush();

        $this->expectException(BadgeException::class);
        try {
            $this->badgeValidatorService->getUserFromBadge($userWithBadge['badge']);
        } catch (BadgeException $e) {
            $this->assertEquals(BadgeException::BADGE_EXPIRED, $e->getErrorCode());
            throw $e;
        }
    }

    public function testGetUserFromBadgeNotFoundThrowsException(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999)
            ->setTypeBadge('permanent')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);
        $this->em->flush();

        $this->expectException(BadgeException::class);
        try {
            $this->badgeValidatorService->getUserFromBadge($badge);
        } catch (BadgeException $e) {
            $this->assertEquals(BadgeException::USER_NOT_FOUND, $e->getErrorCode());
            throw $e;
        }
    }

    public function testGetUserActiveBadge(): void
    {
        $userWithBadge = TestEntityFactory::createTestUserWithBadge($this->em, $this->passwordHasher);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadge($userWithBadge['user']);

        $this->assertInstanceOf(Badge::class, $result);
        $this->assertEquals($userWithBadge['badge']->getNumeroBadge(), $result->getNumeroBadge());
    }

    public function testGetUserActiveBadgeReturnsNullWhenNoBadge(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadge($user);

        $this->assertNull($result);
    }

    public function testGetUserActiveBadges(): void
    {
        $userWithBadge = TestEntityFactory::createTestUserWithBadge($this->em, $this->passwordHasher);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadges($userWithBadge['user']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('numero_badge', $result[0]);
        $this->assertTrue($result[0]['is_active']);
    }

    public function testGetUserBadgeHistory(): void
    {
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher);

        // Créer un pointage simple
        $pointage = new Pointage();
        $pointage->setBadge($completeSetup['badge'])
            ->setBadgeuse($completeSetup['badgeuse'])
            ->setHeure(new \DateTime())
            ->setType('entree');
        $this->em->persist($pointage);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserBadgeHistory($completeSetup['user']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('entree', $result[0]['type']);
    }

    public function testIsRecentEntry(): void
    {
        $completeSetup = TestEntityFactory::createCompleteTestSetup($this->em, $this->passwordHasher);

        $recentPointage = new Pointage();
        $recentPointage->setBadge($completeSetup['badge'])
            ->setBadgeuse($completeSetup['badgeuse'])
            ->setHeure(new \DateTime('-2 hours'))
            ->setType('entree');

        $oldPointage = new Pointage();
        $oldPointage->setBadge($completeSetup['badge'])
            ->setBadgeuse($completeSetup['badgeuse'])
            ->setHeure(new \DateTime('-10 hours'))
            ->setType('entree');

        $sortiePointage = new Pointage();
        $sortiePointage->setBadge($completeSetup['badge'])
            ->setBadgeuse($completeSetup['badgeuse'])
            ->setHeure(new \DateTime('-1 hour'))
            ->setType('sortie');

        $this->assertTrue($this->badgeValidatorService->isRecentEntry($recentPointage));
        $this->assertFalse($this->badgeValidatorService->isRecentEntry($oldPointage));
        $this->assertFalse($this->badgeValidatorService->isRecentEntry($sortiePointage));
    }
}
