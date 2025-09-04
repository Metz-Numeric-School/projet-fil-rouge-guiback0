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
use App\Entity\Pointage;
use App\Entity\ServiceZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        // ========== ORGANISATIONS ==========
        $organisations = [];

        $organisation1 = new Organisation();
        $organisation1->setNomOrganisation('Ministère de la Défense');
        $organisation1->setEmail('test@defense.gouv.fr');
        $organisation1->setDateCreation(new \DateTime('2020-01-01'));
        $organisation1->setSiret('12345678901234');
        $organisation1->setTelephone('0123456789');
        $organisation1->setSiteWeb('https://defense.gouv.fr');
        $organisation1->setPays('France');
        $organisation1->setNomRue('Rue Saint-Dominique');
        $organisation1->setNumeroRue(14);
        $organisation1->setCodePostal('75007');
        $organisation1->setVille('Paris');
        $manager->persist($organisation1);
        $organisations['defense'] = $organisation1;

        $organisation2 = new Organisation();
        $organisation2->setNomOrganisation('Ministère de l\'Intérieur');
        $organisation2->setEmail('test@interieur.gouv.fr');
        $organisation2->setDateCreation(new \DateTime('2020-01-01'));
        $organisation2->setSiret('98765432109876');
        $organisation2->setTelephone('0987654321');
        $organisation2->setSiteWeb('https://interieur.gouv.fr');
        $organisation2->setPays('France');
        $organisation2->setNomRue('Place Beauvau');
        $organisation2->setNumeroRue(1);
        $organisation2->setCodePostal('75008');
        $organisation2->setVille('Paris');
        $manager->persist($organisation2);
        $organisations['interieur'] = $organisation2;

        $organisation3 = new Organisation();
        $organisation3->setNomOrganisation('Ministère de l\'Économie');
        $organisation3->setEmail('test@economie.gouv.fr');
        $organisation3->setDateCreation(new \DateTime('2020-01-01'));
        $organisation3->setSiret('11223344556677');
        $organisation3->setTelephone('0144871717');
        $organisation3->setSiteWeb('https://economie.gouv.fr');
        $organisation3->setPays('France');
        $organisation3->setNomRue('Rue de Bercy');
        $organisation3->setNumeroRue(139);
        $organisation3->setCodePostal('75012');
        $organisation3->setVille('Paris');
        $manager->persist($organisation3);
        $organisations['economie'] = $organisation3;

        // ========== SERVICES ==========
        $services = [];

        // Services principaux
        $servicePrincipalDefense = new Service();
        $servicePrincipalDefense->setNomService('Direction Générale');
        $servicePrincipalDefense->setNiveauService(1);
        $servicePrincipalDefense->setIsPrincipal(true);
        $servicePrincipalDefense->setOrganisation($organisations['defense']);
        $manager->persist($servicePrincipalDefense);
        $services['principal_defense'] = $servicePrincipalDefense;

        $servicePrincipalInterieur = new Service();
        $servicePrincipalInterieur->setNomService('Direction Générale');
        $servicePrincipalInterieur->setNiveauService(1);
        $servicePrincipalInterieur->setIsPrincipal(true);
        $servicePrincipalInterieur->setOrganisation($organisations['interieur']);
        $manager->persist($servicePrincipalInterieur);
        $services['principal_interieur'] = $servicePrincipalInterieur;

        $servicePrincipalEconomie = new Service();
        $servicePrincipalEconomie->setNomService('Direction Générale');
        $servicePrincipalEconomie->setNiveauService(1);
        $servicePrincipalEconomie->setIsPrincipal(true);
        $servicePrincipalEconomie->setOrganisation($organisations['economie']);
        $manager->persist($servicePrincipalEconomie);
        $services['principal_economie'] = $servicePrincipalEconomie;

        // Services secondaires
        $serviceIT = new Service();
        $serviceIT->setNomService('Service IT');
        $serviceIT->setNiveauService(2);
        $serviceIT->setIsPrincipal(false);
        $serviceIT->setOrganisation($organisations['defense']);
        $manager->persist($serviceIT);
        $services['it'] = $serviceIT;

        $serviceSecurity = new Service();
        $serviceSecurity->setNomService('Service Sécurité');
        $serviceSecurity->setNiveauService(2);
        $serviceSecurity->setIsPrincipal(false);
        $serviceSecurity->setOrganisation($organisations['defense']);
        $manager->persist($serviceSecurity);
        $services['security'] = $serviceSecurity;

        $serviceLogistique = new Service();
        $serviceLogistique->setNomService('Service Logistique');
        $serviceLogistique->setNiveauService(3);
        $serviceLogistique->setIsPrincipal(false);
        $serviceLogistique->setOrganisation($organisations['defense']);
        $manager->persist($serviceLogistique);
        $services['logistique'] = $serviceLogistique;

        $serviceRH = new Service();
        $serviceRH->setNomService('Service RH');
        $serviceRH->setNiveauService(2);
        $serviceRH->setIsPrincipal(false);
        $serviceRH->setOrganisation($organisations['interieur']);
        $manager->persist($serviceRH);
        $services['rh'] = $serviceRH;

        $servicePolice = new Service();
        $servicePolice->setNomService('Service Police');
        $servicePolice->setNiveauService(3);
        $servicePolice->setIsPrincipal(false);
        $servicePolice->setOrganisation($organisations['interieur']);
        $manager->persist($servicePolice);
        $services['police'] = $servicePolice;

        $serviceFinance = new Service();
        $serviceFinance->setNomService('Service Finance');
        $serviceFinance->setNiveauService(2);
        $serviceFinance->setIsPrincipal(false);
        $serviceFinance->setOrganisation($organisations['economie']);
        $manager->persist($serviceFinance);
        $services['finance'] = $serviceFinance;

        // ========== ZONES ==========
        $zones = [];

        $zonePrincipale = new Zone();
        $zonePrincipale->setNomZone('Zone Principale');
        $zonePrincipale->setDescription('Zone principale d\'accès général');
        $zonePrincipale->setCapacite(200);
        $manager->persist($zonePrincipale);
        $zones['principale'] = $zonePrincipale;

        $zoneAlpha = new Zone();
        $zoneAlpha->setNomZone('Zone Alpha');
        $zoneAlpha->setDescription('Zone ultra-restreinte');
        $zoneAlpha->setCapacite(5);
        $manager->persist($zoneAlpha);
        $zones['alpha'] = $zoneAlpha;

        $zoneBeta = new Zone();
        $zoneBeta->setNomZone('Zone Beta');
        $zoneBeta->setDescription('Zone restreinte');
        $zoneBeta->setCapacite(25);
        $manager->persist($zoneBeta);
        $zones['beta'] = $zoneBeta;

        $zonePublic = new Zone();
        $zonePublic->setNomZone('Zone Public');
        $zonePublic->setDescription('Hall d\'accueil public');
        $zonePublic->setCapacite(100);
        $manager->persist($zonePublic);
        $zones['public'] = $zonePublic;

        $zoneBureau = new Zone();
        $zoneBureau->setNomZone('Zone Bureau');
        $zoneBureau->setDescription('Espaces de bureaux');
        $zoneBureau->setCapacite(50);
        $manager->persist($zoneBureau);
        $zones['bureau'] = $zoneBureau;

        $zoneTechnique = new Zone();
        $zoneTechnique->setNomZone('Zone Technique');
        $zoneTechnique->setDescription('Salles serveurs et installations techniques');
        $zoneTechnique->setCapacite(10);
        $manager->persist($zoneTechnique);
        $zones['technique'] = $zoneTechnique;

        // ========== UTILISATEURS ==========
        $users = [];

        // Super Admin
        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@test.com');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->hasher->hashPassword($superAdmin, 'SuperAdmin123!'));
        $superAdmin->setNom('ADMIN');
        $superAdmin->setPrenom('Super');
        $superAdmin->setTelephone('0100000001');
        $superAdmin->setDateInscription(new \DateTime('2020-01-01'));
        $superAdmin->setCompteActif(true);
        $manager->persist($superAdmin);
        $users['superadmin'] = $superAdmin;

        // Admin Défense
        $adminDefense = new User();
        $adminDefense->setEmail('admin@defense.test.com');
        $adminDefense->setRoles(['ROLE_ADMIN']);
        $adminDefense->setPassword($this->hasher->hashPassword($adminDefense, 'AdminDefense123!'));
        $adminDefense->setNom('MARTIN');
        $adminDefense->setPrenom('Jean');
        $adminDefense->setTelephone('0123456701');
        $adminDefense->setDateInscription(new \DateTime('2020-01-15'));
        $adminDefense->setCompteActif(true);
        $manager->persist($adminDefense);
        $users['admin_defense'] = $adminDefense;

        // Admin Intérieur
        $adminInterieur = new User();
        $adminInterieur->setEmail('admin@interieur.test.com');
        $adminInterieur->setRoles(['ROLE_ADMIN']);
        $adminInterieur->setPassword($this->hasher->hashPassword($adminInterieur, 'AdminInterieur123!'));
        $adminInterieur->setNom('BERNARD');
        $adminInterieur->setPrenom('Marie');
        $adminInterieur->setTelephone('0987654301');
        $adminInterieur->setDateInscription(new \DateTime('2020-02-01'));
        $adminInterieur->setCompteActif(true);
        $manager->persist($adminInterieur);
        $users['admin_interieur'] = $adminInterieur;

        // Utilisateurs réguliers - Défense
        $userDefense1 = new User();
        $userDefense1->setEmail('jean.dupont@defense.test.com');
        $userDefense1->setRoles(['ROLE_USER']);
        $userDefense1->setPassword($this->hasher->hashPassword($userDefense1, 'UserTest123!'));
        $userDefense1->setNom('DUPONT');
        $userDefense1->setPrenom('Jean');
        $userDefense1->setTelephone('0123456711');
        $userDefense1->setDateInscription(new \DateTime('2021-03-15'));
        $userDefense1->setCompteActif(true);
        $manager->persist($userDefense1);
        $users['user_defense_1'] = $userDefense1;

        $userDefense2 = new User();
        $userDefense2->setEmail('sophie.rousseau@defense.test.com');
        $userDefense2->setRoles(['ROLE_USER']);
        $userDefense2->setPassword($this->hasher->hashPassword($userDefense2, 'UserTest123!'));
        $userDefense2->setNom('ROUSSEAU');
        $userDefense2->setPrenom('Sophie');
        $userDefense2->setTelephone('0123456712');
        $userDefense2->setDateInscription(new \DateTime('2021-04-10'));
        $userDefense2->setCompteActif(true);
        $manager->persist($userDefense2);
        $users['user_defense_2'] = $userDefense2;

        // Utilisateurs réguliers - Intérieur
        $userInterieur1 = new User();
        $userInterieur1->setEmail('marie.martin@interieur.test.com');
        $userInterieur1->setRoles(['ROLE_USER']);
        $userInterieur1->setPassword($this->hasher->hashPassword($userInterieur1, 'UserTest123!'));
        $userInterieur1->setNom('MARTIN');
        $userInterieur1->setPrenom('Marie');
        $userInterieur1->setTelephone('0987654311');
        $userInterieur1->setDateInscription(new \DateTime('2021-06-01'));
        $userInterieur1->setCompteActif(true);
        $manager->persist($userInterieur1);
        $users['user_interieur_1'] = $userInterieur1;

        $userInterieur2 = new User();
        $userInterieur2->setEmail('pierre.durand@interieur.test.com');
        $userInterieur2->setRoles(['ROLE_USER']);
        $userInterieur2->setPassword($this->hasher->hashPassword($userInterieur2, 'UserTest123!'));
        $userInterieur2->setNom('DURAND');
        $userInterieur2->setPrenom('Pierre');
        $userInterieur2->setTelephone('0987654312');
        $userInterieur2->setDateInscription(new \DateTime('2021-08-01'));
        $userInterieur2->setCompteActif(true);
        $manager->persist($userInterieur2);
        $users['user_interieur_2'] = $userInterieur2;

        // Utilisateurs réguliers - Économie
        $userEconomie1 = new User();
        $userEconomie1->setEmail('antoine.leroy@economie.test.com');
        $userEconomie1->setRoles(['ROLE_USER']);
        $userEconomie1->setPassword($this->hasher->hashPassword($userEconomie1, 'UserTest123!'));
        $userEconomie1->setNom('LEROY');
        $userEconomie1->setPrenom('Antoine');
        $userEconomie1->setTelephone('0144871711');
        $userEconomie1->setDateInscription(new \DateTime('2021-09-15'));
        $userEconomie1->setCompteActif(true);
        $manager->persist($userEconomie1);
        $users['user_economie_1'] = $userEconomie1;

        // Utilisateur désactivé (RGPD)
        $userDeactivated = new User();
        $userDeactivated->setEmail('deactivated@test.com');
        $userDeactivated->setRoles(['ROLE_USER']);
        $userDeactivated->setPassword($this->hasher->hashPassword($userDeactivated, 'UserTest123!'));
        $userDeactivated->setNom('DESACTIVE');
        $userDeactivated->setPrenom('Utilisateur');
        $userDeactivated->setTelephone('0000000099');
        $userDeactivated->setDateInscription(new \DateTime('2019-01-01'));
        $userDeactivated->setDateDerniereConnexion(new \DateTime('2019-12-31'));
        $userDeactivated->deactivate();
        $manager->persist($userDeactivated);
        $users['user_deactivated'] = $userDeactivated;

        // Utilisateur de test avec configuration complète
        $userTest = new User();
        $userTest->setEmail('test@test.com');
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setPassword($this->hasher->hashPassword($userTest, 'test123'));
        $userTest->setNom('TEST');
        $userTest->setPrenom('User');
        $userTest->setTelephone('0000000000');
        $userTest->setDateInscription(new \DateTime('2024-01-01'));
        $userTest->setHeureDebut(\DateTime::createFromFormat('H:i', '08:30'));
        $userTest->setHorraire(\DateTime::createFromFormat('H:i', '08:00'));
        $userTest->setJoursSemaineTravaille(5);
        $userTest->setPoste('Testeur');
        $userTest->setCompteActif(true);
        $manager->persist($userTest);
        $users['user_test'] = $userTest;

        // ========== BADGES ==========
        $badges = [];

        $badgeData = [
            ['user' => 'superadmin', 'number' => 200001, 'type' => 'administrateur'],
            ['user' => 'admin_defense', 'number' => 200002, 'type' => 'administrateur'],
            ['user' => 'admin_interieur', 'number' => 200003, 'type' => 'administrateur'],
            ['user' => 'user_defense_1', 'number' => 200004, 'type' => 'permanent'],
            ['user' => 'user_defense_2', 'number' => 200005, 'type' => 'permanent'],
            ['user' => 'user_interieur_1', 'number' => 200006, 'type' => 'permanent'],
            ['user' => 'user_interieur_2', 'number' => 200007, 'type' => 'permanent'],
            ['user' => 'user_economie_1', 'number' => 200008, 'type' => 'permanent'],
            ['user' => 'user_deactivated', 'number' => 200009, 'type' => 'desactive'],
            ['user' => 'user_test', 'number' => 200010, 'type' => 'permanent'],
        ];

        foreach ($badgeData as $data) {
            $badge = new Badge();
            $badge->setNumeroBadge($data['number']);
            $badge->setTypeBadge($data['type']);
            $badge->setDateCreation(new \DateTime('2021-01-01'));

            if ($data['type'] === 'temporaire') {
                $badge->setDateExpiration(new \DateTime('2025-12-31'));
            } elseif ($data['type'] === 'visiteur') {
                $badge->setDateExpiration(new \DateTime('2024-12-31'));
            }

            $manager->persist($badge);
            $badges[$data['user']] = $badge;
        }

        // ========== BADGEUSES ==========
        $badgeuses = [];

        $badgeuseData = [
            ['ref' => 'BADGE-ALPHA-001', 'key' => 'alpha_1'],
            ['ref' => 'BADGE-ALPHA-002', 'key' => 'alpha_2'],
            ['ref' => 'BADGE-BETA-001', 'key' => 'beta_1'],
            ['ref' => 'BADGE-BETA-002', 'key' => 'beta_2'],
            ['ref' => 'BADGE-PUBLIC-001', 'key' => 'public_1'],
            ['ref' => 'BADGE-BUREAU-001', 'key' => 'bureau_1'],
            ['ref' => 'BADGE-TECHNIQUE-001', 'key' => 'technique_1'],
            ['ref' => 'BADGE-PRINCIPALE-001', 'key' => 'principale_1'],
        ];

        foreach ($badgeuseData as $data) {
            $badgeuse = new Badgeuse();
            $badgeuse->setReference($data['ref']);
            $badgeuse->setDateInstallation(new \DateTime('2020-01-01'));
            $manager->persist($badgeuse);
            $badgeuses[$data['key']] = $badgeuse;
        }

        // ========== ACCÈS ==========
        $accesData = [
            ['badgeuse' => 'principale_1', 'zone' => 'principale', 'nom' => 'Accès Principal'],
            ['badgeuse' => 'alpha_1', 'zone' => 'alpha', 'nom' => 'Accès Zone Alpha 1'],
            ['badgeuse' => 'alpha_2', 'zone' => 'alpha', 'nom' => 'Accès Zone Alpha 2'],
            ['badgeuse' => 'beta_1', 'zone' => 'beta', 'nom' => 'Accès Zone Beta 1'],
            ['badgeuse' => 'beta_2', 'zone' => 'beta', 'nom' => 'Accès Zone Beta 2'],
            ['badgeuse' => 'public_1', 'zone' => 'public', 'nom' => 'Accès Hall Public'],
            ['badgeuse' => 'bureau_1', 'zone' => 'bureau', 'nom' => 'Accès Bureau'],
            ['badgeuse' => 'technique_1', 'zone' => 'technique', 'nom' => 'Accès Zone Technique'],
        ];

        foreach ($accesData as $data) {
            $acces = new Acces();
            $acces->setNomAcces($data['nom']);
            $acces->setDateInstallation(new \DateTime('2020-01-01 10:00:00'));
            $acces->setZone($zones[$data['zone']]);
            $acces->setBadgeuse($badgeuses[$data['badgeuse']]);
            $manager->persist($acces);
        }

        // ========== USER BADGES ==========
        foreach ($badgeData as $data) {
            $userBadge = new UserBadge();
            $userBadge->setUtilisateur($users[$data['user']]);
            $userBadge->setBadge($badges[$data['user']]);
            $manager->persist($userBadge);
        }

        // ========== TRAVAILLER ==========
        $travaillerData = [
            // Principal services (mandatory)
            ['user' => 'superadmin', 'service' => 'principal_defense', 'date' => '2020-01-01'],
            ['user' => 'admin_defense', 'service' => 'principal_defense', 'date' => '2020-01-15'],
            ['user' => 'admin_interieur', 'service' => 'principal_interieur', 'date' => '2020-02-01'],
            ['user' => 'user_defense_1', 'service' => 'principal_defense', 'date' => '2021-03-15'],
            ['user' => 'user_defense_2', 'service' => 'principal_defense', 'date' => '2021-04-10'],
            ['user' => 'user_interieur_1', 'service' => 'principal_interieur', 'date' => '2021-06-01'],
            ['user' => 'user_interieur_2', 'service' => 'principal_interieur', 'date' => '2021-08-01'],
            ['user' => 'user_economie_1', 'service' => 'principal_economie', 'date' => '2021-09-15'],
            ['user' => 'user_test', 'service' => 'principal_defense', 'date' => '2024-01-01'],

            // Secondary services (optional)
            ['user' => 'admin_defense', 'service' => 'security', 'date' => '2020-01-15'],
            ['user' => 'user_defense_1', 'service' => 'it', 'date' => '2021-03-15'],
            ['user' => 'user_defense_2', 'service' => 'logistique', 'date' => '2021-04-10'],
            ['user' => 'user_interieur_1', 'service' => 'rh', 'date' => '2021-06-01'],
            ['user' => 'user_interieur_2', 'service' => 'police', 'date' => '2021-08-01'],
            ['user' => 'user_economie_1', 'service' => 'finance', 'date' => '2021-09-15'],
        ];

        foreach ($travaillerData as $data) {
            $travailler = new Travailler();
            $travailler->setUtilisateur($users[$data['user']]);
            $travailler->setService($services[$data['service']]);
            $travailler->setDateDebut(new \DateTime($data['date']));
            $manager->persist($travailler);
        }

        // ========== SERVICE ZONES ==========
        $serviceZoneData = [
            // Principal services access to main zone
            ['service' => 'principal_defense', 'zone' => 'principale'],
            ['service' => 'principal_interieur', 'zone' => 'principale'],
            ['service' => 'principal_economie', 'zone' => 'principale'],

            // Specific zone access
            ['service' => 'principal_defense', 'zone' => 'alpha'],
            ['service' => 'principal_defense', 'zone' => 'beta'],
            ['service' => 'principal_defense', 'zone' => 'bureau'],
            ['service' => 'principal_defense', 'zone' => 'public'],
            ['service' => 'principal_interieur', 'zone' => 'beta'],
            ['service' => 'principal_interieur', 'zone' => 'bureau'],
            ['service' => 'principal_interieur', 'zone' => 'public'],
            ['service' => 'principal_economie', 'zone' => 'bureau'],
            ['service' => 'principal_economie', 'zone' => 'public'],

            // Secondary services
            ['service' => 'security', 'zone' => 'alpha'],
            ['service' => 'security', 'zone' => 'beta'],
            ['service' => 'it', 'zone' => 'technique'],
            ['service' => 'it', 'zone' => 'bureau'],
            ['service' => 'logistique', 'zone' => 'bureau'],
            ['service' => 'rh', 'zone' => 'bureau'],
            ['service' => 'police', 'zone' => 'beta'],
            ['service' => 'finance', 'zone' => 'bureau'],
        ];

        foreach ($serviceZoneData as $data) {
            $serviceZone = new ServiceZone();
            $serviceZone->setService($services[$data['service']]);
            $serviceZone->setZone($zones[$data['zone']]);
            $manager->persist($serviceZone);
        }


        // ========== POINTAGES ==========
        $pointageData = [];

        // Create historical pointages for the last 30 days
        for ($i = 1; $i <= 30; $i++) {
            $date = new \DateTime("-{$i} days");
            $dayOfWeek = $date->format('N'); // 1=Monday, 7=Sunday

            // Working days: Monday to Friday
            if ($dayOfWeek <= 5) {
                // Morning entry
                $pointageData[] = [
                    'user' => 'user_test',
                    'badgeuse' => 'principale_1',
                    'heure' => $date->format('Y-m-d') . ' 08:30:00',
                    'type' => 'entree'
                ];

                // Access to work zone
                $pointageData[] = [
                    'user' => 'user_test',
                    'badgeuse' => 'bureau_1',
                    'heure' => $date->format('Y-m-d') . ' 09:00:00',
                    'type' => 'entree'
                ];

                // Lunch break
                $pointageData[] = [
                    'user' => 'user_test',
                    'badgeuse' => 'bureau_1',
                    'heure' => $date->format('Y-m-d') . ' 12:00:00',
                    'type' => 'sortie'
                ];

                $pointageData[] = [
                    'user' => 'user_test',
                    'badgeuse' => 'bureau_1',
                    'heure' => $date->format('Y-m-d') . ' 13:30:00',
                    'type' => 'entree'
                ];

                // End of day
                $pointageData[] = [
                    'user' => 'user_test',
                    'badgeuse' => 'principale_1',
                    'heure' => $date->format('Y-m-d') . ' 17:30:00',
                    'type' => 'sortie'
                ];
            }
        }

        // Additional pointages for other users
        $additionalPointageData = [
            ['user' => 'user_defense_1', 'badgeuse' => 'principale_1', 'heure' => '-1 day 08:30:00', 'type' => 'entree'],
            ['user' => 'user_defense_1', 'badgeuse' => 'beta_1', 'heure' => '-1 day 09:00:00', 'type' => 'entree'],
            ['user' => 'user_defense_1', 'badgeuse' => 'principale_1', 'heure' => '-1 day 17:30:00', 'type' => 'sortie'],

            ['user' => 'user_interieur_1', 'badgeuse' => 'principale_1', 'heure' => '-2 days 09:00:00', 'type' => 'entree'],
            ['user' => 'user_interieur_1', 'badgeuse' => 'bureau_1', 'heure' => '-2 days 09:30:00', 'type' => 'entree'],
            ['user' => 'user_interieur_1', 'badgeuse' => 'principale_1', 'heure' => '-2 days 17:30:00', 'type' => 'sortie'],
        ];

        $pointageData = array_merge($pointageData, $additionalPointageData);

        foreach ($pointageData as $data) {
            $pointage = new Pointage();
            $pointage->setBadge($badges[$data['user']]);
            $pointage->setBadgeuse($badgeuses[$data['badgeuse']]);
            $pointage->setHeure(new \DateTime($data['heure']));
            $pointage->setType($data['type']);
            $manager->persist($pointage);
        }

        $manager->flush();
    }
}
