<?php

namespace App\Controller\API\Pointage;

use App\Entity\User;
use App\Service\Pointage\BadgeuseAccessService;
use App\Service\Pointage\PointageValidationService;
use App\Service\Pointage\PointageService;
use App\Service\Pointage\UserStatusService;
use App\Service\Pointage\WorkTimeCalculatorService;
use App\Service\Pointage\BadgeValidatorService;
use App\Service\Database\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pointage', name: 'api_pointage_')]
class PointageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BadgeuseAccessService $badgeuseAccessService,
        private PointageValidationService $pointageValidationService,
        private PointageService $pointageService,
        private UserStatusService $userStatusService,
        private WorkTimeCalculatorService $workTimeCalculator,
        private BadgeValidatorService $badgeValidator,
        private TransactionService $transactionService
    ) {}

    /**
     * Get accessible badgeuses for current user with status
     * Uses: GET /api/pointage/badgeuses
     */
    #[Route('/badgeuses', name: 'badgeuses', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getBadgeuses(): JsonResponse
    {
        $user = $this->validateAuthenticatedUser();
        if ($user instanceof JsonResponse) return $user;

        return $this->transactionService->executeAndRespond(
            fn() => $this->getBadgeusesData($user),
            'Badgeuses récupérées avec succès'
        );
    }

    /**
     * Perform pointage action on a badgeuse
     * Uses: POST /api/pointage/action
     */
    #[Route('/action', name: 'action', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function performPointage(Request $request): JsonResponse
    {
        $user = $this->validateAuthenticatedUser();
        if ($user instanceof JsonResponse) return $user;

        $data = $this->validatePointageRequest($request);
        if ($data instanceof JsonResponse) return $data;

        return $this->transactionService->executeAndRespond(
            fn() => $this->executePointage($user, $data['badgeuse_id'], $data['force']),
            'Pointage effectué avec succès'
        );
    }

    /**
     * Get current user working status
     * Uses: GET /api/pointage/status
     */
    #[Route('/status', name: 'status', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUserStatus(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        if (!$user->isCompteActif()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ACCOUNT_DEACTIVATED',
                'message' => 'Votre compte est désactivé'
            ], 403);
        }

        try {
            $this->entityManager->beginTransaction();

            $userStatus = $this->userStatusService->getUserWorkingStatus($user);
            
            // Ajouter la clé is_working pour compatibilité
            $userStatus['is_working'] = ($userStatus['status'] ?? 'absent') === 'present';
            $userStatus['last_pointage'] = $userStatus['last_action'];

            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'data' => $userStatus,
                'message' => 'Statut utilisateur récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la récupération du statut'
            ], 500);
        }
    }

    /**
     * Get working time for a specific period
     * Uses: GET /api/pointage/working-time
     */
    #[Route('/working-time', name: 'working_time', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getWorkingTime(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        if (!$user->isCompteActif()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ACCOUNT_DEACTIVATED',
                'message' => 'Votre compte est désactivé'
            ], 403);
        }

        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        if (!$startDate || !$endDate) {
            return new JsonResponse([
                'success' => false,
                'error' => 'MISSING_PARAMETERS',
                'message' => 'Dates de début et fin requises'
            ], 400);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_DATE_FORMAT',
                'message' => 'Format de date invalide (YYYY-MM-DD requis)'
            ], 400);
        }

        try {
            $this->entityManager->beginTransaction();

            $workingTime = $this->workTimeCalculator->calculateWorkingTimeForPeriod($user, $startDate, $endDate);

            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'data' => $workingTime,
                'message' => 'Temps de travail calculé avec succès'
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors du calcul du temps de travail'
            ], 500);
        }
    }

    /**
     * Validate a pointage action before performing it
     * Uses: POST /api/pointage/validate
     */
    #[Route('/validate', name: 'validate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function validatePointage(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        if (!$user->isCompteActif()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ACCOUNT_DEACTIVATED',
                'message' => 'Votre compte est désactivé'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['badgeuse_id']) || !is_numeric($data['badgeuse_id'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_REQUEST',
                'message' => 'ID de badgeuse requis et doit être numérique'
            ], 400);
        }

        $badgeuseId = (int)$data['badgeuse_id'];

        try {
            $this->entityManager->beginTransaction();

            $validation = $this->pointageValidationService->validatePointageAction($user, $badgeuseId);

            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'data' => $validation,
                'message' => 'Validation effectuée'
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la validation'
            ], 500);
        }
    }

    /**
     * Automatic pointage via badge scan (for testing environment)
     * Uses: POST /api/pointage/badge
     */
    #[Route('/badge', name: 'badge', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function pointageBadge(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        if (!$user->isCompteActif()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ACCOUNT_DEACTIVATED',
                'message' => 'Votre compte est désactivé'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['badgeuse_id']) || !is_numeric($data['badgeuse_id'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_REQUEST',
                'message' => 'ID de badgeuse requis et doit être numérique'
            ], 400);
        }

        $badgeuseId = (int)$data['badgeuse_id'];

        try {
            $this->entityManager->beginTransaction();

            // Get previous status for response
            $previousStatus = $this->userStatusService->getUserWorkingStatus($user);
            $wasWorking = ($previousStatus['status'] ?? 'absent') === 'present';

            // PointageService determines the correct type (entree/sortie/acces)
            // based on whether the badgeuse provides access to principal or secondary services
            $result = $this->pointageService->performPointageWithValidation($user, $badgeuseId, false);

            if (!$result['success']) {
                if ($this->entityManager->getConnection()->isTransactionActive()) {
                    $this->entityManager->rollback();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'error' => $result['error'] ?? 'POINTAGE_FAILED',
                    'message' => $result['message'],
                    'debug_info' => [
                        'user_id' => $user->getId(),
                        'badgeuse_id' => $badgeuseId,
                        'was_working' => $wasWorking
                    ]
                ], 400);
            }

            $this->entityManager->commit();

            // Get the pointage type from the result
            $pointageType = $result['data']['pointage']['type'] ?? 'unknown';

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'pointage' => $result['data']['pointage'],
                    'previous_status' => $wasWorking ? 'working' : 'not_working',
                    'new_status' => $result['data']['new_status'],
                    'action_performed' => $pointageType,
                    'work_session' => $result['data']['work_session'] ?? null,
                    'message' => $result['data']['message']
                ],
                'message' => 'Pointage automatique effectué avec succès'
            ]);

        } catch (\Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors du pointage automatique',
                'debug_info' => [
                    'exception_message' => $e->getMessage(),
                    'user_id' => $user->getId(),
                    'badgeuse_id' => $badgeuseId
                ]
            ], 500);
        }
    }

    /**
     * Valide l'utilisateur authentifié et son état
     */
    private function validateAuthenticatedUser(): User|JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        if (!$user->isCompteActif()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'ACCOUNT_DEACTIVATED',
                'message' => 'Votre compte est désactivé'
            ], 403);
        }

        return $user;
    }

    /**
     * Valide les données de requête de pointage
     */
    private function validatePointageRequest(Request $request): array|JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['badgeuse_id']) || !is_numeric($data['badgeuse_id'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_REQUEST',
                'message' => 'ID de badgeuse requis et doit être numérique'
            ], 400);
        }

        return [
            'badgeuse_id' => (int)$data['badgeuse_id'],
            'force' => $data['force'] ?? false
        ];
    }

    /**
     * Récupère les données des badgeuses pour un utilisateur
     */
    private function getBadgeusesData(User $user): array
    {
        $badgeusesResult = $this->badgeuseAccessService->getUserBadgeusesWithStatus($user);
        
        if (isset($badgeusesResult['success']) && !$badgeusesResult['success']) {
            throw new \Exception($badgeusesResult['message'] ?? 'Erreur lors de la récupération des badgeuses');
        }
        
        $userStatus = $this->userStatusService->getUserWorkingStatus($user);
        $userBadges = $this->badgeValidator->getUserActiveBadges($user);

        return [
            'badgeuses' => $badgeusesResult['data'] ?? [],
            'user_status' => $userStatus,
            'user_badges' => $userBadges
        ];
    }

    /**
     * Exécute l'action de pointage
     */
    private function executePointage(User $user, int $badgeuseId, bool $force): array
    {
        $result = $this->pointageService->performPointageWithValidation($user, $badgeuseId, $force);

        if (!$result['success']) {
            throw new \Exception($result['message']);
        }

        return [
            'pointage' => $result['data']['pointage'],
            'new_status' => $result['data']['new_status'],
            'work_session' => $result['data']['work_session'] ?? null,
            'message' => $result['data']['message']
        ];
    }
}