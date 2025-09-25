<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la zone est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom de la zone doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom de la zone ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $nom_zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'La description ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif')]
    #[Assert\Range(
        min: 1,
        max: 10000,
        notInRangeMessage: 'La capacité doit être entre {{ min }} et {{ max }} personnes'
    )]
    private ?int $capacite = null;

    /**
     * @var Collection<int, Acces>
     */
    #[ORM\OneToMany(targetEntity: Acces::class, mappedBy: 'zone')]
    private Collection $acces;

    /**
     * @var Collection<int, ServiceZone>
     */
    #[ORM\OneToMany(targetEntity: ServiceZone::class, mappedBy: 'zone')]
    private Collection $serviceZones;

    public function __construct()
    {
        $this->acces = new ArrayCollection();
        $this->serviceZones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomZone(): ?string
    {
        return $this->nom_zone;
    }

    public function setNomZone(string $nom_zone): static
    {
        $this->nom_zone = $nom_zone;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(?int $capacite): static
    {
        $this->capacite = $capacite;

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
            $acce->setZone($this);
        }

        return $this;
    }

    public function removeAcce(Acces $acce): static
    {
        if ($this->acces->removeElement($acce)) {
            // set the owning side to null (unless already changed)
            if ($acce->getZone() === $this) {
                $acce->setZone(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ServiceZone>
     */
    public function getServiceZones(): Collection
    {
        return $this->serviceZones;
    }

    public function addServiceZone(ServiceZone $serviceZone): static
    {
        if (!$this->serviceZones->contains($serviceZone)) {
            $this->serviceZones->add($serviceZone);
            $serviceZone->setZone($this);
        }

        return $this;
    }

    public function removeServiceZone(ServiceZone $serviceZone): static
    {
        if ($this->serviceZones->removeElement($serviceZone)) {
            // set the owning side to null (unless already changed)
            if ($serviceZone->getZone() === $this) {
                $serviceZone->setZone(null);
            }
        }

        return $this;
    }
}
