<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Service;
use App\Entity\ServiceZone;
use App\Entity\User;
use App\Entity\Travailler;
use App\Entity\Zone;
use App\Form\OrganisationType;
use App\Form\ServiceType;
use App\Form\UserType;
use App\Repository\OrganisationRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/organisation')]
final class OrganisationController extends AbstractController
{
    #[Route(name: 'app_organisation_index', methods: ['GET'])]
    public function index(OrganisationRepository $organisationRepository): Response
    {
        return $this->render('organisation/index.html.twig', [
            'organisations' => $organisationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_organisation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organisation = new Organisation();
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();

            try {
                $entityManager->persist($organisation);

                // Create the default service
                $defaultService = new Service();
                $defaultService->setNomService('Service principal');
                $defaultService->setNiveauService(1);
                $defaultService->setOrganisation($organisation);
                $entityManager->persist($defaultService);
                $entityManager->flush(); // Flush to get IDs

                // Create the principale zone
                $principaleZone = new Zone();
                $principaleZone->setNomZone('Zone principale');
                $principaleZone->setDescription('Zone principale créée automatiquement');
                $entityManager->persist($principaleZone);
                $entityManager->flush(); // Flush to get zone ID

                // Link the service to the principale zone
                $serviceZone = new ServiceZone();
                $serviceZone->setService($defaultService);
                $serviceZone->setZone($principaleZone);
                $entityManager->persist($serviceZone);
                $entityManager->flush();

                $entityManager->commit();

                return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception) {
                $entityManager->rollback();
                $this->addFlash('error', 'Erreur lors de la création de l\'organisation. Veuillez réessayer.');
            }
        }

        return $this->render('organisation/new.html.twig', [
            'organisation' => $organisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organisation_show', methods: ['GET'])]
    public function show(Organisation $organisation, PointageRepository $pointageRepository): Response
    {
        // Get the last 5 pointages for this organisation
        try {
            $allPointages = $pointageRepository->findByOrganisation($organisation->getId());
            $recentPointages = array_slice($allPointages, 0, 5);
        } catch (\Exception $e) {
            // If there's an error fetching pointages, set empty array
            $recentPointages = [];
        }

        // Get deactivated users count for admins only
        $deactivatedUsersCount = 0;
        if ($this->isGranted('ROLE_ADMIN')) {
            $deactivatedUsers = [];
            foreach ($organisation->getServices() as $service) {
                foreach ($service->getTravail() as $travail) {
                    $user = $travail->getUtilisateur();
                    if ($user && !$user->isCompteActif() && !in_array($user, $deactivatedUsers)) {
                        $deactivatedUsers[] = $user;
                    }
                }
            }
            $deactivatedUsersCount = count($deactivatedUsers);
        }

        return $this->render('organisation/show.html.twig', [
            'organisation' => $organisation,
            'recent_pointages' => $recentPointages,
            'deactivated_users_count' => $deactivatedUsersCount,
        ]);
    }

    #[Route('/{id}/service/new', name: 'app_organisation_service_new', methods: ['GET', 'POST'])]
    public function newService(Request $request, Organisation $organisation, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $service->setOrganisation($organisation);

        $form = $this->createForm(ServiceType::class, $service, [
            'hide_organisation' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->beginTransaction();

            try {
                $entityManager->persist($service);
                $entityManager->flush(); // Flush to get service ID

                // Check if a "Zone principale" exists for this organisation
                // Since zones are linked to organisations through services, we need to find it via ServiceZone
                $principaleZone = $entityManager->getRepository(Zone::class)
                    ->createQueryBuilder('z')
                    ->join('z.serviceZones', 'sz')
                    ->join('sz.service', 's')
                    ->where('s.organisation = :organisation')
                    ->andWhere('z.nom_zone = :nom_zone')
                    ->setParameter('organisation', $organisation)
                    ->setParameter('nom_zone', 'Zone principale')
                    ->getQuery()
                    ->getOneOrNullResult();

                // If no Zone principale exists, create it and link it to this service
                if (!$principaleZone) {
                    $principaleZone = new Zone();
                    $principaleZone->setNomZone('Zone principale');
                    $principaleZone->setDescription('Zone principale créée automatiquement');
                    $entityManager->persist($principaleZone);
                    $entityManager->flush(); // Flush to get zone ID

                    // Link the new service to the principale zone
                    $serviceZone = new ServiceZone();
                    $serviceZone->setService($service);
                    $serviceZone->setZone($principaleZone);
                    $entityManager->persist($serviceZone);
                    $entityManager->flush();
                }

                $entityManager->commit();

                $this->addFlash('success', 'Service créé avec succès !');
                return $this->redirectToRoute('app_service_show', ['id' => $service->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception) {
                $entityManager->rollback();
                $this->addFlash('error', 'Erreur lors de la création du service. Veuillez réessayer.');
            }
        }

        return $this->render('organisation/new_service.html.twig', [
            'organisation' => $organisation,
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_organisation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Organisation $organisation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organisation/edit.html.twig', [
            'organisation' => $organisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_organisation_delete', methods: ['POST'])]
    public function delete(Request $request, Organisation $organisation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $organisation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($organisation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_organisation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/user/new', name: 'app_organisation_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, Organisation $organisation, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        // Get organisation's principal service and other services for secondary assignment
        $principalService = null;
        $availableServices = [];

        foreach ($organisation->getServices() as $service) {
            if ($service->getNomService() === 'Service principal') {
                $principalService = $service;
            } else {
                $availableServices[] = $service;
            }
        }

        $form = $this->createForm(UserType::class, $user, [
            'available_services' => $availableServices,
            'organisation_context' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);

            // Auto-assign to principal service (mandatory and immutable)
            if ($principalService) {
                $principalTravail = new Travailler();
                $principalTravail->setUtilisateur($user);
                $principalTravail->setService($principalService);
                $principalTravail->setDateDebut(new \DateTime());
                $principalTravail->setIsPrincipal(true);
                $entityManager->persist($principalTravail);
            }

            // Handle secondary services if selected
            $secondaryServices = $form->get('secondary_services')->getData();
            if ($secondaryServices) {
                foreach ($secondaryServices as $service) {
                    $secondaryTravail = new Travailler();
                    $secondaryTravail->setUtilisateur($user);
                    $secondaryTravail->setService($service);
                    $secondaryTravail->setDateDebut(new \DateTime());
                    $secondaryTravail->setIsPrincipal(false);
                    $entityManager->persist($secondaryTravail);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès et assigné à l\'organisation.');
            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organisation/new_user.html.twig', [
            'organisation' => $organisation,
            'user' => $user,
            'form' => $form,
        ]);
    }
}
