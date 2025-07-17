<?php

namespace App\DataFixtures;

use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\Zone;
use App\Entity\User;
use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Acces;
use App\Entity\UserBadge;
use App\Entity\Travailler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des organisations avec toutes les propriétés requises
        $organisation1 = new Organisation();
        $organisation1->setNomOrganisation('Ministère de la Défense');
        $organisation1->setEmail('contact@defense.gouv.fr');
        $organisation1->setDateCreation(new \DateTime('2020-01-01'));
        $organisation1->setSiret('12345678901234');
        $organisation1->setTelephone('01.23.45.67.89');
        $organisation1->setSiteWeb('https://defense.gouv.fr');
        $organisation1->setPays('France');
        $organisation1->setNomRue('Rue Saint-Dominique');
        $organisation1->setNumeroRue(14);
        $organisation1->setCodePostal('75007');
        $organisation1->setVille('Paris');
        $manager->persist($organisation1);

        $organisation2 = new Organisation();
        $organisation2->setNomOrganisation('Ministère de l\'Intérieur');
        $organisation2->setEmail('contact@interieur.gouv.fr');
        $organisation2->setDateCreation(new \DateTime('2019-06-15'));
        $organisation2->setSiret('98765432109876');
        $organisation2->setTelephone('01.98.76.54.32');
        $organisation2->setSiteWeb('https://interieur.gouv.fr');
        $organisation2->setPays('France');
        $organisation2->setNomRue('Place Beauvau');
        $organisation2->setNumeroRue(1);
        $organisation2->setCodePostal('75008');
        $organisation2->setVille('Paris');
        $manager->persist($organisation2);

        // Créer des services
        $service1 = new Service();
        $service1->setNomService('Service Informatique');
        $service1->setNiveauService(1);
        $service1->setOrganisation($organisation1);
        $manager->persist($service1);

        $service2 = new Service();
        $service2->setNomService('Service Sécurité');
        $service2->setNiveauService(2);
        $service2->setOrganisation($organisation1);
        $manager->persist($service2);

        $service3 = new Service();
        $service3->setNomService('Service RH');
        $service3->setNiveauService(1);
        $service3->setOrganisation($organisation2);
        $manager->persist($service3);

        // Créer des zones
        $zone1 = new Zone();
        $zone1->setNomZone('Zone Sécurisée A');
        $zone1->setDescription('Zone d\'accès restreint niveau 1');
        $zone1->setCapacite(50);
        $manager->persist($zone1);

        $zone2 = new Zone();
        $zone2->setNomZone('Zone Sécurisée B');
        $zone2->setDescription('Zone d\'accès restreint niveau 2');
        $zone2->setCapacite(25);
        $manager->persist($zone2);

        $zone3 = new Zone();
        $zone3->setNomZone('Zone Publique');
        $zone3->setDescription('Zone d\'accès libre');
        $zone3->setCapacite(100);
        $manager->persist($zone3);

        // Créer des utilisateurs
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setNom('Administrateur');
        $admin->setPrenom('Système');
        $admin->setTelephone('01.23.45.67.89');
        $admin->setDateInscription(new \DateTime('2020-01-01')); // Ajout de la date d'inscription
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail('jean.dupont@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->hasher->hashPassword($user1, 'password123'));
        $user1->setNom('Dupont');
        $user1->setPrenom('Jean');
        $user1->setTelephone('01.11.22.33.44');
        $user1->setDateInscription(new \DateTime('2021-03-15')); // Ajout de la date d'inscription
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('marie.martin@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->hasher->hashPassword($user2, 'password123'));
        $user2->setNom('Martin');
        $user2->setPrenom('Marie');
        $user2->setTelephone('01.55.66.77.88');
        $user2->setDateInscription(new \DateTime('2021-06-01')); // Ajout de la date d'inscription
        $manager->persist($user2);

        // Créer un utilisateur pour la deuxième organisation
        $user3 = new User();
        $user3->setEmail('pierre.durand@example.com');
        $user3->setRoles(['ROLE_USER']);
        $user3->setPassword($this->hasher->hashPassword($user3, 'password123'));
        $user3->setNom('Durand');
        $user3->setPrenom('Pierre');
        $user3->setTelephone('01.99.88.77.66');
        $user3->setDateInscription(new \DateTime('2021-08-01'));
        $manager->persist($user3);

        // Créer des badges avec toutes les propriétés requises
        $badge1 = new Badge();
        $badge1->setNumeroBadge(100001);
        $badge1->setTypeBadge('standard'); // Type de badge requis
        $badge1->setDateCreation(new \DateTime('2021-03-15')); // Date de création requise
        $badge1->setDateExpiration(new \DateTime('2024-03-15')); // Date d'expiration optionnelle
        $manager->persist($badge1);

        $badge2 = new Badge();
        $badge2->setNumeroBadge(100002);
        $badge2->setTypeBadge('premium'); // Type de badge requis
        $badge2->setDateCreation(new \DateTime('2021-06-01')); // Date de création requise
        $badge2->setDateExpiration(new \DateTime('2024-06-01')); // Date d'expiration optionnelle
        $manager->persist($badge2);

        $badge3 = new Badge();
        $badge3->setNumeroBadge(100003);
        $badge3->setTypeBadge('visiteur'); // Type de badge requis
        $badge3->setDateCreation(new \DateTime('2020-01-01')); // Date de création requise
        $badge3->setDateExpiration(new \DateTime('2023-01-01')); // Date d'expiration optionnelle
        $manager->persist($badge3);

        // Créer des badgeuses
        $badgeuse1 = new Badgeuse();
        $badgeuse1->setReference('BADGEUSE001');
        $badgeuse1->setDateInstallation(new \DateTime('2020-01-01'));
        $manager->persist($badgeuse1);

        $badgeuse2 = new Badgeuse();
        $badgeuse2->setReference('BADGEUSE002');
        $badgeuse2->setDateInstallation(new \DateTime('2020-01-01'));
        $manager->persist($badgeuse2);

        // Créer des accès
        $acces1 = new Acces();
        $acces1->setNumeroBadgeuse(1);
        $acces1->setDateInstallation(new \DateTime('2020-01-01'));
        $acces1->setZone($zone1);
        $acces1->setBadgeuse($badgeuse1);
        $manager->persist($acces1);

        $acces2 = new Acces();
        $acces2->setNumeroBadgeuse(2);
        $acces2->setDateInstallation(new \DateTime('2020-01-01'));
        $acces2->setZone($zone2);
        $acces2->setBadgeuse($badgeuse2);
        $manager->persist($acces2);

        // Créer des relations UserBadge (basé sur votre entité UserBadge)
        $userBadge1 = new UserBadge();
        $userBadge1->setUtilisateur($user1);
        $userBadge1->setBadge($badge1);
        $manager->persist($userBadge1);

        $userBadge2 = new UserBadge();
        $userBadge2->setUtilisateur($user2);
        $userBadge2->setBadge($badge2);
        $manager->persist($userBadge2);

        $userBadge3 = new UserBadge();
        $userBadge3->setUtilisateur($user3);
        $userBadge3->setBadge($badge3);
        $manager->persist($userBadge3);

        // Créer des relations Travailler (basé sur votre entité Travailler)
        $travaillerAdmin = new Travailler();
        $travaillerAdmin->setUtilisateur($admin);
        $travaillerAdmin->setService($service1);
        $travaillerAdmin->setDateDebut(new \DateTime('2020-01-01'));
        $manager->persist($travaillerAdmin);

        $travailler1 = new Travailler();
        $travailler1->setUtilisateur($user1);
        $travailler1->setService($service1);
        $travailler1->setDateDebut(new \DateTime('2021-03-15'));
        $manager->persist($travailler1);

        $travailler2 = new Travailler();
        $travailler2->setUtilisateur($user2);
        $travailler2->setService($service3); // Assigner Marie au Service RH (Ministère de l'Intérieur)
        $travailler2->setDateDebut(new \DateTime('2021-06-01'));
        $manager->persist($travailler2);

        $travailler3 = new Travailler();
        $travailler3->setUtilisateur($user3);
        $travailler3->setService($service2); // Assigner Pierre au Service Sécurité (Ministère de la Défense)
        $travailler3->setDateDebut(new \DateTime('2021-08-01'));
        $manager->persist($travailler3);

        $manager->flush();
    }
}
