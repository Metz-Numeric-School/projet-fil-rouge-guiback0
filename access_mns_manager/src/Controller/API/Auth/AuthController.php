<?php

namespace App\Controller\API\Auth;

use App\DTO\LoginRequestDTO;
use App\Entity\User;
use App\Service\Security\LoginAttemptService;
use App\Service\User\UserOrganisationService;
use App\Service\User\UserServiceDataService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
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
        private UserOrganisationService $userOrganisationService,
        private UserServiceDataService $userServiceDataService,
        private LoginAttemptService $loginAttemptService
    ) {}

    /**
     * Authentification utilisateur
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // TEMPORAIRE: Rate limiting désactivé pour les tests
        /*
        try {
            // Vérification du rate limiting
            $this->loginAttemptService->checkAttempt($request);
        } catch (TooManyLoginAttemptsAuthenticationException $e) {
            $response = new JsonResponse([
                'success' => false,
                'error' => 'TOO_MANY_ATTEMPTS',
                'message' => $e->getMessage(),
                'remaining_attempts' => 0,
                'retry_after_minutes' => 15
            ], 429);
            
            // Ajout des en-têtes standards pour rate limiting
            $response->headers->set('Retry-After', '900'); // 15 minutes en secondes
            $response->headers->set('X-Rate-Limit-Limit', '3');
            $response->headers->set('X-Rate-Limit-Remaining', '0');
            $response->headers->set('X-Rate-Limit-Reset', (time() + 900)); // timestamp de reset
            
            return $response;
        }
        */

        $rawContent = $request->getContent();
        $data = json_decode($rawContent, true);

        // DEBUG TEMPORAIRE
        if (!is_array($data)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_JSON',
                'message' => 'Format JSON invalide',
                'debug' => [
                    'raw_content' => $rawContent,
                    'content_type' => $request->headers->get('Content-Type'),
                    'json_error' => json_last_error_msg(),
                    'decoded_data' => $data
                ]
            ], 400);
        }

        $loginDTO = LoginRequestDTO::fromArray($data);
        $errors = $this->validator->validate($loginDTO);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            
            return new JsonResponse([
                'success' => false,
                'error' => 'VALIDATION_FAILED',
                'message' => 'Données de connexion invalides',
                'details' => $errorMessages
            ], 400);
        }

        // Recherche de l'utilisateur
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $loginDTO->email]);

        if (!$user) {
            // $remainingAttempts = $this->loginAttemptService->getRemainingAttempts($request);
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Identifiants invalides'
                // 'remaining_attempts' => $remainingAttempts
            ], 401);
        }

        // Vérification du mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $loginDTO->password)) {
            // $remainingAttempts = $this->loginAttemptService->getRemainingAttempts($request);
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Identifiants invalides'
                // 'remaining_attempts' => $remainingAttempts
            ], 401);
        }

        // Authentification réussie - reset des tentatives
        // $this->loginAttemptService->resetAttempts($request);

        // Mise à jour de la dernière connexion
        $user->updateLastLogin();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Génération du token JWT
        $token = $this->JWTManager->create($user);

        // Récupération des informations de l'organisation
        $organisation = $this->userOrganisationService->getUserOrganisation($user);

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
     * Profil utilisateur connecté (données basiques)
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

        // Récupération des informations de base et services
        $organisation = $this->userOrganisationService->getUserOrganisation($user);
        $currentService = $this->userServiceDataService->getCurrentServiceData($user);
        $principalService = $this->userServiceDataService->getPrincipalService($user);
        $secondaryServices = $this->userServiceDataService->getSecondaryServices($user);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'poste' => $user->getPoste(),
                'roles' => $user->getRoles(),
                'compte_actif' => $user->isCompteActif(),
                'organisation' => $organisation ? [
                    'id' => $organisation->getId(),
                    'nom' => $organisation->getNomOrganisation()
                ] : null,
                'service' => $currentService,
                'principal_service' => $principalService,
                'secondary_services' => $secondaryServices
            ],
            'message' => 'Profil utilisateur récupéré'
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
