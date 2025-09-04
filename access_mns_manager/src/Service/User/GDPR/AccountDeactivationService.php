<?php

namespace App\Service\User\GDPR;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AccountDeactivationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function deactivateAccount(User $user): array
    {
        $user->deactivate();
        $this->entityManager->flush();

        return [
            'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d')
        ];
    }
}