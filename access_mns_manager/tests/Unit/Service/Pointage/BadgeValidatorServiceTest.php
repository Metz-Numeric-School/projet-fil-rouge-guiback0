<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\Badge;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Pointage;
use App\Entity\Badgeuse;
use App\Exception\BadgeException;
use App\Service\Pointage\BadgeValidatorService;
use App\Tests\Shared\DatabaseKernelTestCase;

class BadgeValidatorServiceTest extends DatabaseKernelTestCase
{
    private BadgeValidatorService $badgeValidatorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeValidatorService = static::getContainer()->get(BadgeValidatorService::class);
    }

    public function testGetUserFromBadgeWithValidBadge(): void
    {
        $user = new User();
        $user->setEmail('test@badge.com')
            ->setNom('Test')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $badge = new Badge();
        $badge->setNumeroBadge(999001)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user)
            ->setBadge($badge);
        $this->em->persist($userBadge);

        $this->em->flush();

        $result = $this->badgeValidatorService->getUserFromBadge($badge);
        $this->assertSame($user, $result);
    }

    public function testGetUserFromBadgeWithExpiredBadge(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999002)
            ->setTypeBadge('expired')
            ->setDateCreation(new \DateTime('-1 year'))
            ->setDateExpiration(new \DateTime('-1 day'));
        $this->em->persist($badge);
        $this->em->flush();

        $this->expectException(BadgeException::class);
        $this->expectExceptionMessage('Badge expiré');
        
        $this->badgeValidatorService->getUserFromBadge($badge);
    }

    public function testGetUserFromBadgeWithNonAssignedBadge(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999003)
            ->setTypeBadge('unassigned')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);
        $this->em->flush();

        $this->expectException(BadgeException::class);
        $this->expectExceptionMessage('Utilisateur non trouvé');
        
        $this->badgeValidatorService->getUserFromBadge($badge);
    }

    public function testGetUserActiveBadgeWithValidUser(): void
    {
        $user = new User();
        $user->setEmail('active@badge.com')
            ->setNom('Active')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $activeBadge = new Badge();
        $activeBadge->setNumeroBadge(999004)
            ->setTypeBadge('active')
            ->setDateCreation(new \DateTime());
        $this->em->persist($activeBadge);

        $expiredBadge = new Badge();
        $expiredBadge->setNumeroBadge(999005)
            ->setTypeBadge('expired')
            ->setDateCreation(new \DateTime('-1 year'))
            ->setDateExpiration(new \DateTime('-1 day'));
        $this->em->persist($expiredBadge);

        $activeUserBadge = new UserBadge();
        $activeUserBadge->setUtilisateur($user)->setBadge($activeBadge);
        $this->em->persist($activeUserBadge);

        $expiredUserBadge = new UserBadge();
        $expiredUserBadge->setUtilisateur($user)->setBadge($expiredBadge);
        $this->em->persist($expiredUserBadge);

        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadge($user);
        $this->assertSame($activeBadge, $result);
    }

    public function testGetUserActiveBadgeWithNoActiveBadges(): void
    {
        $user = new User();
        $user->setEmail('nobadge@test.com')
            ->setNom('No')
            ->setPrenom('Badge')
            ->setPassword('password');
        $this->em->persist($user);
        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadge($user);
        $this->assertNull($result);
    }

    public function testGetUserActiveBadgesReturnsFormattedArray(): void
    {
        $user = new User();
        $user->setEmail('multi@badge.com')
            ->setNom('Multi')
            ->setPrenom('Badge')
            ->setPassword('password');
        $this->em->persist($user);

        $badge1 = new Badge();
        $badge1->setNumeroBadge(999006)
            ->setTypeBadge('permanent')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge1);

        $badge2 = new Badge();
        $badge2->setNumeroBadge(999007)
            ->setTypeBadge('temporaire')
            ->setDateCreation(new \DateTime())
            ->setDateExpiration(new \DateTime('+1 year'));
        $this->em->persist($badge2);

        $userBadge1 = new UserBadge();
        $userBadge1->setUtilisateur($user)->setBadge($badge1);
        $this->em->persist($userBadge1);

        $userBadge2 = new UserBadge();
        $userBadge2->setUtilisateur($user)->setBadge($badge2);
        $this->em->persist($userBadge2);

        $this->em->flush();

        $result = $this->badgeValidatorService->getUserActiveBadges($user);
        
        $this->assertCount(2, $result);
        $this->assertEquals(999006, $result[0]['numero_badge']);
        $this->assertEquals('permanent', $result[0]['type_badge']);
        $this->assertTrue($result[0]['is_active']);
    }

    public function testGetUserBadgeHistoryWithDateRange(): void
    {
        $user = new User();
        $user->setEmail('history@badge.com')
            ->setNom('History')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $badge = new Badge();
        $badge->setNumeroBadge(999008)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user)->setBadge($badge);
        $this->em->persist($userBadge);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-BADGEUSE-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $pointage1 = new Pointage();
        $pointage1->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('-2 days'))
            ->setType('entree');
        $this->em->persist($pointage1);

        $pointage2 = new Pointage();
        $pointage2->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('-1 day'))
            ->setType('sortie');
        $this->em->persist($pointage2);

        $this->em->flush();

        $startDate = new \DateTime('-3 days');
        $endDate = new \DateTime('now');
        
        $result = $this->badgeValidatorService->getUserBadgeHistory($user, $startDate, $endDate);
        
        $this->assertCount(2, $result);
        $this->assertEquals('sortie', $result[0]['type']); // Plus récent en premier
        $this->assertEquals('entree', $result[1]['type']);
        $this->assertArrayHasKey('badgeuse', $result[0]);
        $this->assertEquals('TEST-BADGEUSE-001', $result[0]['badgeuse']['reference']);
    }

    public function testGetUserBadgeHistoryWithoutDateRange(): void
    {
        $user = new User();
        $user->setEmail('history2@badge.com')
            ->setNom('History2')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $badge = new Badge();
        $badge->setNumeroBadge(999009)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user)->setBadge($badge);
        $this->em->persist($userBadge);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-BADGEUSE-002')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $pointage = new Pointage();
        $pointage->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime())
            ->setType('entree');
        $this->em->persist($pointage);

        $this->em->flush();

        $result = $this->badgeValidatorService->getUserBadgeHistory($user);
        
        $this->assertCount(1, $result);
        $this->assertEquals('entree', $result[0]['type']);
    }

    public function testIsRecentEntryWithRecentEntry(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999010)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-BADGEUSE-003')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $recentPointage = new Pointage();
        $recentPointage->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('-2 hours'))
            ->setType('entree');
        $this->em->persist($recentPointage);

        $this->em->flush();

        $result = $this->badgeValidatorService->isRecentEntry($recentPointage);
        $this->assertTrue($result);
    }

    public function testIsRecentEntryWithOldEntry(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999011)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-BADGEUSE-004')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $oldPointage = new Pointage();
        $oldPointage->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('-10 hours'))
            ->setType('entree');
        $this->em->persist($oldPointage);

        $this->em->flush();

        $result = $this->badgeValidatorService->isRecentEntry($oldPointage);
        $this->assertFalse($result);
    }

    public function testIsRecentEntryWithSortieType(): void
    {
        $badge = new Badge();
        $badge->setNumeroBadge(999012)
            ->setTypeBadge('test')
            ->setDateCreation(new \DateTime());
        $this->em->persist($badge);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-BADGEUSE-005')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $sortiePointage = new Pointage();
        $sortiePointage->setBadge($badge)
            ->setBadgeuse($badgeuse)
            ->setHeure(new \DateTime('-1 hour'))
            ->setType('sortie');
        $this->em->persist($sortiePointage);

        $this->em->flush();

        $result = $this->badgeValidatorService->isRecentEntry($sortiePointage);
        $this->assertFalse($result);
    }
}