<?php

namespace App\Controller;

use App\Entity\Acces;
use App\Entity\Service;
use App\Entity\Organisation;
use App\Entity\ServiceZone;
use App\Entity\Zone;
use App\Entity\User;
use App\Entity\Travailler;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/service')]
final class ServiceController extends AbstractController
{
    #[Route(name: 'app_service_index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository): Response
    {
        return $this->render('service/index.html.twig', [
            'services' => $serviceRepository->findAll(),
        ]);
    }

    #[Route('/organisation/{id}', name: 'app_service_by_organisation', methods: ['GET'])]
    public function byOrganisation(Organisation $organisation): Response
    {
        return $this->render('service/by_organisation.html.twig', [
            'services' => $organisation->getServices(),
            'organisation' => $organisation,
        ]);
    }

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();

            try {
                $entityManager->persist($service);
                $entityManager->flush(); // Flush to get service ID

                // Check if this is the first service for this organisation
                $organisation = $service->getOrganisation();
                if ($organisation) {
                    $existingServices = $entityManager->getRepository(Service::class)
                        ->findBy(['organisation' => $organisation]);

                    // If this is the first service (only the one we just created)
                    if (count($existingServices) === 1) {
                        // Create the principale zone
                        $principaleZone = new Zone();
                        $principaleZone->setNomZone('Zone principale');
                        $principaleZone->setDescription('Zone principale créée automatiquement');
                        $entityManager->persist($principaleZone);
                        $entityManager->flush(); // Flush to get zone ID

                        // Link the service to the principale zone
                        $serviceZone = new ServiceZone();
                        $serviceZone->setService($service);
                        $serviceZone->setZone($principaleZone);
                        $entityManager->persist($serviceZone);
                        $entityManager->flush();
                    }
                }

                $entityManager->commit();

                return $this->redirectToRoute('app_service_show', ['id' => $service->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception) {
                $entityManager->rollback();
                $this->addFlash('error', 'Erreur lors de la création du service. Veuillez réessayer.');
            }
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_show', methods: ['GET'])]
    public function show(Service $service, UserRepository $userRepository): Response
    {
        // Get users from the same organisation not currently working in this service
        $organisation = $service->getOrganisation();
        $availableUsers = [];

        if ($organisation) {
            // Get all users who have their principal service in this organisation
            $allOrgUsers = $userRepository->createQueryBuilder('u')
                ->join('u.travail', 't')
                ->join('t.service', 's')
                ->where('s.organisation = :organisation')
                ->andWhere('t.is_principal = true')
                ->setParameter('organisation', $organisation)
                ->getQuery()
                ->getResult();

            // Filter out users already working in this service
            $serviceUserIds = [];
            foreach ($service->getTravail() as $travail) {
                if ($travail->getUtilisateur()) {
                    $serviceUserIds[] = $travail->getUtilisateur()->getId();
                }
            }

            foreach ($allOrgUsers as $user) {
                if (!in_array($user->getId(), $serviceUserIds)) {
                    $availableUsers[] = $user;
                }
            }
        }

        return $this->render('service/show.html.twig', [
            'service' => $service,
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service, [
            'read_only_organisation' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $service->getId(), $request->getPayload()->getString('_token'))) {
            // Prevent deletion of "Service principal"
            if ($service->getNomService() === 'Service principal') {
                $this->addFlash('error', 'Le Service principal ne peut pas être supprimé.');
                return $this->redirectToRoute('app_service_show', ['id' => $service->getId()], Response::HTTP_SEE_OTHER);
            }

            $organisation = $service->getOrganisation();

            // Remove all Travailler relations for this service
            foreach ($service->getTravail() as $travail) {
                $entityManager->remove($travail);
            }

            // Remove associated ServiceZone entries and zones not used elsewhere
            foreach ($service->getServiceZones() as $serviceZone) {
                $zone = $serviceZone->getZone();
                $entityManager->remove($serviceZone);
                
                // Check if this zone is used by other services
                if ($zone && $zone->getNomZone() !== 'Zone principale') {
                    $otherServiceZones = $entityManager->getRepository(ServiceZone::class)
                        ->findBy(['zone' => $zone]);
                    
                    // If this zone is only used by this service, delete it and its access points
                    if (count($otherServiceZones) <= 1) { // <= 1 because we're about to remove the current one
                        // Remove all access points in this zone
                        foreach ($zone->getAcces() as $acces) {
                            $entityManager->remove($acces);
                        }
                        $entityManager->remove($zone);
                    }
                }
            }

            $entityManager->remove($service);
            $entityManager->flush();

            $this->addFlash('success', 'Service supprimé avec succès.');

            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_service_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/zone/{zoneId}/remove', name: 'app_service_zone_remove', methods: ['POST'])]
    public function removeZone(Request $request, Service $service, int $zoneId, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('remove_zone' . $service->getId() . '_' . $zoneId, $request->getPayload()->getString('_token'))) {
            $zone = $entityManager->getRepository(Zone::class)->find($zoneId);

            if ($zone) {
                $serviceZone = $entityManager->getRepository(ServiceZone::class)
                    ->findOneBy(['service' => $service, 'zone' => $zone]);

                if ($serviceZone) {
                    $entityManager->remove($serviceZone);
                    $entityManager->flush();

                    $this->addFlash('success', 'Zone retirée du service avec succès.');
                } else {
                    $this->addFlash('warning', 'Cette zone n\'est pas assignée à ce service.');
                }
            } else {
                $this->addFlash('error', 'Zone introuvable.');
            }
        }

        return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
    }

    #[Route('/{id}/user/{userId}/add', name: 'app_service_user_add', methods: ['POST'])]
    public function addUser(Service $service, int $userId, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if (!$this->isCsrfTokenValid('add_user' . $service->getId() . '_' . $userId, $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Check if user is already working in this service
        $existingTravail = $entityManager->getRepository(Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'service' => $service]);

        if ($existingTravail) {
            $this->addFlash('warning', 'Cet utilisateur travaille déjà dans ce service.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Check if user belongs to the same organisation
        $userPrincipalService = $user->getPrincipalService();
        if (!$userPrincipalService || $userPrincipalService->getOrganisation() !== $service->getOrganisation()) {
            $this->addFlash('error', 'Cet utilisateur n\'appartient pas à la même organisation.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Add user to service
        $travailler = new Travailler();
        $travailler->setUtilisateur($user);
        $travailler->setService($service);
        $travailler->setDateDebut(new \DateTime());
        $travailler->setIsPrincipal(false); // Secondary service assignment

        $entityManager->persist($travailler);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getPrenom()} {$user->getNom()} a été ajouté au service avec succès.");
        return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
    }

    #[Route('/{id}/user/{userId}/remove', name: 'app_service_user_remove', methods: ['POST'])]
    public function removeUser(Service $service, int $userId, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if (!$this->isCsrfTokenValid('remove_user' . $service->getId() . '_' . $userId, $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Find work relationship
        $travail = $entityManager->getRepository(Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'service' => $service]);

        if (!$travail) {
            $this->addFlash('warning', 'Cet utilisateur ne travaille pas dans ce service.');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Check if this is the user's principal service
        if ($travail->isIsPrincipal()) {
            $this->addFlash('error', "Impossible de retirer {$user->getPrenom()} {$user->getNom()} de son service principal.");
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }

        // Delete the work relationship record completely
        $entityManager->remove($travail);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur {$user->getPrenom()} {$user->getNom()} a été retiré du service avec succès.");
        return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
    }
}
