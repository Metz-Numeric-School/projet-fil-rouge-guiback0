<?php

namespace App\Controller;

use App\Entity\Zone;
use App\Entity\ServiceZone;
use App\Form\ZoneType;
use App\Repository\ZoneRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/zone')]
final class ZoneController extends AbstractController{
    #[Route(name: 'app_zone_index', methods: ['GET'])]
    public function index(ZoneRepository $zoneRepository): Response
    {
        return $this->render('zone/index.html.twig', [
            'zones' => $zoneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_zone_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ServiceRepository $serviceRepository): Response
    {
        $zone = new Zone();
        $service = null;
        $serviceZone = null;
        
        // Check if we're creating a zone for a specific service
        $serviceId = $request->query->get('service');
        if ($serviceId) {
            $service = $serviceRepository->find($serviceId);
            if (!$service) {
                $this->addFlash('error', 'Service introuvable.');
                return $this->redirectToRoute('app_service_index');
            }
            
            // Prepare ServiceZone for potential automatic assignment to the service
            $serviceZone = new ServiceZone();
            $serviceZone->setService($service);
        }
        
        $form = $this->createForm(ZoneType::class, $zone, [
            'from_service' => $service !== null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if a zone with the same name already exists
            $existingZone = $entityManager->getRepository(Zone::class)
                ->findOneBy([
                    'nom_zone' => $zone->getNomZone()
                ]);
            
            if ($existingZone) {
                $this->addFlash('warning', 'Une zone avec ce nom existe déjà.');
                return $this->render('zone/new.html.twig', [
                    'zone' => $zone,
                    'form' => $form,
                    'service' => $service,
                ]);
            }
            
            try {
                // Start transaction
                $entityManager->beginTransaction();
                
                // Create the zone
                $entityManager->persist($zone);
                $entityManager->flush(); // Flush to get the zone ID
                
                // If we're creating for a specific service, automatically assign it
                if ($service && $serviceZone) {
                    $serviceZone->setZone($zone);
                    $entityManager->persist($serviceZone);
                    $entityManager->flush();
                    
                    $entityManager->commit();
                    
                    $this->addFlash('success', 'Zone "' . $zone->getNomZone() . '" créée et assignée au service "' . $service->getNomService() . '" avec succès.');
                    return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
                } else {
                    $entityManager->commit();
                    
                    $this->addFlash('success', 'Zone "' . $zone->getNomZone() . '" créée avec succès.');
                    return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
                }
            } catch (\Exception) {
                $entityManager->rollback();
                $this->addFlash('error', 'Erreur lors de la création de la zone. Veuillez réessayer.');
            }
        }

        return $this->render('zone/new.html.twig', [
            'zone' => $zone,
            'form' => $form,
            'service' => $service,
        ]);
    }

    #[Route('/{id}', name: 'app_zone_show', methods: ['GET'])]
    public function show(Zone $zone): Response
    {
        return $this->render('zone/show.html.twig', [
            'zone' => $zone,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_zone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('zone/edit.html.twig', [
            'zone' => $zone,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_zone_delete', methods: ['POST'])]
    public function delete(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$zone->getId(), $request->getPayload()->getString('_token'))) {
            // Prevent deletion of "Zone principale"
            if ($zone->getNomZone() === 'Zone principale') {
                $this->addFlash('error', 'La Zone principale ne peut pas être supprimée.');
                return $this->redirectToRoute('app_zone_show', ['id' => $zone->getId()], Response::HTTP_SEE_OTHER);
            }

            $entityManager->remove($zone);
            $entityManager->flush();
            
            $this->addFlash('success', 'Zone "' . $zone->getNomZone() . '" supprimée avec succès.');
        }

        return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
    }
}
