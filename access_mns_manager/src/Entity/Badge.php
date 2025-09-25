<?php

namespace App\Entity;

use App\Repository\BadgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le numéro de badge est obligatoire')]
    #[Assert\Positive(message: 'Le numéro de badge doit être positif')]
    private ?int $numero_badge = null;

    /**
     * Type de badge (technologie uniquement : RFID, NFC, MIFARE, etc.)
     * N'INFLUENCE PAS les permissions d'accès aux zones/badgeuses
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type de badge est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le type de badge doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le type de badge ne peut pas contenir plus de {{ limit }} caractères'
    )]
    #[Assert\Choice(
        choices: ['RFID', 'NFC', 'MIFARE', 'HID', 'Prox', 'iCLASS'],
        message: 'Le type de badge doit être: {{ choices }}'
    )]
    private ?string $type_badge = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date de création est obligatoire')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date de création ne peut pas être dans le futur'
    )]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThan(
        propertyPath: 'date_creation',
        message: 'La date d\'expiration doit être postérieure à la date de création'
    )]
    private ?\DateTimeInterface $date_expiration = null;

    /**
     * @var Collection<int, Pointage>
     */
    #[ORM\OneToMany(targetEntity: Pointage::class, mappedBy: 'badge')]
    private Collection $pointages;

    /**
     * @var Collection<int, UserBadge>
     */
    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'badge')]
    private Collection $userBadges;

    public function __construct()
    {
        $this->pointages = new ArrayCollection();
        $this->userBadges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroBadge(): ?int
    {
        return $this->numero_badge;
    }

    public function setNumeroBadge(int $numero_badge): static
    {
        $this->numero_badge = $numero_badge;

        return $this;
    }

    public function getTypeBadge(): ?string
    {
        return $this->type_badge;
    }

    public function setTypeBadge(string $type_badge): static
    {
        $this->type_badge = $type_badge;

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

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(?\DateTimeInterface $date_expiration): static
    {
        $this->date_expiration = $date_expiration;

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
            $pointage->setBadge($this);
        }

        return $this;
    }

    public function removePointage(Pointage $pointage): static
    {
        if ($this->pointages->removeElement($pointage)) {
            // set the owning side to null (unless already changed)
            if ($pointage->getBadge() === $this) {
                $pointage->setBadge(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserBadge>
     */
    public function getUserBadges(): Collection
    {
        return $this->userBadges;
    }

    public function addUserBadge(UserBadge $userBadge): static
    {
        if (!$this->userBadges->contains($userBadge)) {
            $this->userBadges->add($userBadge);
            $userBadge->setBadge($this);
        }

        return $this;
    }

    public function removeUserBadge(UserBadge $userBadge): static
    {
        if ($this->userBadges->removeElement($userBadge)) {
            // set the owning side to null (unless already changed)
            if ($userBadge->getBadge() === $this) {
                $userBadge->setBadge(null);
            }
        }

        return $this;
    }
}
