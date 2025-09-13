<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function getGroups(): array
    {
        return ['prod', 'production'];
    }

    public function load(ObjectManager $manager): void
    {
        // Récupération des variables d'environnement
        $superAdminEmail = $_ENV['SUPER_ADMIN_EMAIL'] ?? null;
        $superAdminPassword = $_ENV['SUPER_ADMIN_PASSWORD'] ?? null;

        // Si les variables ne sont pas définies, on ne fait rien
        if (!$superAdminEmail || !$superAdminPassword) {
            return;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $manager->getRepository(User::class)->findOneBy(['email' => $superAdminEmail]);
        if ($existingUser) {
            return; // L'utilisateur existe déjà
        }

        // Créer le Super Admin
        $superAdmin = new User();
        $superAdmin->setEmail($superAdminEmail);
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->hasher->hashPassword($superAdmin, $superAdminPassword));
        $superAdmin->setNom($_ENV['SUPER_ADMIN_NOM'] ?? 'ADMIN');
        $superAdmin->setPrenom($_ENV['SUPER_ADMIN_PRENOM'] ?? 'Super');
        $superAdmin->setTelephone($_ENV['SUPER_ADMIN_TELEPHONE'] ?? '01.00.00.00.01');
        $superAdmin->setDateInscription(new \DateTime());
        $superAdmin->setCompteActif(true);

        $manager->persist($superAdmin);
        $manager->flush();
    }
}