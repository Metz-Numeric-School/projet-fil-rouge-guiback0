<?php

namespace App\Controller;

use App\Repository\OrganisationRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        OrganisationRepository $organisationRepository,
        ServiceRepository $serviceRepository
    ): Response {
        // Get recent organisations (last 5) with enhanced data
        $recentOrganisations = $organisationRepository->findBy([], ['date_creation' => 'DESC'], 5);
        
        // Add service count for each organisation
        foreach ($recentOrganisations as $organisation) {
            $organisation->serviceCount = $serviceRepository->count(['organisation' => $organisation]);
        }

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'recent_organisations' => $recentOrganisations,
        ]);
    }
}
