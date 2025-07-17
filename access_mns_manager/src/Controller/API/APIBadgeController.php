<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Service\BadgeService;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/badge', name: 'api_badge_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class APIBadgeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BadgeService $badgeService,
        private OrganisationService $organisationService,
        private ValidatorInterface $validator
    ) {}

    /**
     * Enregistrer un badgeage
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des données requises
            if (!isset($data['badge_number']) || !isset($data['badgeuse_id'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'MISSING_DATA',
                    'message' => 'Numéro de badge et ID de badgeuse requis'
                ], 400);
            }

            $badgeNumber = (int) $data['badge_number'];
            $badgeuseId = (int) $data['badgeuse_id'];
            $type = $data['type'] ?? 'entree';

            // Enregistrement du badgeage
            $result = $this->badgeService->recordBadgeAction($badgeNumber, $badgeuseId, $type);

            $statusCode = $result['success'] ? 201 : 400;
            return new JsonResponse($result, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Historique des badgeages
     */
    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'Utilisateur invalide'
                ], 401);
            }

            // Gestion des paramètres de date
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 50)));

            $startDateTime = $startDate ? new \DateTime($startDate) : null;
            $endDateTime = $endDate ? new \DateTime($endDate) : null;

            // Si pas de date de fin, on limite à 30 jours
            if (!$endDateTime) {
                $endDateTime = new \DateTime();
            }

            if (!$startDateTime) {
                $startDateTime = clone $endDateTime;
                $startDateTime->sub(new \DateInterval('P30D'));
            }

            // Récupération de l'historique
            $history = $this->badgeService->getUserBadgeHistory($user, $startDateTime, $endDateTime);

            // Pagination
            $totalRecords = count($history);
            $offset = ($page - 1) * $limit;
            $paginatedHistory = array_slice($history, $offset, $limit);

            return new JsonResponse([
                'success' => true,
                'data' => $paginatedHistory,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $limit)
                ],
                'period' => [
                    'start' => $startDateTime->format('Y-m-d'),
                    'end' => $endDateTime->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Statut actuel (présent/absent)
     */
    #[Route('/current', name: 'current', methods: ['GET'])]
    public function current(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'Utilisateur invalide'
                ], 401);
            }

            $status = $this->badgeService->getCurrentUserStatus($user);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user_id' => $user->getId(),
                    'status' => $status['status'],
                    'last_action' => $status['last_action'],
                    'date' => $status['date']
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération du statut'
            ], 500);
        }
    }

    /**
     * Badgeages d'un utilisateur spécifique (managers uniquement)
     */
    #[Route('/user/{id}', name: 'user_history', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function userHistory(int $id, Request $request): JsonResponse
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

            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 50)));

            $startDateTime = $startDate ? new \DateTime($startDate) : null;
            $endDateTime = $endDate ? new \DateTime($endDate) : null;

            // Si pas de date de fin, on limite à 30 jours
            if (!$endDateTime) {
                $endDateTime = new \DateTime();
            }

            if (!$startDateTime) {
                $startDateTime = clone $endDateTime;
                $startDateTime->sub(new \DateInterval('P30D'));
            }

            // Récupération de l'historique
            $history = $this->badgeService->getUserBadgeHistory($user, $startDateTime, $endDateTime);

            // Pagination
            $totalRecords = count($history);
            $offset = ($page - 1) * $limit;
            $paginatedHistory = array_slice($history, $offset, $limit);

            return new JsonResponse([
                'success' => true,
                'data' => $paginatedHistory,
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail()
                ],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $limit)
                ],
                'period' => [
                    'start' => $startDateTime->format('Y-m-d'),
                    'end' => $endDateTime->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Statut actuel d'un utilisateur (managers uniquement)
     */
    #[Route('/user/{id}/current', name: 'user_current', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function userCurrent(int $id): JsonResponse
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

            $status = $this->badgeService->getCurrentUserStatus($user);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom(),
                        'email' => $user->getEmail()
                    ],
                    'status' => $status['status'],
                    'last_action' => $status['last_action'],
                    'date' => $status['date']
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération du statut'
            ], 500);
        }
    }

    /**
     * Statistiques de badgeage pour l'organisation (managers uniquement)
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function stats(Request $request): JsonResponse
    {
        try {
            $organisation = $this->organisationService->getCurrentUserOrganisation();

            if (!$organisation) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'NO_ORGANISATION',
                    'message' => 'Aucune organisation trouvée'
                ], 403);
            }

            $date = $request->query->get('date', date('Y-m-d'));
            $selectedDate = new \DateTime($date);

            $users = $this->organisationService->getOrganisationUsers();
            $stats = [
                'date' => $date,
                'total_users' => count($users),
                'present_users' => 0,
                'absent_users' => 0,
                'users_status' => []
            ];

            foreach ($users as $user) {
                $status = $this->badgeService->getCurrentUserStatus($user);
                $isPresent = $status['status'] === 'present';

                if ($isPresent) {
                    $stats['present_users']++;
                } else {
                    $stats['absent_users']++;
                }

                $stats['users_status'][] = [
                    'user' => [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom()
                    ],
                    'status' => $status['status'],
                    'last_action' => $status['last_action']
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
}
