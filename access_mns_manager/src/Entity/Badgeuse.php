<?php

namespace App\Entity;

use App\Repository\BadgeuseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadgeuseRepository::class)]
class Badgeuse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_installation = null;

    /**
     * @var Collection<int, Acces>
     */
    #[ORM\OneToMany(targetEntity: Acces::class, mappedBy: 'badgeuse')]
    private Collection $acces;

    public function __construct()
    {
        $this->acces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    /**
     * @return Collection<int, Acces>
     */
    public function getAcces(): Collection
    {
        return $this->acces;
    }

    public function addAcce(Acces $acce): static
    {
        if (!$this->acces->contains($acce)) {
            $this->acces->add($acce);
            $acce->setBadgeuse($this);
        }

        return $this;
    }

    public function removeAcce(Acces $acce): static
    {
        if ($this->acces->removeElement($acce)) {
            // set the owning side to null (unless already changed)
            if ($acce->getBadgeuse() === $this) {
                $acce->setBadgeuse(null);
            }
        }

        return $this;
    }
}
