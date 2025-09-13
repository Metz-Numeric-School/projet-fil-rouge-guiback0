<?php

namespace App\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserConstraintsTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testEmailConstraints(): void
    {
        // Test valid email
        $errors = $this->validator->validate('test@example.com', [
            new Assert\NotBlank(message: 'L\'email est obligatoire'),
            new Assert\Email(message: 'L\'email {{ value }} n\'est pas valide', mode: 'strict'),
            new Assert\Length(min: 5, max: 180)
        ]);
        $this->assertCount(0, $errors);

        // Test empty email
        $errors = $this->validator->validate('', [
            new Assert\NotBlank(message: 'L\'email est obligatoire'),
            new Assert\Email(message: 'L\'email {{ value }} n\'est pas valide', mode: 'strict')
        ]);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('L\'email est obligatoire', (string) $errors);

        // Test invalid email format
        $errors = $this->validator->validate('invalid-email', [
            new Assert\Email(message: 'L\'email {{ value }} n\'est pas valide', mode: 'strict')
        ]);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('n\'est pas valide', (string) $errors);
    }

    public function testPasswordConstraints(): void
    {
        // Test valid password (hashed)
        $errors = $this->validator->validate('$2y$13$hashedPassword', [
            new Assert\NotBlank(message: 'Le mot de passe est obligatoire')
        ]);
        $this->assertCount(0, $errors);

        // Test empty password
        $errors = $this->validator->validate('', [
            new Assert\NotBlank(message: 'Le mot de passe est obligatoire')
        ]);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('Le mot de passe est obligatoire', (string) $errors);
    }

    public function testRawPasswordConstraints(): void
    {
        // Test valid raw password
        $errors = $this->validator->validate('ValidPassword123!', [
            new Assert\Length(min: 8),
            new Assert\Regex(
                pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial'
            )
        ]);
        $this->assertCount(0, $errors);

        // Test short password
        $errors = $this->validator->validate('short', [
            new Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères')
        ]);
        $this->assertGreaterThan(0, count($errors));

        // Test weak password
        $errors = $this->validator->validate('password', [
            new Assert\Regex(
                pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial'
            )
        ]);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameConstraints(): void
    {
        // Test valid name
        $errors = $this->validator->validate('Dupont', [
            new Assert\NotBlank(message: 'Le nom est obligatoire'),
            new Assert\Length(min: 2, max: 255),
            new Assert\Regex(
                pattern: '/^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/',
                message: 'Le nom ne peut contenir que des lettres, espaces, apostrophes et tirets'
            )
        ]);
        $this->assertCount(0, $errors);

        // Test valid French name with accents
        $errors = $this->validator->validate('Dûpont-Léger', [
            new Assert\Regex(
                pattern: '/^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/',
                message: 'Le nom ne peut contenir que des lettres, espaces, apostrophes et tirets'
            )
        ]);
        $this->assertCount(0, $errors);

        // Test empty name
        $errors = $this->validator->validate('', [
            new Assert\NotBlank(message: 'Le nom est obligatoire')
        ]);
        $this->assertGreaterThan(0, count($errors));

        // Test invalid characters
        $errors = $this->validator->validate('Dupont123', [
            new Assert\Regex(
                pattern: '/^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/',
                message: 'Le nom ne peut contenir que des lettres, espaces, apostrophes et tirets'
            )
        ]);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testPhoneConstraints(): void
    {
        $validPhones = ['0123456789', '+33123456789'];
        $invalidPhones = ['123456', '0023456789', '+44123456789', 'abc123'];

        foreach ($validPhones as $phone) {
            $errors = $this->validator->validate($phone, [
                new Assert\Regex(
                    pattern: '/^(\+33|0)[1-9]([0-9]{8})$/',
                    message: 'Le numéro de téléphone doit être au format français valide'
                )
            ]);
            $this->assertCount(0, $errors, "Phone $phone should be valid");
        }

        foreach ($invalidPhones as $phone) {
            $errors = $this->validator->validate($phone, [
                new Assert\Regex(
                    pattern: '/^(\+33|0)[1-9]([0-9]{8})$/',
                    message: 'Le numéro de téléphone doit être au format français valide'
                )
            ]);
            $this->assertGreaterThan(0, count($errors), "Phone $phone should be invalid");
        }
    }

    public function testDateConstraints(): void
    {
        // Test valid birth date
        $validDate = new \DateTime('-30 years');
        $errors = $this->validator->validate($validDate, [
            new Assert\LessThan(value: 'today'),
            new Assert\GreaterThan(value: '-119 years') // Use 119 to be safe
        ]);
        $this->assertCount(0, $errors);

        // Test future date (should fail)
        $futureDate = new \DateTime('+1 day');
        $errors = $this->validator->validate($futureDate, [
            new Assert\LessThan(
                value: 'today',
                message: 'La date de naissance doit être antérieure à aujourd\'hui'
            )
        ]);
        $this->assertGreaterThan(0, count($errors));

        // Test too old date (should fail)
        $tooOldDate = new \DateTime('-130 years');
        $errors = $this->validator->validate($tooOldDate, [
            new Assert\GreaterThan(
                value: '-120 years',
                message: 'La date de naissance ne peut pas être antérieure à 120 ans'
            )
        ]);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testRangeConstraints(): void
    {
        // Test valid working days
        $errors = $this->validator->validate(5, [
            new Assert\Range(min: 1, max: 7)
        ]);
        $this->assertCount(0, $errors);

        // Test invalid working days
        $errors = $this->validator->validate(8, [
            new Assert\Range(
                min: 1, 
                max: 7, 
                notInRangeMessage: 'Le nombre de jours doit être entre {{ min }} et {{ max }}'
            )
        ]);
        $this->assertGreaterThan(0, count($errors));
    }
}