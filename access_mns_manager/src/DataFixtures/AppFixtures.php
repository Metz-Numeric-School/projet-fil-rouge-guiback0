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
use App\Entity\ServiceZone;
use App\Entity\Gerer;
use App\Entity\Pointage;
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

        $organisation3 = new Organisation();
        $organisation3->setNomOrganisation('Entreprise TechSecure');
        $organisation3->setEmail('contact@techsecure.com');
        $organisation3->setDateCreation(new \DateTime('2018-03-20'));
        $organisation3->setSiret('11122233344556');
        $organisation3->setTelephone('01.45.67.89.01');
        $organisation3->setSiteWeb('https://techsecure.com');
        $organisation3->setPays('France');
        $organisation3->setNomRue('Avenue des Champs-Élysées');
        $organisation3->setNumeroRue(123);
        $organisation3->setCodePostal('75008');
        $organisation3->setVille('Paris');
        $manager->persist($organisation3);

        // Créer des services diversifiés
        $service1 = new Service();
        $service1->setNomService('Service Informatique');
        $service1->setNiveauService(1);
        $service1->setOrganisation($organisation1);
        $manager->persist($service1);

        $service2 = new Service();
        $service2->setNomService('Service Sécurité');
        $service2->setNiveauService(3);
        $service2->setOrganisation($organisation1);
        $manager->persist($service2);

        $service3 = new Service();
        $service3->setNomService('Service RH');
        $service3->setNiveauService(1);
        $service3->setOrganisation($organisation2);
        $manager->persist($service3);

        $service4 = new Service();
        $service4->setNomService('Service Opérations');
        $service4->setNiveauService(2);
        $service4->setOrganisation($organisation2);
        $manager->persist($service4);

        $service5 = new Service();
        $service5->setNomService('Développement');
        $service5->setNiveauService(1);
        $service5->setOrganisation($organisation3);
        $manager->persist($service5);

        $service6 = new Service();
        $service6->setNomService('Support Technique');
        $service6->setNiveauService(2);
        $service6->setOrganisation($organisation3);
        $manager->persist($service6);

        // Créer des zones diversifiées
        $zone1 = new Zone();
        $zone1->setNomZone('Zone Sécurisée Niveau 1');
        $zone1->setDescription('Zone d\'accès restreint - Classification Confidentiel');
        $zone1->setCapacite(50);
        $manager->persist($zone1);

        $zone2 = new Zone();
        $zone2->setNomZone('Zone Sécurisée Niveau 2');
        $zone2->setDescription('Zone d\'accès très restreint - Classification Secret');
        $zone2->setCapacite(25);
        $manager->persist($zone2);

        $zone3 = new Zone();
        $zone3->setNomZone('Zone Publique');
        $zone3->setDescription('Zone d\'accès libre - Accueil et espaces communs');
        $zone3->setCapacite(200);
        $manager->persist($zone3);

        $zone4 = new Zone();
        $zone4->setNomZone('Zone Technique');
        $zone4->setDescription('Zone technique - Serveurs et équipements');
        $zone4->setCapacite(15);
        $manager->persist($zone4);

        $zone5 = new Zone();
        $zone5->setNomZone('Zone Direction');
        $zone5->setDescription('Zone réservée à la direction et réunions confidentielles');
        $zone5->setCapacite(30);
        $manager->persist($zone5);

        // Créer des utilisateurs avec tous les champs
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setNom('Administrateur');
        $admin->setPrenom('Système');
        $admin->setDateNaissance(new \DateTime('1980-05-15'));
        $admin->setTelephone('01.23.45.67.89');
        $admin->setDateInscription(new \DateTime('2020-01-01'));
        $admin->setAdresse('14 Rue Saint-Dominique, 75007 Paris');
        $admin->setHorraire(new \DateTime('08:00:00'));
        $admin->setHeureDebut(new \DateTime('08:00:00'));
        $admin->setJoursSemaineTravaille(5);
        $admin->setPoste('Administrateur Système');
        $manager->persist($admin);

        $manager1 = new User();
        $manager1->setEmail('director@defense.gouv.fr');
        $manager1->setRoles(['ROLE_MANAGER']);
        $manager1->setPassword($this->hasher->hashPassword($manager1, 'manager123'));
        $manager1->setNom('Dubois');
        $manager1->setPrenom('François');
        $manager1->setDateNaissance(new \DateTime('1975-09-22'));
        $manager1->setTelephone('01.23.45.67.90');
        $manager1->setDateInscription(new \DateTime('2020-01-15'));
        $manager1->setAdresse('25 Avenue Bosquet, 75007 Paris');
        $manager1->setHorraire(new \DateTime('07:30:00'));
        $manager1->setHeureDebut(new \DateTime('07:30:00'));
        $manager1->setJoursSemaineTravaille(5);
        $manager1->setPoste('Directeur Service Informatique');
        $manager->persist($manager1);

        $user1 = new User();
        $user1->setEmail('jean.dupont@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->hasher->hashPassword($user1, 'password123'));
        $user1->setNom('Dupont');
        $user1->setPrenom('Jean');
        $user1->setDateNaissance(new \DateTime('1990-03-10'));
        $user1->setTelephone('01.11.22.33.44');
        $user1->setDateInscription(new \DateTime('2021-03-15'));
        $user1->setAdresse('12 Rue de la Paix, 75001 Paris');
        $user1->setHorraire(new \DateTime('09:00:00'));
        $user1->setHeureDebut(new \DateTime('09:00:00'));
        $user1->setJoursSemaineTravaille(5);
        $user1->setPoste('Développeur Senior');
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('marie.martin@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->hasher->hashPassword($user2, 'password123'));
        $user2->setNom('Martin');
        $user2->setPrenom('Marie');
        $user2->setDateNaissance(new \DateTime('1985-11-28'));
        $user2->setTelephone('01.55.66.77.88');
        $user2->setDateInscription(new \DateTime('2021-06-01'));
        $user2->setAdresse('8 Boulevard Saint-Germain, 75005 Paris');
        $user2->setHorraire(new \DateTime('08:30:00'));
        $user2->setHeureDebut(new \DateTime('08:30:00'));
        $user2->setJoursSemaineTravaille(4);
        $user2->setPoste('Responsable RH');
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('pierre.durand@example.com');
        $user3->setRoles(['ROLE_USER']);
        $user3->setPassword($this->hasher->hashPassword($user3, 'password123'));
        $user3->setNom('Durand');
        $user3->setPrenom('Pierre');
        $user3->setDateNaissance(new \DateTime('1988-07-14'));
        $user3->setTelephone('01.99.88.77.66');
        $user3->setDateInscription(new \DateTime('2021-08-01'));
        $user3->setAdresse('45 Rue du Faubourg Saint-Antoine, 75011 Paris');
        $user3->setHorraire(new \DateTime('22:00:00'));
        $user3->setHeureDebut(new \DateTime('22:00:00'));
        $user3->setJoursSemaineTravaille(5);
        $user3->setPoste('Agent de Sécurité');
        $manager->persist($user3);

        $user4 = new User();
        $user4->setEmail('sophie.bernard@techsecure.com');
        $user4->setRoles(['ROLE_USER']);
        $user4->setPassword($this->hasher->hashPassword($user4, 'password123'));
        $user4->setNom('Bernard');
        $user4->setPrenom('Sophie');
        $user4->setDateNaissance(new \DateTime('1992-12-03'));
        $user4->setTelephone('01.44.55.66.77');
        $user4->setDateInscription(new \DateTime('2022-01-10'));
        $user4->setAdresse('77 Avenue Marceau, 75016 Paris');
        $user4->setHorraire(new \DateTime('09:30:00'));
        $user4->setHeureDebut(new \DateTime('09:30:00'));
        $user4->setJoursSemaineTravaille(5);
        $user4->setPoste('Analyste Cybersécurité');
        $manager->persist($user4);

        $user5 = new User();
        $user5->setEmail('lucas.petit@techsecure.com');
        $user5->setRoles(['ROLE_USER']);
        $user5->setPassword($this->hasher->hashPassword($user5, 'password123'));
        $user5->setNom('Petit');
        $user5->setPrenom('Lucas');
        $user5->setDateNaissance(new \DateTime('1995-04-18'));
        $user5->setTelephone('01.33.44.55.66');
        $user5->setDateInscription(new \DateTime('2022-03-01'));
        $user5->setAdresse('15 Rue de Rivoli, 75004 Paris');
        $user5->setHorraire(new \DateTime('10:00:00'));
        $user5->setHeureDebut(new \DateTime('10:00:00'));
        $user5->setJoursSemaineTravaille(5);
        $user5->setPoste('Support Technique Junior');
        $manager->persist($user5);

        // Créer des badges diversifiés
        $badge1 = new Badge();
        $badge1->setNumeroBadge(100001);
        $badge1->setTypeBadge('admin');
        $badge1->setDateCreation(new \DateTime('2020-01-01'));
        $manager->persist($badge1);

        $badge2 = new Badge();
        $badge2->setNumeroBadge(100002);
        $badge2->setTypeBadge('manager');
        $badge2->setDateCreation(new \DateTime('2020-01-15'));
        $manager->persist($badge2);

        $badge3 = new Badge();
        $badge3->setNumeroBadge(100003);
        $badge3->setTypeBadge('standard');
        $badge3->setDateCreation(new \DateTime('2021-03-15'));
        $manager->persist($badge3);

        $badge4 = new Badge();
        $badge4->setNumeroBadge(100004);
        $badge4->setTypeBadge('standard');
        $badge4->setDateCreation(new \DateTime('2021-06-01'));
        $manager->persist($badge4);

        $badge5 = new Badge();
        $badge5->setNumeroBadge(100005);
        $badge5->setTypeBadge('security');
        $badge5->setDateCreation(new \DateTime('2021-08-01'));
        $manager->persist($badge5);

        $badge6 = new Badge();
        $badge6->setNumeroBadge(100006);
        $badge6->setTypeBadge('visiteur');
        $badge6->setDateCreation(new \DateTime('2022-01-10'));
        $badge6->setDateExpiration(new \DateTime('2024-01-10'));
        $manager->persist($badge6);

        $badge7 = new Badge();
        $badge7->setNumeroBadge(100007);
        $badge7->setTypeBadge('temporaire');
        $badge7->setDateCreation(new \DateTime('2022-03-01'));
        $badge7->setDateExpiration(new \DateTime('2023-03-01'));
        $manager->persist($badge7);

        // Créer des badgeuses
        $badgeuse1 = new Badgeuse();
        $badgeuse1->setReference('BADGEUSE_ENTREE_PRINCIPALE');
        $badgeuse1->setDateInstallation(new \DateTime('2020-01-01'));
        $manager->persist($badgeuse1);

        $badgeuse2 = new Badgeuse();
        $badgeuse2->setReference('BADGEUSE_ZONE_SECURISEE_1');
        $badgeuse2->setDateInstallation(new \DateTime('2020-01-01'));
        $manager->persist($badgeuse2);

        $badgeuse3 = new Badgeuse();
        $badgeuse3->setReference('BADGEUSE_ZONE_SECURISEE_2');
        $badgeuse3->setDateInstallation(new \DateTime('2020-01-15'));
        $manager->persist($badgeuse3);

        $badgeuse4 = new Badgeuse();
        $badgeuse4->setReference('BADGEUSE_ZONE_TECHNIQUE');
        $badgeuse4->setDateInstallation(new \DateTime('2020-02-01'));
        $manager->persist($badgeuse4);

        $badgeuse5 = new Badgeuse();
        $badgeuse5->setReference('BADGEUSE_ZONE_DIRECTION');
        $badgeuse5->setDateInstallation(new \DateTime('2020-02-15'));
        $manager->persist($badgeuse5);

        // Créer des accès pour chaque zone
        $acces1 = new Acces();
        $acces1->setNumeroBadgeuse(1);
        $acces1->setDateInstallation(new \DateTime('2020-01-01'));
        $acces1->setZone($zone3); // Zone publique
        $acces1->setBadgeuse($badgeuse1);
        $manager->persist($acces1);

        $acces2 = new Acces();
        $acces2->setNumeroBadgeuse(2);
        $acces2->setDateInstallation(new \DateTime('2020-01-01'));
        $acces2->setZone($zone1); // Zone sécurisée niveau 1
        $acces2->setBadgeuse($badgeuse2);
        $manager->persist($acces2);

        $acces3 = new Acces();
        $acces3->setNumeroBadgeuse(3);
        $acces3->setDateInstallation(new \DateTime('2020-01-15'));
        $acces3->setZone($zone2); // Zone sécurisée niveau 2
        $acces3->setBadgeuse($badgeuse3);
        $manager->persist($acces3);

        $acces4 = new Acces();
        $acces4->setNumeroBadgeuse(4);
        $acces4->setDateInstallation(new \DateTime('2020-02-01'));
        $acces4->setZone($zone4); // Zone technique
        $acces4->setBadgeuse($badgeuse4);
        $manager->persist($acces4);

        $acces5 = new Acces();
        $acces5->setNumeroBadgeuse(5);
        $acces5->setDateInstallation(new \DateTime('2020-02-15'));
        $acces5->setZone($zone5); // Zone direction
        $acces5->setBadgeuse($badgeuse5);
        $manager->persist($acces5);

        // Créer des relations UserBadge
        $userBadge1 = new UserBadge();
        $userBadge1->setUtilisateur($admin);
        $userBadge1->setBadge($badge1);
        $manager->persist($userBadge1);

        $userBadge2 = new UserBadge();
        $userBadge2->setUtilisateur($manager1);
        $userBadge2->setBadge($badge2);
        $manager->persist($userBadge2);

        $userBadge3 = new UserBadge();
        $userBadge3->setUtilisateur($user1);
        $userBadge3->setBadge($badge3);
        $manager->persist($userBadge3);

        $userBadge4 = new UserBadge();
        $userBadge4->setUtilisateur($user2);
        $userBadge4->setBadge($badge4);
        $manager->persist($userBadge4);

        $userBadge5 = new UserBadge();
        $userBadge5->setUtilisateur($user3);
        $userBadge5->setBadge($badge5);
        $manager->persist($userBadge5);

        $userBadge6 = new UserBadge();
        $userBadge6->setUtilisateur($user4);
        $userBadge6->setBadge($badge6);
        $manager->persist($userBadge6);

        $userBadge7 = new UserBadge();
        $userBadge7->setUtilisateur($user5);
        $userBadge7->setBadge($badge7);
        $manager->persist($userBadge7);

        // Créer des relations Travailler
        $travaillerAdmin = new Travailler();
        $travaillerAdmin->setUtilisateur($admin);
        $travaillerAdmin->setService($service1);
        $travaillerAdmin->setDateDebut(new \DateTime('2020-01-01'));
        $manager->persist($travaillerAdmin);

        $travaillerManager1 = new Travailler();
        $travaillerManager1->setUtilisateur($manager1);
        $travaillerManager1->setService($service1);
        $travaillerManager1->setDateDebut(new \DateTime('2020-01-15'));
        $manager->persist($travaillerManager1);

        $travailler1 = new Travailler();
        $travailler1->setUtilisateur($user1);
        $travailler1->setService($service1);
        $travailler1->setDateDebut(new \DateTime('2021-03-15'));
        $manager->persist($travailler1);

        $travailler2 = new Travailler();
        $travailler2->setUtilisateur($user2);
        $travailler2->setService($service3);
        $travailler2->setDateDebut(new \DateTime('2021-06-01'));
        $manager->persist($travailler2);

        $travailler3 = new Travailler();
        $travailler3->setUtilisateur($user3);
        $travailler3->setService($service2);
        $travailler3->setDateDebut(new \DateTime('2021-08-01'));
        $manager->persist($travailler3);

        $travailler4 = new Travailler();
        $travailler4->setUtilisateur($user4);
        $travailler4->setService($service5);
        $travailler4->setDateDebut(new \DateTime('2022-01-10'));
        $manager->persist($travailler4);

        $travailler5 = new Travailler();
        $travailler5->setUtilisateur($user5);
        $travailler5->setService($service6);
        $travailler5->setDateDebut(new \DateTime('2022-03-01'));
        $manager->persist($travailler5);

        // Créer des relations ServiceZone (permissions d'accès par service)
        $serviceZone1 = new ServiceZone();
        $serviceZone1->setService($service1); // Service Informatique
        $serviceZone1->setZone($zone3); // Zone publique
        $manager->persist($serviceZone1);

        $serviceZone2 = new ServiceZone();
        $serviceZone2->setService($service1); // Service Informatique
        $serviceZone2->setZone($zone4); // Zone technique
        $manager->persist($serviceZone2);

        $serviceZone3 = new ServiceZone();
        $serviceZone3->setService($service2); // Service Sécurité
        $serviceZone3->setZone($zone1); // Zone sécurisée niveau 1
        $manager->persist($serviceZone3);

        $serviceZone4 = new ServiceZone();
        $serviceZone4->setService($service2); // Service Sécurité
        $serviceZone4->setZone($zone2); // Zone sécurisée niveau 2
        $manager->persist($serviceZone4);

        $serviceZone5 = new ServiceZone();
        $serviceZone5->setService($service3); // Service RH
        $serviceZone5->setZone($zone3); // Zone publique
        $manager->persist($serviceZone5);

        $serviceZone6 = new ServiceZone();
        $serviceZone6->setService($service1); // Service Informatique (accès direction)
        $serviceZone6->setZone($zone5); // Zone direction
        $manager->persist($serviceZone6);

        // Créer des relations Gerer (hiérarchie managériale)
        $gerer1 = new Gerer();
        $gerer1->setManageur($manager1); // François Dubois manage
        $gerer1->setEmploye($user1); // Jean Dupont
        $manager->persist($gerer1);

        $gerer2 = new Gerer();
        $gerer2->setManageur($admin); // Admin manage
        $gerer2->setEmploye($manager1); // François Dubois
        $manager->persist($gerer2);

        $gerer3 = new Gerer();
        $gerer3->setManageur($user2); // Marie Martin (RH) manage
        $gerer3->setEmploye($user3); // Pierre Durand
        $manager->persist($gerer3);

        $gerer4 = new Gerer();
        $gerer4->setManageur($user4); // Sophie Bernard manage
        $gerer4->setEmploye($user5); // Lucas Petit
        $manager->persist($gerer4);

        // Créer des pointages (historique d'utilisation des badges)
        $now = new \DateTime();
        $yesterday = (new \DateTime())->modify('-1 day');
        $twoDaysAgo = (new \DateTime())->modify('-2 days');

        // Pointages d'aujourd'hui
        $pointage1 = new Pointage();
        $pointage1->setBadge($badge1);
        $pointage1->setBadgeuse($badgeuse1);
        $pointage1->setHeure((new \DateTime())->setTime(8, 0));
        $pointage1->setType('entrée');
        $manager->persist($pointage1);

        $pointage2 = new Pointage();
        $pointage2->setBadge($badge2);
        $pointage2->setBadgeuse($badgeuse1);
        $pointage2->setHeure((new \DateTime())->setTime(7, 30));
        $pointage2->setType('entrée');
        $manager->persist($pointage2);

        $pointage3 = new Pointage();
        $pointage3->setBadge($badge3);
        $pointage3->setBadgeuse($badgeuse1);
        $pointage3->setHeure((new \DateTime())->setTime(9, 0));
        $pointage3->setType('entrée');
        $manager->persist($pointage3);

        $pointage4 = new Pointage();
        $pointage4->setBadge($badge1);
        $pointage4->setBadgeuse($badgeuse4);
        $pointage4->setHeure((new \DateTime())->setTime(10, 30));
        $pointage4->setType('accès_zone');
        $manager->persist($pointage4);

        $pointage5 = new Pointage();
        $pointage5->setBadge($badge5);
        $pointage5->setBadgeuse($badgeuse2);
        $pointage5->setHeure((new \DateTime())->setTime(22, 0));
        $pointage5->setType('entrée');
        $manager->persist($pointage5);

        // Pointages d'hier
        $pointage6 = new Pointage();
        $pointage6->setBadge($badge1);
        $pointage6->setBadgeuse($badgeuse1);
        $pointage6->setHeure($yesterday->setTime(8, 15));
        $pointage6->setType('entrée');
        $manager->persist($pointage6);

        $pointage7 = new Pointage();
        $pointage7->setBadge($badge1);
        $pointage7->setBadgeuse($badgeuse1);
        $pointage7->setHeure($yesterday->setTime(18, 30));
        $pointage7->setType('sortie');
        $manager->persist($pointage7);

        $pointage8 = new Pointage();
        $pointage8->setBadge($badge3);
        $pointage8->setBadgeuse($badgeuse1);
        $pointage8->setHeure($yesterday->setTime(9, 15));
        $pointage8->setType('entrée');
        $manager->persist($pointage8);

        $pointage9 = new Pointage();
        $pointage9->setBadge($badge3);
        $pointage9->setBadgeuse($badgeuse1);
        $pointage9->setHeure($yesterday->setTime(17, 45));
        $pointage9->setType('sortie');
        $manager->persist($pointage9);

        // Pointages d'il y a deux jours
        $pointage10 = new Pointage();
        $pointage10->setBadge($badge2);
        $pointage10->setBadgeuse($badgeuse1);
        $pointage10->setHeure($twoDaysAgo->setTime(7, 45));
        $pointage10->setType('entrée');
        $manager->persist($pointage10);

        $pointage11 = new Pointage();
        $pointage11->setBadge($badge2);
        $pointage11->setBadgeuse($badgeuse5);
        $pointage11->setHeure($twoDaysAgo->setTime(14, 30));
        $pointage11->setType('accès_zone');
        $manager->persist($pointage11);

        $pointage12 = new Pointage();
        $pointage12->setBadge($badge2);
        $pointage12->setBadgeuse($badgeuse1);
        $pointage12->setHeure($twoDaysAgo->setTime(19, 0));
        $pointage12->setType('sortie');
        $manager->persist($pointage12);

        $manager->flush();
    }
}
