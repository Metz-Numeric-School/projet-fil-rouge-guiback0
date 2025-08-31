<?php

namespace App\Service\User;

use App\Entity\Organisation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;

/**
 * Service pour la gestion des utilisateurs et des organisations
 */
class UserService
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

        return $this->getUserOrganisation($user);
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

        return false;
    }


    /**
     * Récupère l'organisation d'un utilisateur donné
     */
    public function getUserOrganisation(User $user): ?Organisation
    {
        // Utilise la méthode getPrincipalService() de l'entité User
        $principalService = $user->getPrincipalService();
        
        if ($principalService) {
            return $principalService->getOrganisation();
        }

        // Si pas de service principal, prendre le premier service actif
        foreach ($user->getTravail() as $travail) {
            if ($travail->getDateFin() === null && $travail->getService()) {
                return $travail->getService()->getOrganisation();
            }
        }

        return null;
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

}
