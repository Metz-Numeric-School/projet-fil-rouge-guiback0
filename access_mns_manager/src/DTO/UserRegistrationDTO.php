<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationDTO
{
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(
        message: 'L\'email {{ value }} n\'est pas valide',
        mode: 'strict'
    )]
    #[Assert\Length(
        min: 5,
        max: 180,
        minMessage: 'L\'email doit contenir au moins {{ limit }} caractères',
        maxMessage: 'L\'email ne peut pas contenir plus de {{ limit }} caractères'
    )]
    public string $email;

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe est obligatoire')]
    #[Assert\IdenticalTo(
        propertyPath: 'password',
        message: 'Les mots de passe doivent être identiques'
    )]
    public string $confirmPassword;

    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas contenir plus de {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/',
        message: 'Le nom ne peut contenir que des lettres, espaces, apostrophes et tirets'
    )]
    public string $nom;

    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas contenir plus de {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZàâäéèêëïîôùûüÿçÀÂÄÉÈÊËÏÎÔÙÛÜŸÇ\s\'-]+$/',
        message: 'Le prénom ne peut contenir que des lettres, espaces, apostrophes et tirets'
    )]
    public string $prenom;

    #[Assert\Date(message: 'La date de naissance doit être une date valide')]
    #[Assert\LessThan(
        value: 'today',
        message: 'La date de naissance doit être antérieure à aujourd\'hui'
    )]
    #[Assert\GreaterThan(
        value: '-120 years',
        message: 'La date de naissance ne peut pas être antérieure à 120 ans'
    )]
    public ?\DateTimeInterface $dateNaissance = null;

    #[Assert\Regex(
        pattern: '/^(\+33|0)[1-9]([0-9]{8})$/',
        message: 'Le numéro de téléphone doit être au format français valide (ex: 0123456789 ou +33123456789)'
    )]
    public ?string $telephone = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Le poste ne peut pas contenir plus de {{ limit }} caractères'
    )]
    public ?string $poste = null;

    public function __construct()
    {
        $this->email = '';
        $this->password = '';
        $this->confirmPassword = '';
        $this->nom = '';
        $this->prenom = '';
    }

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->confirmPassword = $data['confirmPassword'] ?? '';
        $dto->nom = $data['nom'] ?? '';
        $dto->prenom = $data['prenom'] ?? '';
        $dto->telephone = $data['telephone'] ?? null;
        $dto->poste = $data['poste'] ?? null;
        
        if (isset($data['dateNaissance'])) {
            $dto->dateNaissance = new \DateTime($data['dateNaissance']);
        }
        
        return $dto;
    }
}