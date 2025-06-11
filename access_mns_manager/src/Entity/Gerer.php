<?php

namespace App\Entity;

use App\Repository\GererRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GererRepository::class)]
class Gerer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'manageur')]
    private ?User $manageur = null;

    #[ORM\ManyToOne(inversedBy: 'employe')]
    private ?User $employe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManageur(): ?User
    {
        return $this->manageur;
    }

    public function setManageur(?User $manageur): static
    {
        $this->manageur = $manageur;

        return $this;
    }

    public function getEmploye(): ?User
    {
        return $this->employe;
    }

    public function setEmploye(?User $employe): static
    {
        $this->employe = $employe;

        return $this;
    }
}
