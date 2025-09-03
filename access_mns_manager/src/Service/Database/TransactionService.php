<?php

namespace App\Service\Database;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Service pour gérer les transactions de base de données
 */
class TransactionService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Exécute une opération dans une transaction
     */
    public function executeInTransaction(callable $operation): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $result = $operation();
            $this->entityManager->commit();
            return $result;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Exécute une opération et retourne une JsonResponse
     */
    public function executeAndRespond(callable $operation, string $successMessage = 'Opération réussie'): JsonResponse
    {
        try {
            $result = $this->executeInTransaction($operation);
            
            return new JsonResponse([
                'success' => true,
                'data' => $result,
                'message' => $successMessage
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}