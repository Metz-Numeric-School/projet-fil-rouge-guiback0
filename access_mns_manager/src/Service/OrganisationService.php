<?php

namespace App\Service;

use App\Entity\Organisation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;

/**
 * Service pour la gestion des organisations et le filtrage des données
 */
class OrganisationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SecurityBundle $security
    ) {}

    /**
     * Récupère l'organisation de l'utilisateur connecté
     */
    public function getCurrentUserOrganisation(): ?Organisation
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        // Récupération de l'organisation via le service de l'utilisateur
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        return $travailler?->getService()?->getOrganisation();
    }

    /**
     * Vérifie si l'utilisateur connecté appartient à l'organisation donnée
     */
    public function userBelongsToOrganisation(Organisation $organisation): bool
    {
        $currentOrganisation = $this->getCurrentUserOrganisation();
        return $currentOrganisation && $currentOrganisation->getId() === $organisation->getId();
    }

    /**
     * Vérifie si l'utilisateur a les permissions pour accéder aux données d'un autre utilisateur
     */
    public function canAccessUserData(User $targetUser): bool
    {
        $currentUser = $this->security->getUser();

        // Un utilisateur peut toujours accéder à ses propres données
        if ($currentUser instanceof User && $currentUser->getId() === $targetUser->getId()) {
            return true;
        }

        // Vérification des rôles manager/admin
        if ($this->security->isGranted('ROLE_MANAGER') || $this->security->isGranted('ROLE_ADMIN')) {
            // Vérification que les utilisateurs appartiennent à la même organisation
            return $this->usersInSameOrganisation($currentUser, $targetUser);
        }

        return false;
    }

    /**
     * Vérifie si deux utilisateurs appartiennent à la même organisation
     */
    public function usersInSameOrganisation(User $user1, User $user2): bool
    {
        $org1 = $this->getUserOrganisation($user1);
        $org2 = $this->getUserOrganisation($user2);

        return $org1 && $org2 && $org1->getId() === $org2->getId();
    }

    /**
     * Récupère l'organisation d'un utilisateur donné
     */
    public function getUserOrganisation(User $user): ?Organisation
    {
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        return $travailler?->getService()?->getOrganisation();
    }

    /**
     * Filtre une requête pour ne récupérer que les données de l'organisation courante
     */
    public function addOrganisationFilter($queryBuilder, string $alias = 'e'): void
    {
        $organisation = $this->getCurrentUserOrganisation();
        if (!$organisation) {
            // Si pas d'organisation, on ne retourne rien
            $queryBuilder->andWhere('1 = 0');
            return;
        }

        // Adapte le filtre selon l'entité
        $queryBuilder->andWhere($alias . '.organisation = :organisation')
            ->setParameter('organisation', $organisation);
    }

    /**
     * Récupère tous les utilisateurs de l'organisation courante
     */
    public function getOrganisationUsers(): array
    {
        $organisation = $this->getCurrentUserOrganisation();
        if (!$organisation) {
            return [];
        }

        return $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('u.travail', 't')
            ->join('t.service', 's')
            ->where('s.organisation = :organisation')
            ->andWhere('t.date_fin IS NULL')
            ->setParameter('organisation', $organisation)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si l'utilisateur connecté est manager
     */
    public function isManager(): bool
    {
        return $this->security->isGranted('ROLE_MANAGER') || $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Vérifie si l'utilisateur connecté est admin
     */
    public function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
