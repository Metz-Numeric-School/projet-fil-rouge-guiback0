<?php

namespace App\Controller;

use App\Entity\Acces;
use App\Entity\Badgeuse;
use App\Form\AccesType;
use App\Repository\AccesRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/acces')]
final class AccesController extends AbstractController{
    #[Route(name: 'app_acces_index', methods: ['GET'])]
    public function index(AccesRepository $accesRepository): Response
    {
        return $this->render('acces/index.html.twig', [
            'acces' => $accesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_acces_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        OrganisationRepository $organisationRepository,
        ZoneRepository $zoneRepository
    ): Response {
        $organisations = $organisationRepository->findAll();
        $selectedOrganisation = null;
        $zones = [];
        
        // Handle organisation selection
        $organisationId = $request->query->get('organisation') ?: $request->request->get('organisation');
        if ($organisationId) {
            $selectedOrganisation = $organisationRepository->find($organisationId);
            if ($selectedOrganisation) {
                $zones = $zoneRepository->findBy(['organisation' => $selectedOrganisation]);
            }
        }

        // Handle form submission
        if ($request->isMethod('POST') && $selectedOrganisation) {
            $selectedZones = $request->request->all('zones');
            $numeroBadgeuse = (int) $request->request->get('numero_badgeuse');
            $referenceBadgeuse = $request->request->get('reference_badgeuse');

            if (empty($selectedZones)) {
                $this->addFlash('error', 'Veuillez sélectionner au moins une zone.');
            } elseif (!$numeroBadgeuse || !$referenceBadgeuse) {
                $this->addFlash('error', 'Veuillez renseigner le numéro et la référence de la badgeuse.');
            } else {
                try {
                    $entityManager->beginTransaction();

                    // Create new badgeuse
                    $badgeuse = new Badgeuse();
                    $badgeuse->setReference($referenceBadgeuse);
                    $badgeuse->setDateInstallation(new \DateTime());
                    $entityManager->persist($badgeuse);
                    $entityManager->flush(); // Get badgeuse ID

                    $createdAccess = [];

                    // Create access for each selected zone
                    foreach ($selectedZones as $zoneId) {
                        $zone = $zoneRepository->find((int)$zoneId);
                        if ($zone && $zone->getOrganisation() === $selectedOrganisation) {
                            $acces = new Acces();
                            $acces->setNumeroBadgeuse($numeroBadgeuse);
                            $acces->setDateInstallation(new \DateTime());
                            $acces->setZone($zone);
                            $acces->setBadgeuse($badgeuse);
                            
                            $entityManager->persist($acces);
                            $createdAccess[] = $zone->getNomZone();
                        }
                    }

                    $entityManager->flush();
                    $entityManager->commit();

                    $this->addFlash('success', 
                        'Badgeuse "' . $referenceBadgeuse . '" créée avec succès. ' .
                        count($createdAccess) . ' accès configuré(s) pour les zones: ' . 
                        implode(', ', $createdAccess)
                    );

                    return $this->redirectToRoute('app_acces_index');

                } catch (\Exception) {
                    $entityManager->rollback();
                    $this->addFlash('error', 'Erreur lors de la création des accès. Veuillez réessayer.');
                }
            }
        }

        return $this->render('acces/new.html.twig', [
            'organisations' => $organisations,
            'selectedOrganisation' => $selectedOrganisation,
            'zones' => $zones,
        ]);
    }

    #[Route('/{id}', name: 'app_acces_show', methods: ['GET'])]
    public function show(Acces $acce): Response
    {
        return $this->render('acces/show.html.twig', [
            'acce' => $acce,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_acces_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Acces $acce, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AccesType::class, $acce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_acces_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('acces/edit.html.twig', [
            'acce' => $acce,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_acces_delete', methods: ['POST'])]
    public function delete(Request $request, Acces $acce, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$acce->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($acce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_acces_index', [], Response::HTTP_SEE_OTHER);
    }
}
