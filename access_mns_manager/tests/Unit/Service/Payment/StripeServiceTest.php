<?php

namespace App\Tests\Unit\Service\Payment;

use App\Service\Payment\StripeService;
use PHPUnit\Framework\TestCase;

class StripeServiceTest extends TestCase
{
    private StripeService $stripeService;

    protected function setUp(): void
    {
        // Create a test service with a mock API key
        $this->stripeService = new StripeService('sk_test_mock_key');
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(StripeService::class, $this->stripeService);
    }

    public function testGetCoffeesMethodExists(): void
    {
        $this->assertTrue(method_exists($this->stripeService, 'getCoffees'));
    }

    public function testGetPriceMethodExists(): void
    {
        $this->assertTrue(method_exists($this->stripeService, 'getPrice'));
    }

    public function testCreateCheckoutSessionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->stripeService, 'createCheckoutSession'));
    }

    public function testVerifySessionMethodExists(): void
    {
        $this->assertTrue(method_exists($this->stripeService, 'verifySession'));
    }

    public function testGetPriceWithInvalidId(): void
    {
        // Test with an obviously invalid price ID
        $result = $this->stripeService->getPrice('invalid_price_id_that_does_not_exist');
        
        // Should return null for invalid price ID
        $this->assertNull($result);
    }

    public function testCreateCheckoutSessionWithInvalidPriceId(): void
    {
        $result = $this->stripeService->createCheckoutSession(
            'invalid_price_id',
            'https://example.com/success',
            'https://example.com/cancel'
        );
        
        // Should return null for invalid price ID
        $this->assertNull($result);
    }

    public function testVerifySessionWithInvalidSessionId(): void
    {
        // Test with an obviously invalid session ID
        $result = $this->stripeService->verifySession('invalid_session_id_that_does_not_exist');
        
        // Should return null for invalid session ID
        $this->assertNull($result);
    }

    public function testGetCoffeesReturnType(): void
    {
        // This will likely throw an exception due to invalid API key, 
        // but we can test that the method returns an array type
        try {
            $result = $this->stripeService->getCoffees();
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            // Expected with invalid API key - test passes if method exists and throws exception
            $this->assertStringContainsString('api key', strtolower($e->getMessage()));
        }
    }

    public function testCreateCheckoutSessionHandlesExceptions(): void
    {
        // Test that the method properly handles Stripe exceptions
        // With invalid API key and invalid price, should return null
        $result = $this->stripeService->createCheckoutSession(
            'price_invalid',
            'https://example.com/success/',
            'https://example.com/cancel/'
        );
        
        // Should return null when Stripe API calls fail
        $this->assertNull($result);
    }
}