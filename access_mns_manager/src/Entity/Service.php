<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_service = null;

    #[ORM\Column]
    private ?int $niveau_service = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $is_principal = false;

    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    /**
     * @var Collection<int, ServiceZone>
     */
    #[ORM\OneToMany(targetEntity: ServiceZone::class, mappedBy: 'service')]
    private Collection $serviceZones;

    /**
     * @var Collection<int, Travailler>
     */
    #[ORM\OneToMany(targetEntity: Travailler::class, mappedBy: 'service')]
    private Collection $travail;

    public function __construct()
    {
        $this->serviceZones = new ArrayCollection();
        $this->travail = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomService(): ?string
    {
        return $this->nom_service;
    }

    public function setNomService(string $nom_service): static
    {
        $this->nom_service = $nom_service;

        return $this;
    }

    public function getNiveauService(): ?int
    {
        return $this->niveau_service;
    }

    public function setNiveauService(int $niveau_service): static
    {
        $this->niveau_service = $niveau_service;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        $this->organisation = $organisation;

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
            $serviceZone->setService($this);
        }

        return $this;
    }

    public function removeServiceZone(ServiceZone $serviceZone): static
    {
        if ($this->serviceZones->removeElement($serviceZone)) {
            // set the owning side to null (unless already changed)
            if ($serviceZone->getService() === $this) {
                $serviceZone->setService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Travailler>
     */
    public function getTravail(): Collection
    {
        return $this->travail;
    }

    public function addTravail(Travailler $travail): static
    {
        if (!$this->travail->contains($travail)) {
            $this->travail->add($travail);
            $travail->setService($this);
        }

        return $this;
    }

    public function removeTravail(Travailler $travail): static
    {
        if ($this->travail->removeElement($travail)) {
            // set the owning side to null (unless already changed)
            if ($travail->getService() === $this) {
                $travail->setService(null);
            }
        }

        return $this;
    }

    public function isIsPrincipal(): bool
    {
        return $this->is_principal;
    }

    public function setIsPrincipal(bool $is_principal): static
    {
        $this->is_principal = $is_principal;

        return $this;
    }
}
