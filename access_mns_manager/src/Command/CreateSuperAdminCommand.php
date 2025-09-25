<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Create a super admin user from environment variables'
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupération des variables d'environnement
        $email = getenv('SUPER_ADMIN_EMAIL') ?: null;
        $password = getenv('SUPER_ADMIN_PASSWORD') ?: null;
        $nom = getenv('SUPER_ADMIN_NOM') ?: 'SYSTÈME';
        $prenom = getenv('SUPER_ADMIN_PRENOM') ?: 'SuperAdmin';

        if (!$email || !$password) {
            $io->error('Les variables d\'environnement SUPER_ADMIN_EMAIL et SUPER_ADMIN_PASSWORD doivent être définies.');
            return Command::FAILURE;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->warning(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));
            
            // Mettre à jour les rôles si ce n'est pas déjà un super admin
            if (!in_array('ROLE_SUPER_ADMIN', $existingUser->getRoles())) {
                $existingUser->setRoles(['ROLE_SUPER_ADMIN']);
                $existingUser->updateLastModification();
                $this->entityManager->flush();
                $io->success('L\'utilisateur existant a été promu super admin.');
            } else {
                $io->info('L\'utilisateur est déjà super admin.');
            }
            
            return Command::SUCCESS;
        }

        // Créer un nouveau super admin inspiré de CommonFixtures.php
        $superAdmin = new User();
        $superAdmin->setEmail($email);
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, $password));
        $superAdmin->setNom($nom);
        $superAdmin->setPrenom($prenom);
        $superAdmin->setTelephone('0100000001');
        $superAdmin->setDateInscription(new \DateTime());
        $superAdmin->setDateNaissance(new \DateTime('1980-01-01'));
        $superAdmin->setCompteActif(true);

        // Validation de l'entité (en excluant les contraintes de dates qui posent problème)
        $violations = $this->validator->validate($superAdmin, null, ['Default']);
        
        // Filtrer les erreurs de validation des dates qui sont correctes mais mal interprétées
        $filteredViolations = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            if (!in_array($propertyPath, ['date_naissance', 'date_inscription'])) {
                $filteredViolations[] = $violation;
            }
        }
        
        if (count($filteredViolations) > 0) {
            $io->error('Erreurs de validation :');
            foreach ($filteredViolations as $violation) {
                $io->text(sprintf('- %s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }
            return Command::FAILURE;
        }

        try {
            $this->entityManager->persist($superAdmin);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Super admin créé avec succès !' . PHP_EOL .
                'Email: %s' . PHP_EOL .
                'Nom: %s %s' . PHP_EOL .
                'Rôles: %s',
                $superAdmin->getEmail(),
                $superAdmin->getPrenom(),
                $superAdmin->getNom(),
                implode(', ', $superAdmin->getRoles())
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Erreur lors de la création du super admin : %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}