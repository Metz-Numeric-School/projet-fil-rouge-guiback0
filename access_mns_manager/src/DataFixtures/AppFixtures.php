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
        // ========== ORGANISATIONS ==========
        $organisations = [];

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
        $organisations['defense'] = $organisation1;

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
        $organisations['interieur'] = $organisation2;

        $organisation3 = new Organisation();
        $organisation3->setNomOrganisation('Ministère de l\'Économie');
        $organisation3->setEmail('contact@economie.gouv.fr');
        $organisation3->setDateCreation(new \DateTime('2018-03-10'));
        $organisation3->setSiret('11223344556677');
        $organisation3->setTelephone('01.44.87.17.17');
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

        // Service principal pour chaque organisation
        $servicePrincipalDefense = new Service();
        $servicePrincipalDefense->setNomService('Service principal');
        $servicePrincipalDefense->setNiveauService(1);
        $servicePrincipalDefense->setIsPrincipal(true);
        $servicePrincipalDefense->setOrganisation($organisations['defense']);
        $manager->persist($servicePrincipalDefense);
        $services['principal_defense'] = $servicePrincipalDefense;

        $servicePrincipalInterieur = new Service();
        $servicePrincipalInterieur->setNomService('Service principal');
        $servicePrincipalInterieur->setNiveauService(1);
        $servicePrincipalInterieur->setIsPrincipal(true);
        $servicePrincipalInterieur->setOrganisation($organisations['interieur']);
        $manager->persist($servicePrincipalInterieur);
        $services['principal_interieur'] = $servicePrincipalInterieur;

        $servicePrincipalEconomie = new Service();
        $servicePrincipalEconomie->setNomService('Service principal');
        $servicePrincipalEconomie->setNiveauService(1);
        $servicePrincipalEconomie->setIsPrincipal(true);
        $servicePrincipalEconomie->setOrganisation($organisations['economie']);
        $manager->persist($servicePrincipalEconomie);
        $services['principal_economie'] = $servicePrincipalEconomie;

        // Services Ministère de la Défense
        $serviceIT = new Service();
        $serviceIT->setNomService('Service Informatique');
        $serviceIT->setNiveauService(2);
        $serviceIT->setOrganisation($organisations['defense']);
        $manager->persist($serviceIT);
        $services['it_defense'] = $serviceIT;

        $serviceSecurity = new Service();
        $serviceSecurity->setNomService('Service Sécurité');
        $serviceSecurity->setNiveauService(3);
        $serviceSecurity->setOrganisation($organisations['defense']);
        $manager->persist($serviceSecurity);
        $services['security_defense'] = $serviceSecurity;

        $serviceLogistics = new Service();
        $serviceLogistics->setNomService('Service Logistique');
        $serviceLogistics->setNiveauService(2);
        $serviceLogistics->setOrganisation($organisations['defense']);
        $manager->persist($serviceLogistics);
        $services['logistics_defense'] = $serviceLogistics;

        // Services Ministère de l'Intérieur
        $serviceRH = new Service();
        $serviceRH->setNomService('Service RH');
        $serviceRH->setNiveauService(1);
        $serviceRH->setOrganisation($organisations['interieur']);
        $manager->persist($serviceRH);
        $services['rh_interieur'] = $serviceRH;

        $servicePolice = new Service();
        $servicePolice->setNomService('Service Police Nationale');
        $servicePolice->setNiveauService(4);
        $servicePolice->setOrganisation($organisations['interieur']);
        $manager->persist($servicePolice);
        $services['police_interieur'] = $servicePolice;

        // Services Ministère de l'Économie
        $serviceFinance = new Service();
        $serviceFinance->setNomService('Service Finances Publiques');
        $serviceFinance->setNiveauService(2);
        $serviceFinance->setOrganisation($organisations['economie']);
        $manager->persist($serviceFinance);
        $services['finance_economie'] = $serviceFinance;

        // ========== ZONES ==========
        $zones = [];

        // Zone principale (partagée entre toutes les organisations)
        $zonePrincipale = new Zone();
        $zonePrincipale->setNomZone('Zone principale');
        $zonePrincipale->setDescription('Zone principale créée automatiquement');
        $zonePrincipale->setCapacite(100);
        $manager->persist($zonePrincipale);
        $zones['principale'] = $zonePrincipale;

        $zoneSA = new Zone();
        $zoneSA->setNomZone('Zone Sécurisée Alpha');
        $zoneSA->setDescription('Zone d\'accès ultra-restreint - Niveau Secret Défense');
        $zoneSA->setCapacite(25);
        $manager->persist($zoneSA);
        $zones['alpha'] = $zoneSA;

        $zoneSB = new Zone();
        $zoneSB->setNomZone('Zone Sécurisée Beta');
        $zoneSB->setDescription('Zone d\'accès restreint - Personnel autorisé uniquement');
        $zoneSB->setCapacite(50);
        $manager->persist($zoneSB);
        $zones['beta'] = $zoneSB;

        $zonePublic = new Zone();
        $zonePublic->setNomZone('Zone d\'Accueil Public');
        $zonePublic->setDescription('Zone d\'accès public - Hall d\'accueil');
        $zonePublic->setCapacite(200);
        $manager->persist($zonePublic);
        $zones['public'] = $zonePublic;

        $zoneBureau = new Zone();
        $zoneBureau->setNomZone('Zone Bureau');
        $zoneBureau->setDescription('Espaces de bureaux - Personnel permanent');
        $zoneBureau->setCapacite(150);
        $manager->persist($zoneBureau);
        $zones['bureau'] = $zoneBureau;

        $zoneTechnique = new Zone();
        $zoneTechnique->setNomZone('Zone Technique');
        $zoneTechnique->setDescription('Salles serveurs et installations techniques');
        $zoneTechnique->setCapacite(30);
        $manager->persist($zoneTechnique);
        $zones['technique'] = $zoneTechnique;

        // ========== UTILISATEURS ==========
        $users = [];

        // Super Admin
        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@access-mns.fr');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->hasher->hashPassword($superAdmin, 'SuperAdmin2024!'));
        $superAdmin->setNom('SYSTÈME');
        $superAdmin->setPrenom('Super Admin');
        $superAdmin->setTelephone('01.00.00.00.01');
        $superAdmin->setDateInscription(new \DateTime('2020-01-01'));
        $superAdmin->setDateDerniereConnexion(new \DateTime('-1 day'));
        $superAdmin->setCompteActif(true);
        $manager->persist($superAdmin);
        $users['superadmin'] = $superAdmin;

        // Admin Défense
        $adminDefense = new User();
        $adminDefense->setEmail('admin@defense.gouv.fr');
        $adminDefense->setRoles(['ROLE_ADMIN']);
        $adminDefense->setPassword($this->hasher->hashPassword($adminDefense, 'AdminDefense2024!'));
        $adminDefense->setNom('MARTIN');
        $adminDefense->setPrenom('Général Alexandre');
        $adminDefense->setTelephone('01.23.45.67.01');
        $adminDefense->setDateInscription(new \DateTime('2020-01-15'));
        $adminDefense->setCompteActif(true);
        $manager->persist($adminDefense);
        $users['admin_defense'] = $adminDefense;

        // Admin Intérieur
        $adminInterieur = new User();
        $adminInterieur->setEmail('admin@interieur.gouv.fr');
        $adminInterieur->setRoles(['ROLE_ADMIN']);
        $adminInterieur->setPassword($this->hasher->hashPassword($adminInterieur, 'AdminInterieur2024!'));
        $adminInterieur->setNom('BERNARD');
        $adminInterieur->setPrenom('Préfet Catherine');
        $adminInterieur->setTelephone('01.98.76.54.01');
        $adminInterieur->setDateInscription(new \DateTime('2020-02-01'));
        $adminInterieur->setCompteActif(true);
        $manager->persist($adminInterieur);
        $users['admin_interieur'] = $adminInterieur;

        // Utilisateurs réguliers - Défense
        $userDefense1 = new User();
        $userDefense1->setEmail('j.dupont@defense.gouv.fr');
        $userDefense1->setRoles(['ROLE_USER']);
        $userDefense1->setPassword($this->hasher->hashPassword($userDefense1, 'UserDefense123'));
        $userDefense1->setNom('DUPONT');
        $userDefense1->setPrenom('Jean-Michel');
        $userDefense1->setTelephone('01.23.45.67.11');
        $userDefense1->setDateInscription(new \DateTime('2021-03-15'));
        $userDefense1->setCompteActif(true);
        $manager->persist($userDefense1);
        $users['user_defense_1'] = $userDefense1;

        $userDefense2 = new User();
        $userDefense2->setEmail('s.rousseau@defense.gouv.fr');
        $userDefense2->setRoles(['ROLE_USER']);
        $userDefense2->setPassword($this->hasher->hashPassword($userDefense2, 'UserDefense123!'));
        $userDefense2->setNom('ROUSSEAU');
        $userDefense2->setPrenom('Sophie');
        $userDefense2->setTelephone('01.23.45.67.12');
        $userDefense2->setDateInscription(new \DateTime('2021-04-10'));
        $userDefense2->setCompteActif(true);
        $manager->persist($userDefense2);
        $users['user_defense_2'] = $userDefense2;

        // Utilisateurs réguliers - Intérieur
        $userInterieur1 = new User();
        $userInterieur1->setEmail('m.martin@interieur.gouv.fr');
        $userInterieur1->setRoles(['ROLE_USER']);
        $userInterieur1->setPassword($this->hasher->hashPassword($userInterieur1, 'UserInterieur123!'));
        $userInterieur1->setNom('MARTIN');
        $userInterieur1->setPrenom('Marie');
        $userInterieur1->setTelephone('01.98.76.54.11');
        $userInterieur1->setDateInscription(new \DateTime('2021-06-01'));
        $userInterieur1->setCompteActif(true);
        $manager->persist($userInterieur1);
        $users['user_interieur_1'] = $userInterieur1;

        $userInterieur2 = new User();
        $userInterieur2->setEmail('p.durand@interieur.gouv.fr');
        $userInterieur2->setRoles(['ROLE_USER']);
        $userInterieur2->setPassword($this->hasher->hashPassword($userInterieur2, 'UserInterieur123!'));
        $userInterieur2->setNom('DURAND');
        $userInterieur2->setPrenom('Pierre');
        $userInterieur2->setTelephone('01.98.76.54.12');
        $userInterieur2->setDateInscription(new \DateTime('2021-08-01'));
        $userInterieur2->setCompteActif(true);
        $manager->persist($userInterieur2);
        $users['user_interieur_2'] = $userInterieur2;

        // Utilisateurs réguliers - Économie
        $userEconomie1 = new User();
        $userEconomie1->setEmail('a.leroy@economie.gouv.fr');
        $userEconomie1->setRoles(['ROLE_USER']);
        $userEconomie1->setPassword($this->hasher->hashPassword($userEconomie1, 'UserEconomie123!'));
        $userEconomie1->setNom('LEROY');
        $userEconomie1->setPrenom('Antoine');
        $userEconomie1->setTelephone('01.44.87.17.11');
        $userEconomie1->setDateInscription(new \DateTime('2021-09-15'));
        $userEconomie1->setCompteActif(true);
        $manager->persist($userEconomie1);
        $users['user_economie_1'] = $userEconomie1;

        // Utilisateur désactivé pour test RGPD
        $userDeactivated = new User();
        $userDeactivated->setEmail('user.deactivated@test.gov.fr');
        $userDeactivated->setRoles(['ROLE_USER']);
        $userDeactivated->setPassword($this->hasher->hashPassword($userDeactivated, 'UserTest123!'));
        $userDeactivated->setNom('ANCIEN');
        $userDeactivated->setPrenom('Utilisateur');
        $userDeactivated->setTelephone('01.00.00.00.99');
        $userDeactivated->setDateInscription(new \DateTime('2019-01-01'));
        $userDeactivated->setDateDerniereConnexion(new \DateTime('2019-12-31'));
        $userDeactivated->deactivate(); // This sets compte_actif to false and date_suppression_prevue
        $manager->persist($userDeactivated);
        $users['user_deactivated'] = $userDeactivated;

        // Utilisateur de test avec mot de passe simple - Configuration complète pour tests pointage
        $userTest = new User();
        $userTest->setEmail('test@test.com');
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setPassword($this->hasher->hashPassword($userTest, 'test123'));
        $userTest->setNom('TEST');
        $userTest->setPrenom('User');
        $userTest->setTelephone('01.00.00.00.00');
        $userTest->setDateInscription(new \DateTime('2024-01-01'));
        // Configuration horaires de travail pour tests automatiques
        $userTest->setHeureDebut(\DateTime::createFromFormat('H:i', '08:30'));
        $userTest->setHorraire(\DateTime::createFromFormat('H:i', '08:00')); // 8h par jour
        $userTest->setJoursSemaineTravaille(5); // Lundi à vendredi
        $userTest->setPoste('Testeur Automatique');
        $userTest->setCompteActif(true);
        $manager->persist($userTest);
        $users['user_test'] = $userTest;

        // ========== BADGES ==========
        $badges = [];

        $badgeNumbers = [200001, 200002, 200003, 200004, 200005, 200006, 200007, 200008, 200009, 200010];
        $badgeTypes = ['administrateur', 'permanent', 'permanent', 'temporaire', 'visiteur', 'permanent', 'permanent', 'permanent', 'desactive', 'permanent'];
        $badgeUsers = ['superadmin', 'admin_defense', 'admin_interieur', 'user_defense_1', 'user_defense_2', 'user_interieur_1', 'user_interieur_2', 'user_economie_1', 'user_deactivated', 'user_test'];

        foreach ($badgeNumbers as $index => $number) {
            $badge = new Badge();
            $badge->setNumeroBadge($number);
            $badge->setTypeBadge($badgeTypes[$index]);
            $dateIndex = ($index + 1);
            $badge->setDateCreation(new \DateTime('2021-01-' . sprintf('%02d', $dateIndex)));

            if ($badgeTypes[$index] === 'temporaire') {
                $badge->setDateExpiration(new \DateTime('2025-12-31'));
            } elseif ($badgeTypes[$index] === 'visiteur') {
                $badge->setDateExpiration(new \DateTime('2024-06-30'));
            }

            $manager->persist($badge);
            $badges[$badgeUsers[$index]] = $badge;
        }

        // ========== BADGEUSES ==========
        $badgeuses = [];

        $badgeuseData = [
            ['ref' => 'BADGE-ALPHA-001', 'date' => '2020-01-01'],
            ['ref' => 'BADGE-BETA-001', 'date' => '2020-01-01'],
            ['ref' => 'BADGE-PUBLIC-001', 'date' => '2020-01-01'],
            ['ref' => 'BADGE-BUREAU-001', 'date' => '2020-01-15'],
            ['ref' => 'BADGE-TECH-001', 'date' => '2020-01-15'],
            ['ref' => 'BADGE-BETA-002', 'date' => '2020-02-01'],
        ];

        foreach ($badgeuseData as $index => $data) {
            $badgeuse = new Badgeuse();
            $badgeuse->setReference($data['ref']);
            $badgeuse->setDateInstallation(new \DateTime($data['date']));
            $manager->persist($badgeuse);
            $badgeuses['badgeuse_' . ($index + 1)] = $badgeuse;
        }

        // ========== ACCÈS ==========
        // Logique modifiée : 
        // - badgeuse_1 et badgeuse_2 : UNIQUEMENT zone principale (badgeuses d'entrée/sortie du bâtiment)
        // - badgeuse_3, 4, 5, 6 : UNIQUEMENT zones secondaires (accès aux zones spécifiques)
        $accesData = [
            // Badgeuses principales (entrée/sortie du bâtiment)
            ['badgeuse' => 'badgeuse_1', 'zone' => 'principale', 'nom' => 'Accès Principal - Entrée A'],
            ['badgeuse' => 'badgeuse_2', 'zone' => 'principale', 'nom' => 'Accès Principal - Entrée B'],
            
            // Badgeuses secondaires (zones spécifiques)
            ['badgeuse' => 'badgeuse_3', 'zone' => 'alpha', 'nom' => 'Accès Zone Alpha'],
            ['badgeuse' => 'badgeuse_4', 'zone' => 'beta', 'nom' => 'Accès Zone Beta'],
            ['badgeuse' => 'badgeuse_5', 'zone' => 'public', 'nom' => 'Accès Hall Public'],
            ['badgeuse' => 'badgeuse_6', 'zone' => 'bureau', 'nom' => 'Accès Bureau Direction'],
        ];

        foreach ($accesData as $data) {
            $acces = new Acces();
            $acces->setNomAcces($data['nom']);
            $acces->setDateInstallation(new \DateTime('2020-01-01'));
            $acces->setZone($zones[$data['zone']]);
            $acces->setBadgeuse($badgeuses[$data['badgeuse']]);
            $manager->persist($acces);
        }

        // ========== ACCÈS SIMPLIFIÉS ==========
        // Les utilisateurs accèdent aux badgeuses via leur service principal
        // Plus besoin d'accès utilisateur direct - tout passe par les ServiceZones

        // ========== USER BADGES ==========
        foreach ($badgeUsers as $userKey) {
            $userBadge = new UserBadge();
            $userBadge->setUtilisateur($users[$userKey]);
            $userBadge->setBadge($badges[$userKey]);
            $manager->persist($userBadge);
        }

        // ========== TRAVAILLER (User-Service relationships) ==========
        // Principal service assignments (mandatory)
        $principalTravaillerData = [
            ['user' => 'superadmin', 'service' => 'principal_defense', 'date' => '2020-01-01'],
            ['user' => 'admin_defense', 'service' => 'principal_defense', 'date' => '2020-01-15'],
            ['user' => 'admin_interieur', 'service' => 'principal_interieur', 'date' => '2020-02-01'],
            ['user' => 'user_defense_1', 'service' => 'principal_defense', 'date' => '2021-03-15'],
            ['user' => 'user_defense_2', 'service' => 'principal_defense', 'date' => '2021-04-10'],
            ['user' => 'user_interieur_1', 'service' => 'principal_interieur', 'date' => '2021-06-01'],
            ['user' => 'user_interieur_2', 'service' => 'principal_interieur', 'date' => '2021-08-01'],
            ['user' => 'user_economie_1', 'service' => 'principal_economie', 'date' => '2021-09-15'],
            ['user' => 'user_test', 'service' => 'principal_defense', 'date' => '2024-01-01'],
        ];

        foreach ($principalTravaillerData as $data) {
            $travailler = new Travailler();
            $travailler->setUtilisateur($users[$data['user']]);
            $travailler->setService($services[$data['service']]);
            $travailler->setDateDebut(new \DateTime($data['date']));
            $manager->persist($travailler);
        }

        // Secondary service assignments (optional)
        $secondaryTravaillerData = [
            ['user' => 'admin_defense', 'service' => 'security_defense', 'date' => '2020-01-15'],
            ['user' => 'user_defense_1', 'service' => 'it_defense', 'date' => '2021-03-15'],
            ['user' => 'user_defense_2', 'service' => 'logistics_defense', 'date' => '2021-04-10'],
            ['user' => 'user_interieur_1', 'service' => 'rh_interieur', 'date' => '2021-06-01'],
            ['user' => 'user_interieur_2', 'service' => 'police_interieur', 'date' => '2021-08-01'],
            ['user' => 'user_economie_1', 'service' => 'finance_economie', 'date' => '2021-09-15'],
        ];

        foreach ($secondaryTravaillerData as $data) {
            $travailler = new Travailler();
            $travailler->setUtilisateur($users[$data['user']]);
            $travailler->setService($services[$data['service']]);
            $travailler->setDateDebut(new \DateTime($data['date']));
            $manager->persist($travailler);
        }

        // ========== SERVICE ZONES ==========
        $serviceZoneData = [
            // Tous les services principaux ont accès à la zone principale (entrée/sortie du bâtiment)
            ['service' => 'principal_defense', 'zone' => 'principale'],
            ['service' => 'principal_interieur', 'zone' => 'principale'],
            ['service' => 'principal_economie', 'zone' => 'principale'],
            
            // Accès aux zones spécifiques selon les besoins métier
            ['service' => 'principal_defense', 'zone' => 'alpha'],    // Accès zone Alpha
            ['service' => 'principal_defense', 'zone' => 'beta'],     // Accès zone Beta
            ['service' => 'principal_defense', 'zone' => 'bureau'],   // Accès bureaux
            ['service' => 'principal_defense', 'zone' => 'public'],   // Accès hall public
            ['service' => 'principal_interieur', 'zone' => 'beta'],   // Accès zone Beta
            ['service' => 'principal_interieur', 'zone' => 'bureau'], // Accès bureaux
            ['service' => 'principal_interieur', 'zone' => 'public'], // Accès hall public
            ['service' => 'principal_economie', 'zone' => 'bureau'],  // Accès bureaux
            
            // Other service zones
            ['service' => 'security_defense', 'zone' => 'alpha'],
            ['service' => 'security_defense', 'zone' => 'beta'],
            ['service' => 'it_defense', 'zone' => 'technique'],
            ['service' => 'it_defense', 'zone' => 'bureau'],
            ['service' => 'logistics_defense', 'zone' => 'bureau'],
            ['service' => 'rh_interieur', 'zone' => 'bureau'],
            ['service' => 'police_interieur', 'zone' => 'beta'],
            ['service' => 'finance_economie', 'zone' => 'bureau'],
        ];

        foreach ($serviceZoneData as $data) {
            $serviceZone = new ServiceZone();
            $serviceZone->setService($services[$data['service']]);
            $serviceZone->setZone($zones[$data['zone']]);
            $manager->persist($serviceZone);
        }


        // ========== POINTAGES (Time tracking) ==========
        $today = new \DateTime();
        $yesterday = new \DateTime('-1 day');
        $lastWeek = new \DateTime('-7 days');

        // CORRECTION : Utilisation de nouvelles badgeuses avec logique principale/secondaire
        $pointageData = [
            // user_defense_1 : entrée par badgeuse principale puis zones secondaires
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'], // Entrée principale
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_4', 'heure' => $lastWeek->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'], // Zone beta
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_4', 'heure' => $lastWeek->format('Y-m-d') . ' 12:00:00', 'type' => 'sortie'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_4', 'heure' => $lastWeek->format('Y-m-d') . ' 13:30:00', 'type' => 'entree'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'], // Sortie principale

            // user_interieur_1 : entrée par badgeuse principale puis zones secondaires  
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_2', 'heure' => $lastWeek->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'], // Entrée principale
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_6', 'heure' => $lastWeek->format('Y-m-d') . ' 09:30:00', 'type' => 'entree'], // Zone bureau
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_2', 'heure' => $lastWeek->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'], // Sortie principale

            // user_defense_2 : entrée par badgeuse principale puis zones secondaires
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 08:15:00', 'type' => 'entree'], // Entrée principale
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_6', 'heure' => $lastWeek->format('Y-m-d') . ' 08:45:00', 'type' => 'entree'], // Zone bureau
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 18:00:00', 'type' => 'sortie'], // Sortie principale
        ];

        // POINTAGES ÉTENDUS pour user_test : Historique sur 30 jours avec nouvelle logique
        $testPointageData = [];
        for ($i = 2; $i < 31; $i++) { // Commencer à 2 jours pour éviter aujourd'hui ET hier
            $testDate = new \DateTime("-{$i} days");
            $dayOfWeek = $testDate->format('N'); // 1=Lundi, 7=Dimanche
            
            // Jours de travail : Lundi à Vendredi (1-5)
            if ($dayOfWeek <= 5) {
                // Logique modifiée : toujours entrer par badgeuse principale, puis utiliser zones secondaires
                $entryBadgeuse = ($i % 2 == 0) ? 'badgeuse_1' : 'badgeuse_2'; // Alternance entrées principales
                $workBadgeuse = ($i % 3 == 0) ? 'badgeuse_6' : 'badgeuse_5'; // Alternance zones bureau/public
                
                // Entrée dans le bâtiment (badgeuse principale)
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $entryBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'];
                // Accès zone de travail (badgeuse secondaire)
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'];
                // Pause déjeuner
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 12:00:00', 'type' => 'sortie'];
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 13:30:00', 'type' => 'entree'];
                // Sortie du bâtiment (badgeuse principale)
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $entryBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'];
            } 
            // Weekend : Pointages occasionnels
            else {
                // Samedi : demi-journée (seulement pour les 4 dernières semaines)
                if ($dayOfWeek == 6 && $i <= 28) {
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $testDate->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'];
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_5', 'heure' => $testDate->format('Y-m-d') . ' 09:30:00', 'type' => 'entree'];
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $testDate->format('Y-m-d') . ' 13:00:00', 'type' => 'sortie'];
                }
                // Dimanche : pas de pointage (repos)
            }
        }
        
        // AJOUT : Pointage incomplet d'hier pour tester le statut "au travail"
        $yesterday = new \DateTime('-1 day');
        if ($yesterday->format('N') <= 5) { // Si hier était un jour de semaine
            $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $yesterday->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'];
            // Pas de sortie pour simuler qu'il est encore au travail
        }
        
        // Fusion des données de pointage
        $pointageData = array_merge($pointageData, $testPointageData);

        foreach ($pointageData as $data) {
            $pointage = new Pointage();
            $pointage->setBadge($badges[$data['badge']]);
            $pointage->setBadgeuse($badgeuses[$data['badgeuse']]);
            $pointage->setHeure(new \DateTime($data['heure']));
            $pointage->setType($data['type']);
            $manager->persist($pointage);
        }

        $manager->flush();
    }
}
