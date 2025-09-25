<?php

namespace App\Entity;

use App\Repository\BadgeuseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BadgeuseRepository::class)]
class Badgeuse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La référence de la badgeuse est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'La référence doit contenir au moins {{ limit }} caractères',
        maxMessage: 'La référence ne peut pas contenir plus de {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z0-9_-]+$/',
        message: 'La référence ne peut contenir que des lettres majuscules, chiffres, tirets et underscores'
    )]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date d\'installation est obligatoire')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date d\'installation ne peut pas être dans le futur'
    )]
    private ?\DateTimeInterface $date_installation = null;

    /**
     * @var Collection<int, Acces>
     */
    #[ORM\OneToMany(targetEntity: Acces::class, mappedBy: 'badgeuse')]
    private Collection $acces;

    /**
     * @var Collection<int, Pointage>
     */
    #[ORM\OneToMany(targetEntity: Pointage::class, mappedBy: 'badgeuse')]
    private Collection $pointages;

    public function __construct()
    {
        $this->acces = new ArrayCollection();
        $this->pointages = new ArrayCollection();
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

    /**
     * @return Collection<int, Pointage>
     */
    public function getPointages(): Collection
    {
        return $this->pointages;
    }

    public function addPointage(Pointage $pointage): static
    {
        if (!$this->pointages->contains($pointage)) {
            $this->pointages->add($pointage);
            $pointage->setBadgeuse($this);
        }

        return $this;
    }

    public function removePointage(Pointage $pointage): static
    {
        if ($this->pointages->removeElement($pointage)) {
            // set the owning side to null (unless already changed)
            if ($pointage->getBadgeuse() === $this) {
                $pointage->setBadgeuse(null);
            }
        }

        return $this;
    }
}
