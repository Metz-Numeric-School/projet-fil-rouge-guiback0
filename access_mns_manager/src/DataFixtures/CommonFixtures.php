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

class CommonFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function getGroups(): array
    {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        // Sécurité : Ne pas charger ces fixtures en production
        if ($_ENV['APP_ENV'] === 'prod') {
            return;
        }

        // ========== ORGANISATIONS ==========
        $organisations = [];

        $organisation1 = new Organisation();
        $organisation1->setNomOrganisation('Ministère de la Défense');
        $organisation1->setEmail('contact@defense.gouv.fr');
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
        $organisation2->setEmail('contact@interieur.gouv.fr');
        $organisation2->setDateCreation(new \DateTime('2019-06-15'));
        $organisation2->setSiret('98765432109876');
        $organisation2->setTelephone('0198765432');
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

        // Services principaux pour chaque organisation
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

        // Services secondaires - Ministère de la Défense
        $serviceIT = new Service();
        $serviceIT->setNomService('Service Informatique');
        $serviceIT->setNiveauService(2);
        $serviceIT->setIsPrincipal(false);
        $serviceIT->setOrganisation($organisations['defense']);
        $manager->persist($serviceIT);
        $services['it_defense'] = $serviceIT;

        $serviceSecurity = new Service();
        $serviceSecurity->setNomService('Service Sécurité');
        $serviceSecurity->setNiveauService(3);
        $serviceSecurity->setIsPrincipal(false);
        $serviceSecurity->setOrganisation($organisations['defense']);
        $manager->persist($serviceSecurity);
        $services['security_defense'] = $serviceSecurity;

        $serviceLogistics = new Service();
        $serviceLogistics->setNomService('Service Logistique');
        $serviceLogistics->setNiveauService(2);
        $serviceLogistics->setIsPrincipal(false);
        $serviceLogistics->setOrganisation($organisations['defense']);
        $manager->persist($serviceLogistics);
        $services['logistics_defense'] = $serviceLogistics;

        // Services secondaires - Ministère de l'Intérieur
        $serviceRH = new Service();
        $serviceRH->setNomService('Service RH');
        $serviceRH->setNiveauService(1);
        $serviceRH->setIsPrincipal(false);
        $serviceRH->setOrganisation($organisations['interieur']);
        $manager->persist($serviceRH);
        $services['rh_interieur'] = $serviceRH;

        $servicePolice = new Service();
        $servicePolice->setNomService('Service Police Nationale');
        $servicePolice->setNiveauService(4);
        $servicePolice->setIsPrincipal(false);
        $servicePolice->setOrganisation($organisations['interieur']);
        $manager->persist($servicePolice);
        $services['police_interieur'] = $servicePolice;

        // Services secondaires - Ministère de l'Économie
        $serviceFinance = new Service();
        $serviceFinance->setNomService('Service Finances Publiques');
        $serviceFinance->setNiveauService(2);
        $serviceFinance->setIsPrincipal(false);
        $serviceFinance->setOrganisation($organisations['economie']);
        $manager->persist($serviceFinance);
        $services['finance_economie'] = $serviceFinance;

        // ========== ZONES ==========
        $zones = [];

        // Zone principale commune (entrée/sortie bâtiment)
        $zonePrincipale = new Zone();
        $zonePrincipale->setNomZone('Zone Principale - Entrée/Sortie');
        $zonePrincipale->setDescription('Zone d\'entrée et sortie commune à tous les services');
        $zonePrincipale->setCapacite(100);
        $manager->persist($zonePrincipale);
        $zones['principale'] = $zonePrincipale;

        // Zones spécifiques par organisation/service - DÉFENSE
        $zoneDefenseAlpha = new Zone();
        $zoneDefenseAlpha->setNomZone('Zone Défense Alpha');
        $zoneDefenseAlpha->setDescription('Zone sécurisée niveau Alpha - Ministère Défense');
        $zoneDefenseAlpha->setCapacite(20);
        $manager->persist($zoneDefenseAlpha);
        $zones['defense_alpha'] = $zoneDefenseAlpha;

        $zoneDefenseBeta = new Zone();
        $zoneDefenseBeta->setNomZone('Zone Défense Beta');
        $zoneDefenseBeta->setDescription('Zone sécurisée niveau Beta - Ministère Défense');
        $zoneDefenseBeta->setCapacite(30);
        $manager->persist($zoneDefenseBeta);
        $zones['defense_beta'] = $zoneDefenseBeta;

        $zoneDefenseBureau = new Zone();
        $zoneDefenseBureau->setNomZone('Zone Bureaux Défense');
        $zoneDefenseBureau->setDescription('Bureaux du Ministère de la Défense');
        $zoneDefenseBureau->setCapacite(50);
        $manager->persist($zoneDefenseBureau);
        $zones['defense_bureau'] = $zoneDefenseBureau;

        $zoneDefenseIT = new Zone();
        $zoneDefenseIT->setNomZone('Zone IT Défense');
        $zoneDefenseIT->setDescription('Zone informatique - Service IT Défense');
        $zoneDefenseIT->setCapacite(15);
        $manager->persist($zoneDefenseIT);
        $zones['defense_it'] = $zoneDefenseIT;

        $zoneDefenseSecurity = new Zone();
        $zoneDefenseSecurity->setNomZone('Zone Sécurité Défense');
        $zoneDefenseSecurity->setDescription('Zone service sécurité - Défense');
        $zoneDefenseSecurity->setCapacite(20);
        $manager->persist($zoneDefenseSecurity);
        $zones['defense_security'] = $zoneDefenseSecurity;

        $zoneDefenseLogistics = new Zone();
        $zoneDefenseLogistics->setNomZone('Zone Logistique Défense');
        $zoneDefenseLogistics->setDescription('Zone service logistique - Défense');
        $zoneDefenseLogistics->setCapacite(25);
        $manager->persist($zoneDefenseLogistics);
        $zones['defense_logistics'] = $zoneDefenseLogistics;

        // Zones spécifiques par organisation/service - INTÉRIEUR
        $zoneInterieurBeta = new Zone();
        $zoneInterieurBeta->setNomZone('Zone Intérieur Beta');
        $zoneInterieurBeta->setDescription('Zone sécurisée Beta - Ministère Intérieur');
        $zoneInterieurBeta->setCapacite(40);
        $manager->persist($zoneInterieurBeta);
        $zones['interieur_beta'] = $zoneInterieurBeta;

        $zoneInterieurBureau = new Zone();
        $zoneInterieurBureau->setNomZone('Zone Bureaux Intérieur');
        $zoneInterieurBureau->setDescription('Bureaux du Ministère de l\'Intérieur');
        $zoneInterieurBureau->setCapacite(60);
        $manager->persist($zoneInterieurBureau);
        $zones['interieur_bureau'] = $zoneInterieurBureau;

        $zoneInterieurRH = new Zone();
        $zoneInterieurRH->setNomZone('Zone RH Intérieur');
        $zoneInterieurRH->setDescription('Zone service RH - Intérieur');
        $zoneInterieurRH->setCapacite(20);
        $manager->persist($zoneInterieurRH);
        $zones['interieur_rh'] = $zoneInterieurRH;

        $zoneInterieurPolice = new Zone();
        $zoneInterieurPolice->setNomZone('Zone Police Nationale');
        $zoneInterieurPolice->setDescription('Zone service Police Nationale');
        $zoneInterieurPolice->setCapacite(35);
        $manager->persist($zoneInterieurPolice);
        $zones['interieur_police'] = $zoneInterieurPolice;

        // Zones spécifiques par organisation/service - ÉCONOMIE
        $zoneEconomieBureau = new Zone();
        $zoneEconomieBureau->setNomZone('Zone Bureaux Économie');
        $zoneEconomieBureau->setDescription('Bureaux du Ministère de l\'Économie');
        $zoneEconomieBureau->setCapacite(40);
        $manager->persist($zoneEconomieBureau);
        $zones['economie_bureau'] = $zoneEconomieBureau;

        $zoneEconomieFinance = new Zone();
        $zoneEconomieFinance->setNomZone('Zone Finances Publiques');
        $zoneEconomieFinance->setDescription('Zone service Finances Publiques');
        $zoneEconomieFinance->setCapacite(30);
        $manager->persist($zoneEconomieFinance);
        $zones['economie_finance'] = $zoneEconomieFinance;

        // Zone publique commune
        $zonePublic = new Zone();
        $zonePublic->setNomZone('Zone Accueil Public');
        $zonePublic->setDescription('Hall d\'accueil public inter-ministériel');
        $zonePublic->setCapacite(200);
        $manager->persist($zonePublic);
        $zones['public'] = $zonePublic;

        // ========== UTILISATEURS ==========
        $users = [];

        // Super Admin
        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@access-mns.fr');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->hasher->hashPassword($superAdmin, 'SuperAdmin2024!'));
        $superAdmin->setNom('SYSTÈME');
        $superAdmin->setPrenom('SuperAdmin');
        $superAdmin->setTelephone('0100000001');
        $superAdmin->setDateInscription(new \DateTime('2020-01-01'));
        $superAdmin->setDateDerniereConnexion(new \DateTime('-1 day'));
        $superAdmin->setDateNaissance(new \DateTime('1980-01-01'));
        $superAdmin->setCompteActif(true);
        $manager->persist($superAdmin);
        $users['superadmin'] = $superAdmin;

        // Admin Défense
        $adminDefense = new User();
        $adminDefense->setEmail('admin@defense.gouv.fr');
        $adminDefense->setRoles(['ROLE_ADMIN']);
        $adminDefense->setPassword($this->hasher->hashPassword($adminDefense, 'AdminDefense2024!'));
        $adminDefense->setNom('Martin');
        $adminDefense->setPrenom('Alexandre');
        $adminDefense->setTelephone('0123456701');
        $adminDefense->setDateInscription(new \DateTime('2020-01-15'));
        $adminDefense->setDateNaissance(new \DateTime('1975-03-15'));
        $adminDefense->setCompteActif(true);
        $manager->persist($adminDefense);
        $users['admin_defense'] = $adminDefense;

        // Admin Intérieur
        $adminInterieur = new User();
        $adminInterieur->setEmail('admin@interieur.gouv.fr');
        $adminInterieur->setRoles(['ROLE_ADMIN']);
        $adminInterieur->setPassword($this->hasher->hashPassword($adminInterieur, 'AdminInterieur2024!'));
        $adminInterieur->setNom('Bernard');
        $adminInterieur->setPrenom('Catherine');
        $adminInterieur->setTelephone('0198765401');
        $adminInterieur->setDateInscription(new \DateTime('2020-02-01'));
        $adminInterieur->setDateNaissance(new \DateTime('1970-08-22'));
        $adminInterieur->setCompteActif(true);
        $manager->persist($adminInterieur);
        $users['admin_interieur'] = $adminInterieur;

        // Utilisateurs réguliers - Défense
        $userDefense1 = new User();
        $userDefense1->setEmail('j.dupont@defense.gouv.fr');
        $userDefense1->setRoles(['ROLE_USER']);
        $userDefense1->setPassword($this->hasher->hashPassword($userDefense1, 'UserDefense123!'));
        $userDefense1->setNom('Dupont');
        $userDefense1->setPrenom('Jean-Michel');
        $userDefense1->setTelephone('0123456711');
        $userDefense1->setDateInscription(new \DateTime('2021-03-15'));
        $userDefense1->setDateNaissance(new \DateTime('1985-06-10'));
        $userDefense1->setCompteActif(true);
        $manager->persist($userDefense1);
        $users['user_defense_1'] = $userDefense1;

        $userDefense2 = new User();
        $userDefense2->setEmail('s.rousseau@defense.gouv.fr');
        $userDefense2->setRoles(['ROLE_USER']);
        $userDefense2->setPassword($this->hasher->hashPassword($userDefense2, 'UserDefense123!'));
        $userDefense2->setNom('Rousseau');
        $userDefense2->setPrenom('Sophie');
        $userDefense2->setTelephone('0123456712');
        $userDefense2->setDateInscription(new \DateTime('2021-04-10'));
        $userDefense2->setDateNaissance(new \DateTime('1990-12-03'));
        $userDefense2->setCompteActif(true);
        $manager->persist($userDefense2);
        $users['user_defense_2'] = $userDefense2;

        // Utilisateurs réguliers - Intérieur
        $userInterieur1 = new User();
        $userInterieur1->setEmail('m.martin@interieur.gouv.fr');
        $userInterieur1->setRoles(['ROLE_USER']);
        $userInterieur1->setPassword($this->hasher->hashPassword($userInterieur1, 'UserInterieur123!'));
        $userInterieur1->setNom('Martin');
        $userInterieur1->setPrenom('Marie');
        $userInterieur1->setTelephone('0198765411');
        $userInterieur1->setDateInscription(new \DateTime('2021-06-01'));
        $userInterieur1->setDateNaissance(new \DateTime('1982-04-18'));
        $userInterieur1->setCompteActif(true);
        $manager->persist($userInterieur1);
        $users['user_interieur_1'] = $userInterieur1;

        $userInterieur2 = new User();
        $userInterieur2->setEmail('p.durand@interieur.gouv.fr');
        $userInterieur2->setRoles(['ROLE_USER']);
        $userInterieur2->setPassword($this->hasher->hashPassword($userInterieur2, 'UserInterieur123!'));
        $userInterieur2->setNom('Durand');
        $userInterieur2->setPrenom('Pierre');
        $userInterieur2->setTelephone('0198765412');
        $userInterieur2->setDateInscription(new \DateTime('2021-08-01'));
        $userInterieur2->setDateNaissance(new \DateTime('1987-11-25'));
        $userInterieur2->setCompteActif(true);
        $manager->persist($userInterieur2);
        $users['user_interieur_2'] = $userInterieur2;

        // Utilisateurs réguliers - Économie
        $userEconomie1 = new User();
        $userEconomie1->setEmail('a.leroy@economie.gouv.fr');
        $userEconomie1->setRoles(['ROLE_USER']);
        $userEconomie1->setPassword($this->hasher->hashPassword($userEconomie1, 'UserEconomie123!'));
        $userEconomie1->setNom('Leroy');
        $userEconomie1->setPrenom('Antoine');
        $userEconomie1->setTelephone('0144871711');
        $userEconomie1->setDateInscription(new \DateTime('2021-09-15'));
        $userEconomie1->setDateNaissance(new \DateTime('1988-07-09'));
        $userEconomie1->setCompteActif(true);
        $manager->persist($userEconomie1);
        $users['user_economie_1'] = $userEconomie1;

        // Utilisateur désactivé pour test RGPD
        $userDeactivated = new User();
        $userDeactivated->setEmail('user.deactivated@test.gov.fr');
        $userDeactivated->setRoles(['ROLE_USER']);
        $userDeactivated->setPassword($this->hasher->hashPassword($userDeactivated, 'UserTest123!'));
        $userDeactivated->setNom('Ancien');
        $userDeactivated->setPrenom('Utilisateur');
        $userDeactivated->setTelephone('0100000099');
        $userDeactivated->setDateInscription(new \DateTime('2019-01-01'));
        $userDeactivated->setDateDerniereConnexion(new \DateTime('2019-12-31'));
        $userDeactivated->setDateNaissance(new \DateTime('1980-01-01'));
        $userDeactivated->deactivate();
        $manager->persist($userDeactivated);
        $users['user_deactivated'] = $userDeactivated;

        // Utilisateur de test avec mot de passe simple - Configuration complète pour tests pointage
        $userTest = new User();
        $userTest->setEmail('test@example.com');
        $userTest->setRoles(['ROLE_USER']);
        $userTest->setPassword($this->hasher->hashPassword($userTest, 'TestUser123!'));
        $userTest->setNom('Test');
        $userTest->setPrenom('User');
        $userTest->setTelephone('0100000000');
        $userTest->setDateInscription(new \DateTime('2024-01-01'));
        $userTest->setDateNaissance(new \DateTime('1995-01-01'));
        $userTest->setHeureDebut(\DateTime::createFromFormat('H:i', '08:30'));
        $userTest->setHorraire(\DateTime::createFromFormat('H:i', '08:00'));
        $userTest->setJoursSemaineTravaille(5);
        $userTest->setPoste('Testeur Automatique');
        $userTest->setCompteActif(true);
        $manager->persist($userTest);
        $users['user_test'] = $userTest;

        // ========== BADGES ==========
        $badges = [];

        $badgeNumbers = [200001, 200002, 200003, 200004, 200005, 200006, 200007, 200008, 200009, 200010];
        $badgeTypes = ['administrateur', 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'desactive', 'permanent'];
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

        // Badgeuses communes (zone principale partagée)
        $badgeuseCommune = [
            ['ref' => 'BADGE-PRINCIPAL-001', 'date' => '2020-01-01'],
            ['ref' => 'BADGE-PRINCIPAL-002', 'date' => '2020-01-01'],
            ['ref' => 'BADGE-PUBLIC-001', 'date' => '2020-01-01'],
        ];

        // Badgeuses spécifiques par organisation - Défense
        $badgeuseDefense = [
            ['ref' => 'BADGE-DEFENSE-ALPHA-001', 'date' => '2020-01-15'],
            ['ref' => 'BADGE-DEFENSE-BETA-001', 'date' => '2020-01-15'],
            ['ref' => 'BADGE-DEFENSE-BUREAU-001', 'date' => '2020-01-15'],
            ['ref' => 'BADGE-DEFENSE-IT-001', 'date' => '2020-03-01'],
            ['ref' => 'BADGE-DEFENSE-SECURITY-001', 'date' => '2020-03-01'],
            ['ref' => 'BADGE-DEFENSE-LOGISTICS-001', 'date' => '2020-03-15'],
        ];

        // Badgeuses spécifiques par organisation - Intérieur
        $badgeuseInterieur = [
            ['ref' => 'BADGE-INTERIEUR-BETA-001', 'date' => '2020-02-01'],
            ['ref' => 'BADGE-INTERIEUR-BUREAU-001', 'date' => '2020-02-01'],
            ['ref' => 'BADGE-INTERIEUR-RH-001', 'date' => '2020-04-01'],
            ['ref' => 'BADGE-INTERIEUR-POLICE-001', 'date' => '2020-04-01'],
        ];

        // Badgeuses spécifiques par organisation - Économie
        $badgeuseEconomie = [
            ['ref' => 'BADGE-ECONOMIE-BUREAU-001', 'date' => '2020-03-10'],
            ['ref' => 'BADGE-ECONOMIE-FINANCE-001', 'date' => '2020-04-15'],
        ];

        $allBadgeuses = array_merge($badgeuseCommune, $badgeuseDefense, $badgeuseInterieur, $badgeuseEconomie);

        foreach ($allBadgeuses as $index => $data) {
            $badgeuse = new Badgeuse();
            $badgeuse->setReference($data['ref']);
            $badgeuse->setDateInstallation(new \DateTime($data['date']));
            $manager->persist($badgeuse);
            $badgeuses['badgeuse_' . ($index + 1)] = $badgeuse;
        }

        // ========== ACCÈS ==========
        $accesData = [
            // Accès communs - Indices 1-3
            ['badgeuse' => 'badgeuse_1', 'zone' => 'principale', 'nom' => 'Accès Principal - Entrée A'],
            ['badgeuse' => 'badgeuse_2', 'zone' => 'principale', 'nom' => 'Accès Principal - Entrée B'],
            ['badgeuse' => 'badgeuse_3', 'zone' => 'public', 'nom' => 'Accès Hall Public'],

            // Accès Défense - Indices 4-9 (zones spécifiques Défense)
            ['badgeuse' => 'badgeuse_4', 'zone' => 'defense_alpha', 'nom' => 'Accès Zone Alpha Défense'],
            ['badgeuse' => 'badgeuse_5', 'zone' => 'defense_beta', 'nom' => 'Accès Zone Beta Défense'],
            ['badgeuse' => 'badgeuse_6', 'zone' => 'defense_bureau', 'nom' => 'Accès Bureaux Défense'],
            ['badgeuse' => 'badgeuse_7', 'zone' => 'defense_it', 'nom' => 'Accès Zone IT Défense'],
            ['badgeuse' => 'badgeuse_8', 'zone' => 'defense_security', 'nom' => 'Accès Zone Sécurité Défense'],
            ['badgeuse' => 'badgeuse_9', 'zone' => 'defense_logistics', 'nom' => 'Accès Zone Logistique Défense'],

            // Accès Intérieur - Indices 10-13 (zones spécifiques Intérieur)
            ['badgeuse' => 'badgeuse_10', 'zone' => 'interieur_beta', 'nom' => 'Accès Zone Beta Intérieur'],
            ['badgeuse' => 'badgeuse_11', 'zone' => 'interieur_bureau', 'nom' => 'Accès Bureaux Intérieur'],
            ['badgeuse' => 'badgeuse_12', 'zone' => 'interieur_rh', 'nom' => 'Accès Zone RH Intérieur'],
            ['badgeuse' => 'badgeuse_13', 'zone' => 'interieur_police', 'nom' => 'Accès Zone Police Nationale'],

            // Accès Économie - Indices 14-15 (zones spécifiques Économie)
            ['badgeuse' => 'badgeuse_14', 'zone' => 'economie_bureau', 'nom' => 'Accès Bureaux Économie'],
            ['badgeuse' => 'badgeuse_15', 'zone' => 'economie_finance', 'nom' => 'Accès Zone Finances Publiques'],
        ];

        foreach ($accesData as $data) {
            $acces = new Acces();
            $acces->setNomAcces($data['nom']);
            $acces->setDateInstallation(new \DateTime('2020-01-01'));
            $acces->setZone($zones[$data['zone']]);
            $acces->setBadgeuse($badgeuses[$data['badgeuse']]);
            $manager->persist($acces);
        }

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

        // Secondary service assignments (multiple per user)
        $secondaryTravaillerData = [
            // Admin défense - accès à plusieurs services secondaires
            ['user' => 'admin_defense', 'service' => 'security_defense', 'date' => '2020-01-15'],
            ['user' => 'admin_defense', 'service' => 'it_defense', 'date' => '2020-01-15'],
            ['user' => 'admin_defense', 'service' => 'logistics_defense', 'date' => '2020-01-15'],

            // Utilisateurs défense - spécialisations
            ['user' => 'user_defense_1', 'service' => 'it_defense', 'date' => '2021-03-15'],
            ['user' => 'user_defense_1', 'service' => 'security_defense', 'date' => '2022-01-01'],

            ['user' => 'user_defense_2', 'service' => 'logistics_defense', 'date' => '2021-04-10'],
            ['user' => 'user_defense_2', 'service' => 'it_defense', 'date' => '2022-06-01'],

            // Admin intérieur - accès à plusieurs services secondaires
            ['user' => 'admin_interieur', 'service' => 'rh_interieur', 'date' => '2020-02-01'],
            ['user' => 'admin_interieur', 'service' => 'police_interieur', 'date' => '2020-02-01'],

            // Utilisateurs intérieur - spécialisations
            ['user' => 'user_interieur_1', 'service' => 'rh_interieur', 'date' => '2021-06-01'],
            ['user' => 'user_interieur_2', 'service' => 'police_interieur', 'date' => '2021-08-01'],
            ['user' => 'user_interieur_2', 'service' => 'rh_interieur', 'date' => '2022-03-01'],

            // Utilisateur économie - services secondaires
            ['user' => 'user_economie_1', 'service' => 'finance_economie', 'date' => '2021-09-15'],

            // SuperAdmin - accès à tous les services secondaires pour supervision
            ['user' => 'superadmin', 'service' => 'it_defense', 'date' => '2020-01-01'],
            ['user' => 'superadmin', 'service' => 'security_defense', 'date' => '2020-01-01'],
            ['user' => 'superadmin', 'service' => 'logistics_defense', 'date' => '2020-01-01'],
            ['user' => 'superadmin', 'service' => 'rh_interieur', 'date' => '2020-01-01'],
            ['user' => 'superadmin', 'service' => 'police_interieur', 'date' => '2020-01-01'],
            ['user' => 'superadmin', 'service' => 'finance_economie', 'date' => '2020-01-01'],

            // User test - quelques services pour les tests
            ['user' => 'user_test', 'service' => 'it_defense', 'date' => '2024-01-01'],
            ['user' => 'user_test', 'service' => 'security_defense', 'date' => '2024-02-01'],
        ];

        foreach ($secondaryTravaillerData as $data) {
            $travailler = new Travailler();
            $travailler->setUtilisateur($users[$data['user']]);
            $travailler->setService($services[$data['service']]);
            $travailler->setDateDebut(new \DateTime($data['date']));
            $manager->persist($travailler);
        }

        // ========== SERVICE ZONES - RELATION 1:1 ==========
        $serviceZoneData = [
            // Services principaux - une zone principale + leur zone spécifique + zone publique
            ['service' => 'principal_defense', 'zone' => 'principale'],
            ['service' => 'principal_defense', 'zone' => 'defense_bureau'],
            ['service' => 'principal_defense', 'zone' => 'public'],

            ['service' => 'principal_interieur', 'zone' => 'principale'],
            ['service' => 'principal_interieur', 'zone' => 'interieur_bureau'],
            ['service' => 'principal_interieur', 'zone' => 'public'],

            ['service' => 'principal_economie', 'zone' => 'principale'],
            ['service' => 'principal_economie', 'zone' => 'economie_bureau'],
            ['service' => 'principal_economie', 'zone' => 'public'],

            // Services secondaires - accès principal + leur zone dédiée UNIQUEMENT
            ['service' => 'it_defense', 'zone' => 'principale'],
            ['service' => 'it_defense', 'zone' => 'defense_it'],

            ['service' => 'security_defense', 'zone' => 'principale'],
            ['service' => 'security_defense', 'zone' => 'defense_security'],
            ['service' => 'security_defense', 'zone' => 'defense_alpha'],
            ['service' => 'security_defense', 'zone' => 'defense_beta'],

            ['service' => 'logistics_defense', 'zone' => 'principale'],
            ['service' => 'logistics_defense', 'zone' => 'defense_logistics'],

            ['service' => 'rh_interieur', 'zone' => 'principale'],
            ['service' => 'rh_interieur', 'zone' => 'interieur_rh'],

            ['service' => 'police_interieur', 'zone' => 'principale'],
            ['service' => 'police_interieur', 'zone' => 'interieur_police'],
            ['service' => 'police_interieur', 'zone' => 'interieur_beta'],

            ['service' => 'finance_economie', 'zone' => 'principale'],
            ['service' => 'finance_economie', 'zone' => 'economie_finance'],
        ];

        foreach ($serviceZoneData as $data) {
            $serviceZone = new ServiceZone();
            $serviceZone->setService($services[$data['service']]);
            $serviceZone->setZone($zones[$data['zone']]);
            $manager->persist($serviceZone);
        }

        // ========== POINTAGES (Time tracking) ==========
        $yesterday = new \DateTime('-1 day');
        $lastWeek = new \DateTime('-7 days');

        $pointageData = [
            // user_defense_1 : entrée par badgeuse principale puis zones secondaires
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_4', 'heure' => $lastWeek->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_7', 'heure' => $lastWeek->format('Y-m-d') . ' 10:00:00', 'type' => 'entree'], // Zone IT
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_7', 'heure' => $lastWeek->format('Y-m-d') . ' 12:00:00', 'type' => 'sortie'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_4', 'heure' => $lastWeek->format('Y-m-d') . ' 13:30:00', 'type' => 'entree'],
            ['badge' => 'user_defense_1', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'],

            // user_interieur_1 : entrée par badgeuse principale puis zones secondaires  
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_2', 'heure' => $lastWeek->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'],
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_11', 'heure' => $lastWeek->format('Y-m-d') . ' 09:30:00', 'type' => 'entree'],
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_12', 'heure' => $lastWeek->format('Y-m-d') . ' 10:00:00', 'type' => 'entree'], // Zone RH
            ['badge' => 'user_interieur_1', 'badgeuse' => 'badgeuse_2', 'heure' => $lastWeek->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'],

            // user_defense_2 : entrée par badgeuse principale puis zones secondaires
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 08:15:00', 'type' => 'entree'],
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_6', 'heure' => $lastWeek->format('Y-m-d') . ' 08:45:00', 'type' => 'entree'],
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_9', 'heure' => $lastWeek->format('Y-m-d') . ' 11:00:00', 'type' => 'entree'], // Zone Logistique
            ['badge' => 'user_defense_2', 'badgeuse' => 'badgeuse_1', 'heure' => $lastWeek->format('Y-m-d') . ' 18:00:00', 'type' => 'sortie'],
        ];

        // POINTAGES ÉTENDUS pour user_test : Historique sur 30 jours avec nouvelle logique
        $testPointageData = [];
        for ($i = 2; $i < 31; $i++) {
            $testDate = new \DateTime("-{$i} days");
            $dayOfWeek = $testDate->format('N');

            if ($dayOfWeek <= 5) {
                $entryBadgeuse = ($i % 2 == 0) ? 'badgeuse_1' : 'badgeuse_2';
                $workBadgeuse = ($i % 3 == 0) ? 'badgeuse_6' : 'badgeuse_5';

                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $entryBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'];
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'];
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 12:00:00', 'type' => 'sortie'];
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $workBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 13:30:00', 'type' => 'entree'];
                $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => $entryBadgeuse, 'heure' => $testDate->format('Y-m-d') . ' 17:30:00', 'type' => 'sortie'];
            } else {
                if ($dayOfWeek == 6 && $i <= 28) {
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $testDate->format('Y-m-d') . ' 09:00:00', 'type' => 'entree'];
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_5', 'heure' => $testDate->format('Y-m-d') . ' 09:30:00', 'type' => 'entree'];
                    $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $testDate->format('Y-m-d') . ' 13:00:00', 'type' => 'sortie'];
                }
            }
        }

        $yesterday = new \DateTime('-1 day');
        if ($yesterday->format('N') <= 5) {
            $testPointageData[] = ['badge' => 'user_test', 'badgeuse' => 'badgeuse_1', 'heure' => $yesterday->format('Y-m-d') . ' 08:30:00', 'type' => 'entree'];
        }

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
