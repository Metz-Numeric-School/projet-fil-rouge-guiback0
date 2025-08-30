<?php

namespace App\Controller\API\Auth;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $JWTManager,
        private ValidatorInterface $validator,
        private UserService $userService
    ) {}

    /**
     * Authentification utilisateur
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'MISSING_CREDENTIALS',
                'message' => 'Email et mot de passe requis'
            ], 400);
        }

        // Recherche de l'utilisateur
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Identifiants invalides'
            ], 401);
        }

        // Vérification du mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Identifiants invalides'
            ], 401);
        }

        // Mise à jour de la dernière connexion
        $user->updateLastLogin();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Génération du token JWT
        $token = $this->JWTManager->create($user);

        // Récupération des informations de l'organisation
        $organisation = $this->userService->getUserOrganisation($user);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'roles' => $user->getRoles()
                ],
                'organisation' => $organisation ? [
                    'id' => $organisation->getId(),
                    'nom' => $organisation->getNomOrganisation()
                ] : null
            ],
            'message' => 'Connexion réussie'
        ]);
    }

    /**
     * Refresh token JWT
     */
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function refresh(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        $token = $this->JWTManager->create($user);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'token' => $token
            ],
            'message' => 'Token renouvelé avec succès'
        ]);
    }

    /**
     * Profil utilisateur connecté
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        // Récupération des informations de l'organisation
        $organisation = $this->userService->getUserOrganisation($user);

        // Récupération du service actuel
        $currentService = null;
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        if ($travailler && $travailler->getService()) {
            $service = $travailler->getService();
            $currentService = [
                'id' => $service->getId(),
                'nom' => $service->getNomService(),
                'niveau' => $service->getNiveauService()
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'date_naissance' => $user->getDateNaissance()?->format('Y-m-d'),
                'date_inscription' => $user->getDateInscription()->format('Y-m-d'),
                'horraire' => $user->getHorraire()?->format('H:i'),
                'heure_debut' => $user->getHeureDebut()?->format('H:i'),
                'jours_semaine_travaille' => $user->getJoursSemaineTravaille(),
                'poste' => $user->getPoste(),
                'date_derniere_connexion' => $user->getDateDerniereConnexion()?->format('Y-m-d H:i:s'),
                'date_derniere_modification' => $user->getDateDerniereModification()?->format('Y-m-d H:i:s'),
                'compte_actif' => $user->isCompteActif(),
                'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d'),
                'roles' => $user->getRoles(),
                'organisation' => $organisation ? [
                    'id' => $organisation->getId(),
                    'nom' => $organisation->getNomOrganisation(),
                    'email' => $organisation->getEmail(),
                    'telephone' => $organisation->getTelephone(),
                    'site_web' => $organisation->getSiteWeb()
                ] : null,
                'service' => $currentService,
                'principal_service' => $user->getPrincipalService() ? [
                    'id' => $user->getPrincipalService()->getId(),
                    'nom' => $user->getPrincipalService()->getNomService(),
                    'niveau' => $user->getPrincipalService()->getNiveauService()
                ] : null,
                'secondary_services' => array_map(function($service) {
                    return [
                        'id' => $service->getId(),
                        'nom' => $service->getNomService(),
                        'niveau' => $service->getNiveauService()
                    ];
                }, $user->getSecondaryServices())
            ]
        ]);
    }

    /**
     * Déconnexion (côté client principalement)
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function logout(): JsonResponse
    {
        // Avec JWT, la déconnexion est principalement gérée côté client
        // Le token peut être ajouté à une blacklist si nécessaire

        return new JsonResponse([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}
