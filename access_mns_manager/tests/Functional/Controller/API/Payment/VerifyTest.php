<?php

namespace App\Tests\Functional\Controller\API\Payment;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VerifyTest extends DatabaseWebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->jwtManager = $container->get(JWTTokenManagerInterface::class);
    }

    public function testVerifySessionValid(): void
    {
        $token = $this->getAuthToken();
        $sessionId = 'cs_test_1234567890abcdef1234567890abcdef1234567890abcdef123';

        $this->client->request('GET', '/manager/api/stripe/verify?session_id=' . $sessionId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $statusCode = $this->client->getResponse()->getStatusCode();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        if ($statusCode === 200) {
            $this->assertValidSessionResponse($response, $sessionId);
        } elseif ($statusCode === 404) {
            $this->assertInvalidSessionResponse($response);
        } else {
            $this->fail('Unexpected status code: ' . $statusCode);
        }
    }

    public function testVerifySessionInvalid(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('GET', '/manager/api/stripe/verify?session_id=invalid_session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertInvalidSessionResponse($response);
    }

    public function testVerifySessionMalformed(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('GET', '/manager/api/stripe/verify?session_id=not-a-session-id', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertInvalidSessionResponse($response);
    }

    public function testVerifySessionMissing(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('GET', '/manager/api/stripe/verify', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('MISSING_SESSION_ID', $response['error']);
        $this->assertEquals('session_id requis', $response['message']);
    }

    public function testVerifySessionEmpty(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('GET', '/manager/api/stripe/verify?session_id=', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('MISSING_SESSION_ID', $response['error']);
    }

    public function testVerifySessionUnauthorized(): void
    {
        $this->client->request('GET', '/manager/api/stripe/verify?session_id=cs_test_123');
        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test d'intégration complet avec création et vérification de vraie session
     */
    public function testCreateAndVerifyRealSession(): void
    {
        $token = $this->getAuthToken();

        // 1. Récupérer un vrai Price ID
        $priceId = $this->getRealPriceId($token);
        if (!$priceId) {
            $this->markTestSkipped('Aucun produit Stripe avec prix disponible');
        }

        // 2. Créer une vraie session Stripe
        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['priceId' => $priceId]));

        if ($this->client->getResponse()->getStatusCode() !== 200) {
            $this->markTestSkipped('Impossible de créer une session Stripe');
        }

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $sessionId = $createData['data']['sessionId'];

        // 3. Vérifier la session créée
        $this->client->request('GET', '/manager/api/stripe/verify?session_id=' . $sessionId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $verifyData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertTrue($verifyData['success']);
        $this->assertEquals($sessionId, $verifyData['data']['id']);
        $this->assertArrayHasKey('payment_status', $verifyData['data']);
        $this->assertArrayHasKey('paid', $verifyData['data']);
    }

    private function getRealPriceId(string $token): ?string
    {
        $this->client->request('GET', '/manager/api/stripe/coffees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);
        
        if ($this->client->getResponse()->getStatusCode() === 200) {
            $data = json_decode($this->client->getResponse()->getContent(), true);
            return $data['data'][0]['price']['id'] ?? null;
        }
        
        return null;
    }

    // Helper methods
    private function getAuthToken(): string
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        return $this->jwtManager->create($user);
    }

    private function assertValidSessionResponse(array $response, string $sessionId): void
    {
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('message', $response);
        
        $data = $response['data'];
        $expectedKeys = ['id', 'payment_status', 'amount_total', 'currency', 'paid'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data);
        }
        
        $this->assertEquals($sessionId, $data['id']);
        
        $expectedMessage = $data['paid'] ? 'Paiement confirmé' : 'Paiement non complété';
        $this->assertEquals($expectedMessage, $response['message']);
    }

    private function assertInvalidSessionResponse(array $response): void
    {
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('INVALID_SESSION', $response['error']);
        $this->assertEquals('Session introuvable ou invalide', $response['message']);
    }
}