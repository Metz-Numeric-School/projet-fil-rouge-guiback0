<?php

namespace App\Service\User;

use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserServiceDataService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getCurrentServiceData(User $user): ?array
    {
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        if ($travailler && $travailler->getService()) {
            return $this->formatServiceData($travailler->getService());
        }

        return null;
    }

    public function getPrincipalService(User $user): ?array
    {
        $principalService = $user->getPrincipalService();
        return $principalService ? $this->formatServiceData($principalService) : null;
    }

    public function getSecondaryServices(User $user): array
    {
        $secondaryServices = [];
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if ($service && !$service->isIsPrincipal()) {
                $secondaryServices[] = $this->formatServiceData($service);
            }
        }
        return $secondaryServices;
    }

    public function formatServiceData(Service $service): array
    {
        return [
            'id' => $service->getId(),
            'nom_service' => $service->getNomService(),
            'niveau_service' => $service->getNiveauService()
        ];
    }
}