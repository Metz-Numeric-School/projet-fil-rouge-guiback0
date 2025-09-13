<?php

namespace App\Tests\Shared;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Badge;
use App\Entity\UserBadge;
use App\Entity\Badgeuse;
use App\Entity\Zone;
use App\Entity\Travailler;
use App\Entity\ServiceZone;
use App\Entity\Acces;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestEntityFactory
{
    public static function createTestOrganisation(): Organisation
    {
        $organisation = new Organisation();
        $organisation->setNomOrganisation('Test Organisation');
        $organisation->setEmail('contact@test.com');
        $organisation->setNomRue('Test Street');

        return $organisation;
    }

    public static function createTestService(Organisation $organisation, bool $isPrincipal = true): Service
    {
        $service = new Service();
        $service->setNomService('Test Service');
        $service->setNiveauService(1);
        $service->setIsPrincipal($isPrincipal);
        $service->setOrganisation($organisation);

        return $service;
    }

    public static function createTestUser(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, string $email = 'test-factory@example.com'): User
    {
        $organisation = self::createTestOrganisation();
        $service = self::createTestService($organisation);

        $user = new User();
        $user->setEmail($email);
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        $travailler = new Travailler();
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());

        // Utiliser addTravail qui gère automatiquement les deux côtés de la relation
        $user->addTravail($travailler);

        $em->persist($organisation);
        $em->persist($service);
        $em->persist($user);
        $em->persist($travailler);

        return $user;
    }

    public static function createTestUserWithBadge(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, string $email = 'test-factory@example.com'): array
    {
        $user = self::createTestUser($em, $passwordHasher, $email);

        $badge = new Badge();
        $badge->setNumeroBadge(123);
        $badge->setTypeBadge('permanent');
        $badge->setDateCreation(new \DateTime());

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user);
        $userBadge->setBadge($badge);
        $userBadge->setDateAttribution(new \DateTime());

        $em->persist($badge);
        $em->persist($userBadge);

        return ['user' => $user, 'badge' => $badge, 'userBadge' => $userBadge];
    }

    public static function createTestZone(string $nomZone = 'Test Zone'): Zone
    {
        $zone = new Zone();
        $zone->setNomZone($nomZone);
        $zone->setDescription('Description pour ' . $nomZone);
        $zone->setCapacite(50);

        return $zone;
    }

    public static function createTestBadgeuse(?Zone $zone = null): Badgeuse
    {
        if (!$zone) {
            $zone = self::createTestZone();
        }

        $badgeuse = new Badgeuse();
        $badgeuse->setReference('REF-BADGE-' . uniqid());
        $badgeuse->setDateInstallation(new \DateTime());

        return $badgeuse;
    }

    public static function createTestUserWithBadgeuse(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, string $email = 'test-factory@example.com'): array
    {
        $userWithBadge = self::createTestUserWithBadge($em, $passwordHasher, $email);

        // Flush pour s'assurer que les relations sont établies
        $em->flush();

        $zone = self::createTestZone();
        $badgeuse = self::createTestBadgeuse($zone);

        // Créer l'accès pour le service à la zone
        $service = $userWithBadge['user']->getTravail()->first()->getService();

        $serviceZone = new ServiceZone();
        $serviceZone->setService($service);
        $serviceZone->setZone($zone);

        $acces = new Acces();
        $acces->setNomAcces('Acces Test');
        $acces->setDateInstallation(new \DateTime());
        $acces->setBadgeuse($badgeuse);
        $acces->setZone($zone);

        $em->persist($zone);
        $em->persist($badgeuse);
        $em->persist($serviceZone);
        $em->persist($acces);

        return [
            'user' => $userWithBadge['user'],
            'badge' => $userWithBadge['badge'],
            'userBadge' => $userWithBadge['userBadge'],
            'badgeuse' => $badgeuse,
            'zone' => $zone,
            'acces' => $acces
        ];
    }

    public static function createCompleteTestSetup(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, string $email = 'test-factory@example.com'): array
    {
        $organisation = self::createTestOrganisation();
        $principalService = self::createTestService($organisation, true);
        $secondaryService = self::createTestService($organisation, false);
        $secondaryService->setNomService('Secondary Service');

        $user = new User();
        $user->setEmail($email);
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        // Relation avec service principal
        $travaillerPrincipal = new Travailler();
        $travaillerPrincipal->setUtilisateur($user);
        $travaillerPrincipal->setService($principalService);
        $travaillerPrincipal->setDateDebut(new \DateTime());

        // Relation avec service secondaire
        $travaillerSecondaire = new Travailler();
        $travaillerSecondaire->setUtilisateur($user);
        $travaillerSecondaire->setService($secondaryService);
        $travaillerSecondaire->setDateDebut(new \DateTime());

        $badge = new Badge();
        $badge->setNumeroBadge(123);
        $badge->setTypeBadge('permanent');
        $badge->setDateCreation(new \DateTime());

        $userBadge = new UserBadge();
        $userBadge->setUtilisateur($user);
        $userBadge->setBadge($badge);
        $userBadge->setDateAttribution(new \DateTime());

        $zone = self::createTestZone();
        $badgeuse = self::createTestBadgeuse($zone);

        $serviceZone = new ServiceZone();
        $serviceZone->setService($principalService);
        $serviceZone->setZone($zone);

        $acces = new Acces();
        $acces->setNomAcces('Acces Test');
        $acces->setDateInstallation(new \DateTime());
        $acces->setBadgeuse($badgeuse);
        $acces->setZone($zone);

        // Persister toutes les entités
        $em->persist($organisation);
        $em->persist($principalService);
        $em->persist($secondaryService);
        $em->persist($user);
        $em->persist($travaillerPrincipal);
        $em->persist($travaillerSecondaire);
        $em->persist($badge);
        $em->persist($userBadge);
        $em->persist($zone);
        $em->persist($badgeuse);
        $em->persist($serviceZone);
        $em->persist($acces);

        return [
            'user' => $user,
            'organisation' => $organisation,
            'principal_service' => $principalService,
            'secondary_service' => $secondaryService,
            'badge' => $badge,
            'userBadge' => $userBadge,
            'badgeuse' => $badgeuse,
            'zone' => $zone,
            'acces' => $acces
        ];
    }
}
