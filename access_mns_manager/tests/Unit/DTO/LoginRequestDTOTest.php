<?php

namespace App\Tests\Unit\DTO;

use App\DTO\LoginRequestDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginRequestDTOTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidLoginRequest(): void
    {
        $dto = new LoginRequestDTO('test@example.com', 'password123');
        
        $errors = $this->validator->validate($dto);
        
        $this->assertCount(0, $errors);
    }

    public function testEmptyEmailShouldFail(): void
    {
        $dto = new LoginRequestDTO('', 'password123');
        
        $errors = $this->validator->validate($dto);
        
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('L\'email est obligatoire', (string) $errors);
    }

    public function testInvalidEmailFormatShouldFail(): void
    {
        $dto = new LoginRequestDTO('invalid-email', 'password123');
        
        $errors = $this->validator->validate($dto);
        
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('L\'email', (string) $errors);
        $this->assertStringContainsString('n\'est pas valide', (string) $errors);
    }

    public function testEmptyPasswordShouldFail(): void
    {
        $dto = new LoginRequestDTO('test@example.com', '');
        
        $errors = $this->validator->validate($dto);
        
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('Le mot de passe', (string) $errors);
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $dto = LoginRequestDTO::fromArray($data);
        
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
        
        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    public function testFromArrayWithMissingData(): void
    {
        $data = ['email' => 'test@example.com'];
        
        $dto = LoginRequestDTO::fromArray($data);
        
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('', $dto->password);
        
        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
    }
}