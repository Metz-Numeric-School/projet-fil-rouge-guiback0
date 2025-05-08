<?php

namespace App\Entity;

use App\Repository\AccesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccesRepository::class)]
class Acces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numero_badgeuse = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_installation = null;

    #[ORM\ManyToOne(inversedBy: 'acces')]
    private ?zone $zone = null;

    #[ORM\ManyToOne(inversedBy: 'acces')]
    private ?Badgeuse $badgeuse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroBadgeuse(): ?int
    {
        return $this->numero_badgeuse;
    }

    public function setNumeroBadgeuse(int $numero_badgeuse): static
    {
        $this->numero_badgeuse = $numero_badgeuse;

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

    public function getZone(): ?zone
    {
        return $this->zone;
    }

    public function setZone(?zone $zone): static
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
