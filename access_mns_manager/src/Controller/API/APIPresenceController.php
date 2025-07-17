<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Service\PresenceService;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/presence', name: 'api_presence_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class APIPresenceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PresenceService $presenceService,
        private OrganisationService $organisationService
    ) {}

    /**
     * Présence hebdomadaire
     */
    #[Route('/weekly', name: 'weekly', methods: ['GET'])]
    public function weekly(Request $request): JsonResponse
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

            $week = $request->query->get('week', date('Y-m-d', strtotime('monday this week')));

            // Validation du format de date
            if (!$this->isValidDate($week)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM-DD attendu)'
                ], 400);
            }

            $userId = $request->query->get('user_id');
            $targetUser = $user;

            // Si un user_id est spécifié, vérifier les permissions
            if ($userId) {
                if (!$this->organisationService->isManager()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Permissions insuffisantes pour consulter les données d\'autres utilisateurs'
                    ], 403);
                }

                $targetUser = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$targetUser) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'USER_NOT_FOUND',
                        'message' => 'Utilisateur non trouvé'
                    ], 404);
                }

                if (!$this->organisationService->canAccessUserData($targetUser)) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Accès refusé à cet utilisateur'
                    ], 403);
                }
            }

            $weeklyPresence = $this->presenceService->getWeeklyPresence($targetUser, $week);

            return new JsonResponse([
                'success' => true,
                'data' => $weeklyPresence
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des données hebdomadaires'
            ], 500);
        }
    }

    /**
     * Présence mensuelle
     */
    #[Route('/monthly', name: 'monthly', methods: ['GET'])]
    public function monthly(Request $request): JsonResponse
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

            $month = $request->query->get('month', date('Y-m'));

            // Validation du format de date
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM attendu)'
                ], 400);
            }

            $userId = $request->query->get('user_id');
            $targetUser = $user;

            // Si un user_id est spécifié, vérifier les permissions
            if ($userId) {
                if (!$this->organisationService->isManager()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Permissions insuffisantes pour consulter les données d\'autres utilisateurs'
                    ], 403);
                }

                $targetUser = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$targetUser) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'USER_NOT_FOUND',
                        'message' => 'Utilisateur non trouvé'
                    ], 404);
                }

                if (!$this->organisationService->canAccessUserData($targetUser)) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Accès refusé à cet utilisateur'
                    ], 403);
                }
            }

            $monthlyPresence = $this->presenceService->getMonthlyPresence($targetUser, $month);

            return new JsonResponse([
                'success' => true,
                'data' => $monthlyPresence
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des données mensuelles'
            ], 500);
        }
    }

    /**
     * Présence journalière
     */
    #[Route('/daily', name: 'daily', methods: ['GET'])]
    public function daily(Request $request): JsonResponse
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

            $date = $request->query->get('date', date('Y-m-d'));

            // Validation du format de date
            if (!$this->isValidDate($date)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM-DD attendu)'
                ], 400);
            }

            $userId = $request->query->get('user_id');
            $targetUser = $user;

            // Si un user_id est spécifié, vérifier les permissions
            if ($userId) {
                if (!$this->organisationService->isManager()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Permissions insuffisantes pour consulter les données d\'autres utilisateurs'
                    ], 403);
                }

                $targetUser = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$targetUser) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'USER_NOT_FOUND',
                        'message' => 'Utilisateur non trouvé'
                    ], 404);
                }

                if (!$this->organisationService->canAccessUserData($targetUser)) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Accès refusé à cet utilisateur'
                    ], 403);
                }
            }

            $dailyPresence = $this->presenceService->getDailyPresence($targetUser, $date);

            return new JsonResponse([
                'success' => true,
                'data' => $dailyPresence
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des données journalières'
            ], 500);
        }
    }

    /**
     * Résumé des temps de travail
     */
    #[Route('/summary', name: 'summary', methods: ['GET'])]
    public function summary(Request $request): JsonResponse
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

            $startDate = $request->query->get('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->query->get('end_date', date('Y-m-d'));

            // Validation des formats de date
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM-DD attendu)'
                ], 400);
            }

            $userId = $request->query->get('user_id');
            $targetUser = $user;

            // Si un user_id est spécifié, vérifier les permissions
            if ($userId) {
                if (!$this->organisationService->isManager()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Permissions insuffisantes pour consulter les données d\'autres utilisateurs'
                    ], 403);
                }

                $targetUser = $this->entityManager->getRepository(User::class)->find($userId);
                if (!$targetUser) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'USER_NOT_FOUND',
                        'message' => 'Utilisateur non trouvé'
                    ], 404);
                }

                if (!$this->organisationService->canAccessUserData($targetUser)) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'ACCESS_DENIED',
                        'message' => 'Accès refusé à cet utilisateur'
                    ], 403);
                }
            }

            $summary = $this->presenceService->getPresenceSummary($targetUser, $startDate, $endDate);

            return new JsonResponse([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération du résumé'
            ], 500);
        }
    }

    /**
     * Présence de l'organisation (managers uniquement)
     */
    #[Route('/organisation', name: 'organisation', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function organisation(Request $request): JsonResponse
    {
        try {
            $startDate = $request->query->get('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->query->get('end_date', date('Y-m-d'));

            // Validation des formats de date
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM-DD attendu)'
                ], 400);
            }

            $organisationPresence = $this->presenceService->getOrganisationPresence($startDate, $endDate);

            return new JsonResponse([
                'success' => true,
                'data' => $organisationPresence
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des données d\'organisation'
            ], 500);
        }
    }

    /**
     * Export CSV des présences (managers uniquement)
     */
    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function export(Request $request): Response
    {
        try {
            $startDate = $request->query->get('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->query->get('end_date', date('Y-m-d'));

            // Validation des formats de date
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_DATE',
                    'message' => 'Format de date invalide (YYYY-MM-DD attendu)'
                ], 400);
            }

            $organisationPresence = $this->presenceService->getOrganisationPresence($startDate, $endDate);
            $csv = $this->presenceService->generateCSVReport($organisationPresence);

            $response = new Response($csv);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="presence_' . $startDate . '_' . $endDate . '.csv"');

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'EXPORT_ERROR',
                'message' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Statistiques de présence en temps réel (managers uniquement)
     */
    #[Route('/stats/realtime', name: 'stats_realtime', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function realtimeStats(): JsonResponse
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

            $users = $this->organisationService->getOrganisationUsers();
            $presentCount = 0;
            $totalCount = count($users);

            foreach ($users as $user) {
                $status = $this->presenceService->getDailyPresence($user, date('Y-m-d'));
                if ($status['status'] === 'present') {
                    $presentCount++;
                }
            }

            $stats = [
                'timestamp' => date('Y-m-d H:i:s'),
                'organisation' => $organisation->getNomOrganisation(),
                'total_users' => $totalCount,
                'present_users' => $presentCount,
                'absent_users' => $totalCount - $presentCount,
                'presence_rate' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 2) : 0
            ];

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

    /**
     * Valide un format de date YYYY-MM-DD
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
