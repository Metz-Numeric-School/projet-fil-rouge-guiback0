<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    /**
     * @var Collection<int, ServiceZone>
     */
    #[ORM\OneToMany(targetEntity: ServiceZone::class, mappedBy: 'service')]
    private Collection $serviceZones;

    public function __construct()
    {
        $this->serviceZones = new ArrayCollection();
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
}
