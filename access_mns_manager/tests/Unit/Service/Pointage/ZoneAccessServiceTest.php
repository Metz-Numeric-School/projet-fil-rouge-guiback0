<?php

namespace App\Tests\Unit\Service\Pointage;

use App\Entity\User;
use App\Entity\Zone;
use App\Entity\Badgeuse;
use App\Entity\Acces;
use App\Entity\Service;
use App\Entity\ServiceZone;
use App\Entity\Travailler;
use App\Entity\Organisation;
use App\Exception\BadgeException;
use App\Service\Pointage\ZoneAccessService;
use App\Service\User\UserService;
use App\Tests\Shared\DatabaseKernelTestCase;

class ZoneAccessServiceTest extends DatabaseKernelTestCase
{
    private ZoneAccessService $zoneAccessService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zoneAccessService = static::getContainer()->get(ZoneAccessService::class);
    }

    public function testGetBadgeuseZonesWithValidBadgeuse(): void
    {
        $zone1 = new Zone();
        $zone1->setNomZone('Zone Test 1')
            ->setDescription('Test zone 1')
            ->setCapacite(50);
        $this->em->persist($zone1);

        $zone2 = new Zone();
        $zone2->setNomZone('Zone Test 2')
            ->setDescription('Test zone 2')
            ->setCapacite(30);
        $this->em->persist($zone2);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-ZONE-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces1 = new Acces();
        $acces1->setNomAcces('Accès Zone 1')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone1)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces1);

        $acces2 = new Acces();
        $acces2->setNomAcces('Accès Zone 2')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone2)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces2);

        $this->em->flush();

        $zones = $this->zoneAccessService->getBadgeuseZones($badgeuse);
        
        $this->assertCount(2, $zones);
        $this->assertContains($zone1, $zones);
        $this->assertContains($zone2, $zones);
    }

    public function testGetBadgeuseZoneNamesReturnsStringArray(): void
    {
        $zone = new Zone();
        $zone->setNomZone('Zone Nominative')
            ->setDescription('Test zone')
            ->setCapacite(25);
        $this->em->persist($zone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('TEST-ZONE-002')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Nominatif')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $this->em->flush();

        $zoneNames = $this->zoneAccessService->getBadgeuseZoneNames($badgeuse);
        
        $this->assertCount(1, $zoneNames);
        $this->assertEquals('Zone Nominative', $zoneNames[0]);
    }

    public function testCanAccessZoneWithValidUserAndZone(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Access Org')
            ->setEmail('access@test.com')
            ->setNomRue('Test Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Test Access Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Accessible Zone')
            ->setDescription('Zone accessible')
            ->setCapacite(50);
        $this->em->persist($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($service)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $user = new User();
        $user->setEmail('access@test.com')
            ->setNom('Access')
            ->setPrenom('Test')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $result = $this->zoneAccessService->canAccessZone($user, $zone);
        $this->assertTrue($result);
    }

    public function testCanAccessZoneWithNoAccess(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('No Access Org')
            ->setEmail('noaccess@test.com')
            ->setNomRue('Test Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('No Access Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Restricted Zone')
            ->setDescription('Zone restreinte')
            ->setCapacite(10);
        $this->em->persist($zone);

        $user = new User();
        $user->setEmail('noaccess@test.com')
            ->setNom('NoAccess')
            ->setPrenom('Test')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $result = $this->zoneAccessService->canAccessZone($user, $zone);
        $this->assertFalse($result);
    }

    public function testCanAccessZoneWithNullZone(): void
    {
        $user = new User();
        $user->setEmail('nullzone@test.com')
            ->setNom('Null')
            ->setPrenom('Zone')
            ->setPassword('password');
        $this->em->persist($user);
        $this->em->flush();

        $result = $this->zoneAccessService->canAccessZone($user, null);
        $this->assertFalse($result);
    }

    public function testIsBadgeuseInPrincipalZoneWithPrincipalAccess(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Principal Zone Org')
            ->setEmail('principal@test.com')
            ->setNomRue('Principal Street');
        $this->em->persist($organisation);

        $principalService = new Service();
        $principalService->setNomService('Principal Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($principalService);

        $zone = new Zone();
        $zone->setNomZone('Principal Zone')
            ->setDescription('Zone principale')
            ->setCapacite(100);
        $this->em->persist($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($principalService)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('PRINCIPAL-BADGE-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Principal')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $user = new User();
        $user->setEmail('principal@test.com')
            ->setNom('Principal')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($principalService)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $result = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);
        $this->assertTrue($result);
    }

    public function testIsBadgeuseInPrincipalZoneWithNoPrincipalService(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('No Principal Org')
            ->setEmail('noprincipal@test.com')
            ->setNomRue('No Principal Street');
        $this->em->persist($organisation);

        $secondaryService = new Service();
        $secondaryService->setNomService('Secondary Service')
            ->setNiveauService(2)
            ->setIsPrincipal(false)
            ->setOrganisation($organisation);
        $this->em->persist($secondaryService);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('NO-PRINCIPAL-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $user = new User();
        $user->setEmail('noprincipal@test.com')
            ->setNom('NoPrincipal')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($secondaryService)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $result = $this->zoneAccessService->isBadgeuseInPrincipalZone($badgeuse, $user);
        $this->assertFalse($result);
    }

    public function testValidateUserZoneAccessSuccess(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Valid Access Org')
            ->setEmail('valid@test.com')
            ->setNomRue('Valid Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('Valid Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Valid Zone')
            ->setDescription('Zone valide')
            ->setCapacite(50);
        $this->em->persist($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($service)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('VALID-ACCESS-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Valid')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $currentUser = new User();
        $currentUser->setEmail('current@test.com')
            ->setNom('Current')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($currentUser);

        $currentTravailler = new Travailler();
        $currentTravailler->setUtilisateur($currentUser)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($currentTravailler);

        $targetUser = new User();
        $targetUser->setEmail('target@test.com')
            ->setNom('Target')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($targetUser);

        $targetTravailler = new Travailler();
        $targetTravailler->setUtilisateur($targetUser)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($targetTravailler);

        $this->em->flush();

        // Mock the current user in UserService
        $userService = $this->createMock(UserService::class);
        $userService->method('getUserOrganisation')
            ->willReturn($organisation);
        $userService->method('getCurrentUserOrganisation')
            ->willReturn($organisation);

        $zoneAccessService = new ZoneAccessService($this->em, $userService);

        // Should not throw exception
        $zoneAccessService->validateUserZoneAccess($targetUser, $badgeuse);
        $this->assertTrue(true); // Test passes if no exception thrown
    }

    public function testValidateUserZoneAccessWithDifferentOrganisation(): void
    {
        $org1 = new Organisation();
        $org1->setNomOrganisation('Org 1')
            ->setEmail('org1@test.com')
            ->setNomRue('Street 1');
        $this->em->persist($org1);

        $org2 = new Organisation();
        $org2->setNomOrganisation('Org 2')
            ->setEmail('org2@test.com')
            ->setNomRue('Street 2');
        $this->em->persist($org2);

        $service1 = new Service();
        $service1->setNomService('Service 1')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($org1);
        $this->em->persist($service1);

        $service2 = new Service();
        $service2->setNomService('Service 2')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($org2);
        $this->em->persist($service2);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('DIFF-ORG-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $targetUser = new User();
        $targetUser->setEmail('target@org1.com')
            ->setNom('Target')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($targetUser);

        $targetTravailler = new Travailler();
        $targetTravailler->setUtilisateur($targetUser)
            ->setService($service1)
            ->setDateDebut(new \DateTime());
        $this->em->persist($targetTravailler);

        $this->em->flush();

        // Mock different organisations
        $userService = $this->createMock(UserService::class);
        $userService->method('getUserOrganisation')
            ->willReturn($org1);
        $userService->method('getCurrentUserOrganisation')
            ->willReturn($org2); // Different organisation

        $zoneAccessService = new ZoneAccessService($this->em, $userService);

        $this->expectException(BadgeException::class);
        $this->expectExceptionMessage('Accès refusé - organisation différente');

        $zoneAccessService->validateUserZoneAccess($targetUser, $badgeuse);
    }

    public function testValidateUserZoneAccessWithNoZonesConfigured(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('No Zones Org')
            ->setEmail('nozones@test.com')
            ->setNomRue('No Zones Street');
        $this->em->persist($organisation);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('NO-ZONES-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $user = new User();
        $user->setEmail('nozones@test.com')
            ->setNom('NoZones')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $this->em->flush();

        $userService = $this->createMock(UserService::class);
        $userService->method('getUserOrganisation')
            ->willReturn($organisation);
        $userService->method('getCurrentUserOrganisation')
            ->willReturn($organisation);

        $zoneAccessService = new ZoneAccessService($this->em, $userService);

        $this->expectException(BadgeException::class);
        $this->expectExceptionMessage('Aucune zone configurée pour cette badgeuse');

        $zoneAccessService->validateUserZoneAccess($user, $badgeuse);
    }

    public function testValidateUserZoneAccessWithNoZoneAccess(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('No Zone Access Org')
            ->setEmail('nozoneaccess@test.com')
            ->setNomRue('No Zone Access Street');
        $this->em->persist($organisation);

        $service = new Service();
        $service->setNomService('No Zone Access Service')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service);

        $zone = new Zone();
        $zone->setNomZone('Restricted Zone')
            ->setDescription('Zone restreinte')
            ->setCapacite(5);
        $this->em->persist($zone);

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('RESTRICTED-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Restreint')
            ->setDateInstallation(new \DateTime())
            ->setZone($zone)
            ->setBadgeuse($badgeuse);
        $this->em->persist($acces);

        $user = new User();
        $user->setEmail('restricted@test.com')
            ->setNom('Restricted')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        $travailler = new Travailler();
        $travailler->setUtilisateur($user)
            ->setService($service)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler);

        $this->em->flush();

        $userService = $this->createMock(UserService::class);
        $userService->method('getUserOrganisation')
            ->willReturn($organisation);
        $userService->method('getCurrentUserOrganisation')
            ->willReturn($organisation);

        $zoneAccessService = new ZoneAccessService($this->em, $userService);

        $this->expectException(BadgeException::class);
        $this->expectExceptionMessage('Accès refusé à cette zone');

        $zoneAccessService->validateUserZoneAccess($user, $badgeuse);
    }

    public function testGetBadgeuseZonesWithNullZones(): void
    {
        $badgeuse = new Badgeuse();
        $badgeuse->setReference('NULL-ZONES-001')
            ->setDateInstallation(new \DateTime());
        $this->em->persist($badgeuse);

        $acces = new Acces();
        $acces->setNomAcces('Accès Sans Zone')
            ->setDateInstallation(new \DateTime())
            ->setBadgeuse($badgeuse);
        // Note: No zone set
        $this->em->persist($acces);

        $this->em->flush();

        $zones = $this->zoneAccessService->getBadgeuseZones($badgeuse);
        $this->assertEmpty($zones);
    }

    public function testCanAccessZoneWithMultipleServices(): void
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Multi Service Org')
            ->setEmail('multi@test.com')
            ->setNomRue('Multi Street');
        $this->em->persist($organisation);

        $service1 = new Service();
        $service1->setNomService('Service 1')
            ->setNiveauService(1)
            ->setIsPrincipal(true)
            ->setOrganisation($organisation);
        $this->em->persist($service1);

        $service2 = new Service();
        $service2->setNomService('Service 2')
            ->setNiveauService(2)
            ->setIsPrincipal(false)
            ->setOrganisation($organisation);
        $this->em->persist($service2);

        $zone = new Zone();
        $zone->setNomZone('Multi Access Zone')
            ->setDescription('Zone multi-service')
            ->setCapacite(75);
        $this->em->persist($zone);

        // Only service2 has access to zone
        $serviceZone = new ServiceZone();
        $serviceZone->setService($service2)
            ->setZone($zone);
        $this->em->persist($serviceZone);

        $user = new User();
        $user->setEmail('multi@test.com')
            ->setNom('Multi')
            ->setPrenom('User')
            ->setPassword('password');
        $this->em->persist($user);

        // User works in both services
        $travailler1 = new Travailler();
        $travailler1->setUtilisateur($user)
            ->setService($service1)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler1);

        $travailler2 = new Travailler();
        $travailler2->setUtilisateur($user)
            ->setService($service2)
            ->setDateDebut(new \DateTime());
        $this->em->persist($travailler2);

        $this->em->flush();

        $result = $this->zoneAccessService->canAccessZone($user, $zone);
        $this->assertTrue($result); // Should have access via service2
    }
}