<?php

namespace App\Tests\Functional\Controller\API\Auth;

use App\Service\Security\LoginAttemptService;
use App\Tests\Shared\DatabaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginValidationTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear rate limiting state for clean tests
        $loginAttemptService = static::getContainer()->get(LoginAttemptService::class);
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $loginAttemptService->resetAttempts($request);
    }

    public function testLoginWithValidationErrors(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'invalid-email',
            'password' => ''
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('VALIDATION_FAILED', $response['error']);
        $this->assertArrayHasKey('details', $response);
        $this->assertIsArray($response['details']);
        $this->assertGreaterThan(0, count($response['details']));
    }

    public function testLoginWithInvalidEmailFormat(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'not-an-email',
            'password' => 'somepassword'
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('VALIDATION_FAILED', $response['error']);
        $this->assertTrue(
            in_array('L\'email not-an-email n\'est pas valide', $response['details']) ||
            in_array('L\'email "not-an-email" n\'est pas valide', $response['details']),
            'Expected validation error message not found: ' . json_encode($response['details'])
        );
    }

    public function testLoginWithEmptyEmail(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => '',
            'password' => 'somepassword'
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('VALIDATION_FAILED', $response['error']);
        $this->assertContains('L\'email est obligatoire', $response['details']);
    }

    public function testLoginWithEmptyPassword(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => ''
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('VALIDATION_FAILED', $response['error']);
        $this->assertContains('Le mot de passe est obligatoire', $response['details']);
    }

    public function testLoginWithMissingFields(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com'
            // Missing password field
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('VALIDATION_FAILED', $response['error']);
        $this->assertContains('Le mot de passe est obligatoire', $response['details']);
    }

    public function testLoginWithInvalidJson(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], 'invalid json');

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('INVALID_JSON', $response['error']);
        $this->assertEquals('Format JSON invalide', $response['message']);
    }

    public function testLoginWithValidFormatButWrongCredentials(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'validpassword123'
        ]));

        $this->assertResponseStatusCodeSame(401);
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('INVALID_CREDENTIALS', $response['error']);
        $this->assertEquals('Identifiants invalides', $response['message']);
    }

    public function testLoginWithValidCredentialsAfterValidation(): void
    {
        $this->client->request('POST', '/manager/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'TestUser123!'
        ]));

        $this->assertResponseIsSuccessful();
        
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('token', $response['data']);
    }
}