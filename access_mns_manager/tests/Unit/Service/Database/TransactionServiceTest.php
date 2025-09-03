<?php

namespace App\Tests\Unit\Service\Database;

use App\Service\Database\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionServiceTest extends TestCase
{
    private TransactionService $transactionService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->transactionService = new TransactionService($this->entityManager);
    }

    public function testExecuteInTransactionSuccess(): void
    {
        $expectedResult = ['success' => true, 'data' => 'test'];
        
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');
        
        $this->entityManager->expects($this->never())
            ->method('rollback');

        $operation = function() use ($expectedResult) {
            return $expectedResult;
        };

        $result = $this->transactionService->executeInTransaction($operation);

        $this->assertEquals($expectedResult, $result);
    }

    public function testExecuteInTransactionFailure(): void
    {
        $exception = new \Exception('Test exception');
        
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->never())
            ->method('commit');
        
        $this->entityManager->expects($this->once())
            ->method('rollback');

        $operation = function() use ($exception) {
            throw $exception;
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        $this->transactionService->executeInTransaction($operation);
    }

    public function testExecuteAndRespondSuccess(): void
    {
        $operationResult = ['id' => 1, 'name' => 'test'];
        
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');

        $operation = function() use ($operationResult) {
            return $operationResult;
        };

        $response = $this->transactionService->executeAndRespond($operation, 'Test réussi');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($operationResult, $responseData['data']);
        $this->assertEquals('Test réussi', $responseData['message']);
    }

    public function testExecuteAndRespondSuccessWithDefaultMessage(): void
    {
        $operationResult = ['id' => 1];
        
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');

        $operation = function() use ($operationResult) {
            return $operationResult;
        };

        $response = $this->transactionService->executeAndRespond($operation);

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Opération réussie', $responseData['message']);
    }

    public function testExecuteAndRespondFailure(): void
    {
        $exception = new \Exception('Database error');
        
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->never())
            ->method('commit');
        
        $this->entityManager->expects($this->once())
            ->method('rollback');

        $operation = function() use ($exception) {
            throw $exception;
        };

        $response = $this->transactionService->executeAndRespond($operation);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('INTERNAL_ERROR', $responseData['error']);
        $this->assertEquals('Database error', $responseData['message']);
    }

    public function testExecuteInTransactionWithComplexOperation(): void
    {
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');

        $counter = 0;
        $operation = function() use (&$counter) {
            $counter++;
            return ['operations_count' => $counter, 'status' => 'completed'];
        };

        $result = $this->transactionService->executeInTransaction($operation);

        $this->assertEquals(['operations_count' => 1, 'status' => 'completed'], $result);
        $this->assertEquals(1, $counter);
    }

    public function testExecuteAndRespondWithArrayResult(): void
    {
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');

        $operation = function() {
            return ['result' => null];
        };

        $response = $this->transactionService->executeAndRespond($operation);

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals(['result' => null], $responseData['data']);
    }

    public function testExecuteAndRespondWithEmptyArrayResult(): void
    {
        $this->entityManager->expects($this->once())
            ->method('beginTransaction');
        
        $this->entityManager->expects($this->once())
            ->method('commit');

        $operation = function() {
            return [];
        };

        $response = $this->transactionService->executeAndRespond($operation);

        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals([], $responseData['data']);
    }
}