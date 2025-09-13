<?php

namespace App\Service\User;

use App\Entity\Organisation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;

class UserOrganisationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SecurityBundle $security
    ) {}

    public function getCurrentUserOrganisation(): ?Organisation
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return $this->getUserOrganisation($user);
    }

    public function getUserOrganisation(User $user): ?Organisation
    {
        $principalService = $user->getPrincipalService();
        
        if ($principalService) {
            return $principalService->getOrganisation();
        }

        foreach ($user->getTravail() as $travail) {
            if ($travail->getDateFin() === null && $travail->getService()) {
                return $travail->getService()->getOrganisation();
            }
        }

        return null;
    }

    public function userBelongsToOrganisation(Organisation $organisation): bool
    {
        $currentOrganisation = $this->getCurrentUserOrganisation();
        return $currentOrganisation && $currentOrganisation->getId() === $organisation->getId();
    }

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

    public function addOrganisationFilter($queryBuilder, string $alias = 'e'): void
    {
        $organisation = $this->getCurrentUserOrganisation();
        if (!$organisation) {
            $queryBuilder->andWhere('1 = 0');
            return;
        }

        $queryBuilder->andWhere($alias . '.organisation = :organisation')
            ->setParameter('organisation', $organisation);
    }
}