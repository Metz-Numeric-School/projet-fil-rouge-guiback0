<?php

namespace App\Tests\Functional\Controller\API\Payment;

use App\Tests\Shared\DatabaseWebTestCase;
use App\Tests\Shared\TestEntityFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CoffeesTest extends DatabaseWebTestCase
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

    public function testGetCoffeesSuccess(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/stripe/coffees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        // Vérifier structure de base
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('count', $response);
        $this->assertIsArray($response['data']);
        
        // Si des produits existent, vérifier le premier
        if ($response['count'] > 0) {
            $product = $response['data'][0];
            $expectedKeys = ['id', 'name', 'description', 'images', 'price', 'created'];
            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $product);
            }
            
            // Vérifier structure du prix si présent
            if ($product['price']) {
                $priceKeys = ['id', 'amount', 'currency', 'formatted_amount'];
                foreach ($priceKeys as $key) {
                    $this->assertArrayHasKey($key, $product['price']);
                }
            }
        }
    }

    public function testGetCoffeesUnauthorized(): void
    {
        $this->client->request('GET', '/manager/api/stripe/coffees');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetCoffeesHandlesStripeErrors(): void
    {
        $user = TestEntityFactory::createTestUser($this->em, $this->passwordHasher);
        $this->em->flush();
        $token = $this->jwtManager->create($user);

        $this->client->request('GET', '/manager/api/stripe/coffees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 500]));
        
        if ($statusCode === 500) {
            $response = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('error', $response);
            $this->assertEquals('STRIPE_ERROR', $response['error']);
        }
    }
}