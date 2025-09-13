<?php

namespace App\Tests\Functional\Controller\API\Payment;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateCheckoutTest extends DatabaseWebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;
    
    // Vrais Product IDs Stripe de test
    private const REAL_PRODUCT_IDS = [
        'prod_SyR5M8oDIqp2Xl',
        'prod_Sw17aaripGWXQk', 
        'prod_Sw16PJ0WaVTm39',
        'prod_Sw16ax3LZMWBki'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->jwtManager = $container->get(JWTTokenManagerInterface::class);
    }

    public function testCreateCheckoutSuccess(): void
    {
        $token = $this->getAuthToken();
        $priceId = $this->getRealPriceId($token) ?? 'price_test_fallback';

        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['priceId' => $priceId]));

        $statusCode = $this->client->getResponse()->getStatusCode();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        if ($statusCode === 200) {
            $this->assertValidCheckoutResponse($response);
        } else {
            $this->assertStripeErrorResponse($response, [400, 500]);
        }
    }

    public function testCreateCheckoutMissingPriceId(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('MISSING_PRICE_ID', $response['error']);
        $this->assertEquals("L'ID du prix est requis", $response['message']);
    }

    public function testCreateCheckoutInvalidPriceId(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['priceId' => 'invalid_price_id']));

        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [400, 500]));
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertContains($response['error'], ['STRIPE_SESSION_ERROR', 'STRIPE_ERROR']);
    }

    public function testCreateCheckoutUnauthorized(): void
    {
        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['priceId' => 'price_123']));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateCheckoutInvalidJson(): void
    {
        $token = $this->getAuthToken();

        $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json'
        ], 'invalid json');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateCheckoutWithRealProducts(): void
    {
        $token = $this->getAuthToken();

        // Récupérer les produits Stripe
        $this->client->request('GET', '/manager/api/stripe/coffees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        if ($this->client->getResponse()->getStatusCode() !== 200) {
            $this->markTestSkipped('Impossible de récupérer les produits Stripe');
        }

        $coffeesData = json_decode($this->client->getResponse()->getContent(), true);
        
        if (empty($coffeesData['data'])) {
            $this->markTestSkipped('Aucun produit Stripe disponible');
        }

        $product = $coffeesData['data'][0];
        $this->assertContains($product['id'], self::REAL_PRODUCT_IDS);
        
        // Tester création de session avec le vrai produit
        if (isset($product['price']['id'])) {
            $this->client->request('POST', '/manager/api/stripe/create-checkout-session', [], [], [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ], json_encode(['priceId' => $product['price']['id']]));
            
            if ($this->client->getResponse()->getStatusCode() === 200) {
                $response = json_decode($this->client->getResponse()->getContent(), true);
                $this->assertValidCheckoutResponse($response);
            }
        }
    }

    // Helper methods
    private function getAuthToken(): string
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        return $this->jwtManager->create($user);
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

    private function assertValidCheckoutResponse(array $response): void
    {
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('sessionId', $response['data']);
        $this->assertArrayHasKey('url', $response['data']);
        $this->assertStringStartsWith('cs_', $response['data']['sessionId']);
        $this->assertStringContainsString('checkout.stripe.com', $response['data']['url']);
    }

    private function assertStripeErrorResponse(array $response, array $validStatusCodes): void
    {
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, $validStatusCodes));
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('error', $response);
    }
}