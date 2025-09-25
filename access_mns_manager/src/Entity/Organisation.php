<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'organisation est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom de l\'organisation doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom de l\'organisation ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $nom_organisation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(\+33|0)[1-9]([0-9]{8})$/',
        message: 'Le numéro de téléphone doit être au format français valide'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(
        message: 'L\'email {{ value }} n\'est pas valide',
        mode: 'strict'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'L\'URL du site web n\'est pas valide')]
    private ?string $site_web = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date de création est obligatoire')]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(length: 14, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[0-9]{14}$/',
        message: 'Le numéro SIRET doit contenir exactement 14 chiffres'
    )]
    private ?string $siret = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le chiffre d\'affaires doit être positif ou zéro')]
    private ?float $ca = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le numéro de rue doit être positif')]
    private ?int $numero_rue = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Le suffixe de rue ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $suffix_rue = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de rue est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom de rue doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom de rue ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $nom_rue = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[0-9]{5}$/',
        message: 'Le code postal doit contenir exactement 5 chiffres'
    )]
    private ?string $code_postal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom de ville doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom de ville ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'organisation', orphanRemoval: true)]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->date_creation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomOrganisation(): ?string
    {
        return $this->nom_organisation;
    }

    public function setNomOrganisation(string $nom_organisation): static
    {
        $this->nom_organisation = $nom_organisation;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteWeb(?string $site_web): static
    {
        $this->site_web = $site_web;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCa(): ?float
    {
        return $this->ca;
    }

    public function setCa(?float $ca): static
    {
        $this->ca = $ca;

        return $this;
    }

    public function getNumeroRue(): ?int
    {
        return $this->numero_rue;
    }

    public function setNumeroRue(?int $numero_rue): static
    {
        $this->numero_rue = $numero_rue;

        return $this;
    }

    public function getSuffixRue(): ?string
    {
        return $this->suffix_rue;
    }

    public function setSuffixRue(?string $suffix_rue): static
    {
        $this->suffix_rue = $suffix_rue;

        return $this;
    }

    public function getNomRue(): ?string
    {
        return $this->nom_rue;
    }

    public function setNomRue(string $nom_rue): static
    {
        $this->nom_rue = $nom_rue;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setOrganisation($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getOrganisation() === $this) {
                $service->setOrganisation(null);
            }
        }

        return $this;
    }
}
