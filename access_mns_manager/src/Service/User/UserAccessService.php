<?php

namespace App\Service\User;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;

class UserAccessService
{
    public function __construct(
        private SecurityBundle $security
    ) {}

    public function canAccessUserData(User $targetUser): bool
    {
        $currentUser = $this->security->getUser();

        if ($currentUser instanceof User && $currentUser->getId() === $targetUser->getId()) {
            return true;
        }

        return false;
    }

    public function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }
}