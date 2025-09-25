<?php

namespace App\Entity;

use App\Repository\AccesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccesRepository::class)]
class Acces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nom d\'accès est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom d\'accès doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom d\'accès ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $nom_acces = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date d\'installation est obligatoire')]
    #[Assert\LessThanOrEqual(
        value: 'now',
        message: 'La date d\'installation ne peut pas être dans le futur'
    )]
    private ?\DateTimeInterface $date_installation = null;

    #[ORM\ManyToOne(inversedBy: 'acces')]
    #[Assert\NotNull(message: 'La zone est obligatoire')]
    private ?Zone $zone = null;

    #[ORM\ManyToOne(inversedBy: 'acces')]
    #[Assert\NotNull(message: 'La badgeuse est obligatoire')]
    private ?Badgeuse $badgeuse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAcces(): ?string
    {
        return $this->nom_acces;
    }

    public function setNomAcces(string $nom_acces): static
    {
        $this->nom_acces = $nom_acces;

        return $this;
    }

    public function getDateInstallation(): ?\DateTimeInterface
    {
        return $this->date_installation;
    }

    public function setDateInstallation(\DateTimeInterface $date_installation): static
    {
        $this->date_installation = $date_installation;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getBadgeuse(): ?Badgeuse
    {
        return $this->badgeuse;
    }

    public function setBadgeuse(?Badgeuse $badgeuse): static
    {
        $this->badgeuse = $badgeuse;

        return $this;
    }
}
