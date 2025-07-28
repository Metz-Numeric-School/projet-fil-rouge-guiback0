<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Method 1: Using getParameter (simplest for AbstractController)
        $environment = $this->getParameter('kernel.environment');
        
        // Check if we're in production or development
        $isProd = $environment === 'prod';
        $isDev = $environment === 'dev';
        
        // You can now use these variables for conditional logic
        if ($isProd) {
            // Production-specific logic
            // e.g., enhanced security, different logging, etc.
        } elseif ($isDev) {
            // Development-specific logic
            // e.g., debug info, different error handling, etc.
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'environment' => $environment, // Pass to template if needed
            'is_prod' => $isProd,
            'is_dev' => $isDev,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
