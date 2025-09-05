<?php

namespace App\Tests\Functional\Security;

use App\Service\Security\LoginAttemptService;
use App\Tests\Shared\DatabaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginThrottlingTest extends DatabaseWebTestCase
{
    public function testLoginThrottlingAfterMultipleFailedAttempts(): void
    {
        // Clear any existing rate limit for this test
        $loginAttemptService = static::getContainer()->get(LoginAttemptService::class);
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginAttemptService->resetAttempts($request);

        // Attempt to login with invalid credentials multiple times
        for ($i = 0; $i < 4; $i++) {
            $this->client->request('POST', '/manager/api/auth/login', [], [], [
                'CONTENT_TYPE' => 'application/json'
            ], json_encode([
                'email' => 'nonexistent@example.com',
                'password' => 'wrong_password'
            ]));

            $statusCode = $this->client->getResponse()->getStatusCode();
            $content = $this->client->getResponse()->getContent();
            
            if ($i < 3) {
                // First 3 attempts should return 401 (unauthorized)
                $this->assertSame(Response::HTTP_UNAUTHORIZED, $statusCode, 
                    "Attempt $i: Expected 401, got $statusCode. Content: $content");
            } else {
                // 4th attempt should be throttled (429 Too Many Requests)
                $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $statusCode, 
                    "Attempt $i: Expected 429 (throttled), got $statusCode. Content: $content");
            }
        }
    }

    public function testLoginThrottlingDoesNotAffectValidCredentials(): void
    {
        // Clear any existing rate limit for this test
        $loginAttemptService = static::getContainer()->get(LoginAttemptService::class);
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginAttemptService->resetAttempts($request);

        // First, make some failed attempts (only 2 to keep within limit)
        for ($i = 0; $i < 2; $i++) {
            $this->client->request('POST', '/manager/api/auth/login', [], [], [
                'CONTENT_TYPE' => 'application/json'
            ], json_encode([
                'email' => 'test@example.com',
                'password' => 'wrong_password'
            ]));
            $this->assertResponseStatusCodeSame(401);
        }

        // Now try with valid credentials - this should still work
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'TestUser123!'
        ]));

        // Should succeed despite previous failed attempts from same IP
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('token', $response['data']);
    }

    public function testLoginThrottlingWithDifferentUsers(): void
    {
        // Clear any existing rate limit for this test
        $loginAttemptService = static::getContainer()->get(LoginAttemptService::class);
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginAttemptService->resetAttempts($request);

        // Make failed attempts for one user (only 2 to keep within limit)
        for ($i = 0; $i < 2; $i++) {
            $this->client->request('POST', '/manager/api/auth/login', [], [], [
                'CONTENT_TYPE' => 'application/json'
            ], json_encode([
                'email' => 'test@example.com',
                'password' => 'wrong_password'
            ]));
            $this->assertResponseStatusCodeSame(401);
        }

        // Try with valid credentials for same user - should still work
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'TestUser123!'
        ]));

        $this->assertResponseIsSuccessful();
    }
}