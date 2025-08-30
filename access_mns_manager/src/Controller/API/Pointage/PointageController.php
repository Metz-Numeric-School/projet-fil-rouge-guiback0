<?php

namespace App\Controller\API\Pointage;

use App\Entity\User;
use App\Service\Pointage\BadgeService;
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
        private BadgeService $badgeService
    ) {}

    /**
     * Get accessible badgeuses for current user with status
     * Uses: GET /api/pointage/badgeuses
     */
    #[Route('/badgeuses', name: 'badgeuses', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getBadgeuses(): JsonResponse
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

            // Get user badgeuses with access status
            $badgeusesResult = $this->badgeService->getUserBadgeusesWithStatus($user);
            
            // Check if badgeuses retrieval failed
            if (isset($badgeusesResult['success']) && !$badgeusesResult['success']) {
                $this->entityManager->rollback();
                return new JsonResponse([
                    'success' => false,
                    'error' => $badgeusesResult['error'] ?? 'BADGEUSES_ERROR',
                    'message' => $badgeusesResult['message'] ?? 'Erreur lors de la récupération des badgeuses',
                    'debug_info' => [
                        'user_id' => $user->getId(),
                        'user_email' => $user->getEmail(),
                        'has_principal_service' => $user->getPrincipalService() !== null,
                        'principal_service_id' => $user->getPrincipalService()?->getId(),
                        'principal_service_name' => $user->getPrincipalService()?->getNomService()
                    ]
                ], 400);
            }
            
            // Get current user working status
            $userStatus = $this->badgeService->getUserWorkingStatus($user);
            
            // Get user active badges
            $userBadges = $this->badgeService->getUserActiveBadges($user);

            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'badgeuses' => $badgeusesResult['data'] ?? [],
                    'user_status' => $userStatus,
                    'user_badges' => $userBadges
                ],
                'message' => 'Badgeuses récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la récupération des badgeuses',
                'debug_info' => [
                    'exception_message' => $e->getMessage(),
                    'user_id' => $user->getId(),
                    'user_email' => $user->getEmail()
                ]
            ], 500);
        }
    }

    /**
     * Perform pointage action on a badgeuse
     * Uses: POST /api/pointage/action
     */
    #[Route('/action', name: 'action', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function performPointage(Request $request): JsonResponse
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

        // Validate request data
        if (!isset($data['badgeuse_id']) || !is_numeric($data['badgeuse_id'])) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_REQUEST',
                'message' => 'ID de badgeuse requis et doit être numérique'
            ], 400);
        }

        $badgeuseId = (int)$data['badgeuse_id'];
        $force = $data['force'] ?? false;

        try {
            $this->entityManager->beginTransaction();

            // Perform pointage with business logic validation
            $result = $this->badgeService->performPointageWithValidation($user, $badgeuseId, $force);

            if (!$result['success']) {
                if ($this->entityManager->getConnection()->isTransactionActive()) {
                    $this->entityManager->rollback();
                }
                
                return new JsonResponse([
                    'success' => false,
                    'error' => $result['error'] ?? 'POINTAGE_FAILED',
                    'message' => $result['message'],
                    'warning' => $result['warning'] ?? null,
                    'debug_info' => [
                        'user_id' => $user->getId(),
                        'badgeuse_id' => $badgeuseId,
                        'force' => $force,
                        'has_principal_service' => $user->getPrincipalService() !== null,
                        'principal_service_id' => $user->getPrincipalService()?->getId()
                    ]
                ], 400);
            }

            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'pointage' => $result['data']['pointage'],
                    'new_status' => $result['data']['new_status'],
                    'work_session' => $result['data']['work_session'] ?? null,
                    'message' => $result['data']['message']
                ],
                'message' => 'Pointage effectué avec succès'
            ]);

        } catch (\Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors du pointage',
                'debug_info' => [
                    'exception_message' => $e->getMessage(),
                    'user_id' => $user->getId(),
                    'badgeuse_id' => $badgeuseId
                ]
            ], 500);
        }
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

            $userStatus = $this->badgeService->getUserWorkingStatus($user);
            
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

            $workingTime = $this->badgeService->calculateWorkingTimeForPeriod($user, $startDate, $endDate);

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

            $validation = $this->badgeService->validatePointageAction($user, $badgeuseId);

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
            $previousStatus = $this->badgeService->getUserWorkingStatus($user);
            $wasWorking = ($previousStatus['status'] ?? 'absent') === 'present';

            // BadgeService determines the correct type (entree/sortie/acces)
            // based on whether the badgeuse provides access to principal or secondary services
            $result = $this->badgeService->performPointageWithValidation($user, $badgeuseId, false);

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
}