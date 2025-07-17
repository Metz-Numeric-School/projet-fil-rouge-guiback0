<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Service\OrganisationService;
use App\Service\PresenceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: 'api_users_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class APIUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrganisationService $organisationService,
        private PresenceService $presenceService,
        private ValidatorInterface $validator
    ) {}

    /**
     * Liste des utilisateurs de l'organisation
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $users = $this->organisationService->getOrganisationUsers();

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 20)));
            $offset = ($page - 1) * $limit;

            $totalUsers = count($users);
            $paginatedUsers = array_slice($users, $offset, $limit);

            $userData = [];
            foreach ($paginatedUsers as $user) {
                $userData[] = $this->formatUserData($user);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $userData,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalUsers,
                    'total_pages' => ceil($totalUsers / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des utilisateurs'
            ], 500);
        }
    }

    /**
     * Détails d'un utilisateur
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'USER_NOT_FOUND',
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Vérification des permissions
            if (!$this->organisationService->canAccessUserData($user)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé'
                ], 403);
            }

            $userData = $this->formatUserData($user, true);

            return new JsonResponse([
                'success' => true,
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération de l\'utilisateur'
            ], 500);
        }
    }

    /**
     * Modifier un utilisateur (managers uniquement)
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_MANAGER')]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'USER_NOT_FOUND',
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Vérification des permissions
            if (!$this->organisationService->canAccessUserData($user)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé'
                ], 403);
            }

            $data = json_decode($request->getContent(), true);

            // Mise à jour des champs autorisés
            if (isset($data['nom'])) {
                $user->setNom($data['nom']);
            }
            if (isset($data['prenom'])) {
                $user->setPrenom($data['prenom']);
            }
            if (isset($data['telephone'])) {
                $user->setTelephone($data['telephone']);
            }
            if (isset($data['adresse'])) {
                $user->setAdresse($data['adresse']);
            }
            if (isset($data['poste'])) {
                $user->setPoste($data['poste']);
            }
            if (isset($data['date_naissance'])) {
                $user->setDateNaissance(new \DateTime($data['date_naissance']));
            }

            // Validation
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Données invalides',
                    'details' => $this->formatValidationErrors($errors)
                ], 400);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $this->formatUserData($user),
                'message' => 'Utilisateur mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'UPDATE_ERROR',
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur'
            ], 500);
        }
    }

    /**
     * Présence d'un utilisateur (managers uniquement)
     */
    #[Route('/{id}/presence', name: 'presence', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function presence(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'USER_NOT_FOUND',
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Vérification des permissions
            if (!$this->organisationService->canAccessUserData($user)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé'
                ], 403);
            }

            $startDate = $request->query->get('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->query->get('end_date', date('Y-m-d'));

            $presence = $this->presenceService->getPresenceSummary($user, $startDate, $endDate);

            return new JsonResponse([
                'success' => true,
                'data' => $presence
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des données de présence'
            ], 500);
        }
    }

    /**
     * Recherche d'utilisateurs
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('q', '');

            if (strlen($query) < 2) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'QUERY_TOO_SHORT',
                    'message' => 'La recherche doit contenir au moins 2 caractères'
                ], 400);
            }

            $organisation = $this->organisationService->getCurrentUserOrganisation();

            if (!$organisation) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'NO_ORGANISATION',
                    'message' => 'Aucune organisation trouvée'
                ], 403);
            }

            $users = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->join('u.travail', 't')
                ->join('t.service', 's')
                ->where('s.organisation = :organisation')
                ->andWhere('t.date_fin IS NULL')
                ->andWhere('(u.nom LIKE :query OR u.prenom LIKE :query OR u.email LIKE :query)')
                ->setParameter('organisation', $organisation)
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(20)
                ->getQuery()
                ->getResult();

            $userData = [];
            foreach ($users as $user) {
                $userData[] = $this->formatUserData($user);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $userData,
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'SEARCH_ERROR',
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Formate les données d'un utilisateur
     */
    private function formatUserData(User $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'telephone' => $user->getTelephone(),
            'poste' => $user->getPoste(),
            'date_inscription' => $user->getDateInscription()->format('Y-m-d')
        ];

        if ($detailed) {
            $data['date_naissance'] = $user->getDateNaissance()?->format('Y-m-d');
            $data['adresse'] = $user->getAdresse();
            $data['roles'] = $user->getRoles();

            // Récupération du service actuel
            $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
                ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

            if ($travailler && $travailler->getService()) {
                $service = $travailler->getService();
                $data['service'] = [
                    'id' => $service->getId(),
                    'nom' => $service->getNomService(),
                    'niveau' => $service->getNiveauService()
                ];
            }
        }

        return $data;
    }

    /**
     * Formate les erreurs de validation
     */
    private function formatValidationErrors($errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage()
            ];
        }
        return $formattedErrors;
    }
}
