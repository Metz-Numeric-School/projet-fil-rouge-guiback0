<?php

namespace App\Controller;

use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController{
    #[Route('/', name: 'app_dashboard')]
    public function index(UserRepository $userRepository, OrganisationRepository $organisationRepository): Response
    {
        // Count total users and organisations
        $totalUsers = $userRepository->count([]);
        $totalOrganisations = $organisationRepository->count([]);
        
        // Get recent organisations (last 5)
        $recentOrganisations = $organisationRepository->findBy([], ['date_creation' => 'DESC'], 5);
        
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'total_users' => $totalUsers,
            'total_organisations' => $totalOrganisations,
            'recent_organisations' => $recentOrganisations,
        ]);
    }
}
