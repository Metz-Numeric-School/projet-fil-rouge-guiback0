<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_inscription = null;


    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $horraire = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heure_debut = null;

    #[ORM\Column(nullable: true)]
    private ?int $jours_semaine_travaille = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_derniere_connexion = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_derniere_modification = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $compte_actif = true;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_suppression_prevue = null;

    /**
     * @var Collection<int, UserBadge>
     */
    #[ORM\OneToMany(targetEntity: UserBadge::class, mappedBy: 'Utilisateur')]
    private Collection $userBadges;

    /**
     * @var Collection<int, Travailler>
     */
    #[ORM\OneToMany(targetEntity: Travailler::class, mappedBy: 'Utilisateur')]
    private Collection $travail;

    /**
     * @var Collection<int, Gerer>
     */
    #[ORM\OneToMany(targetEntity: Gerer::class, mappedBy: 'manageur')]
    private Collection $manageur;

    /**
     * @var Collection<int, Gerer>
     */
    #[ORM\OneToMany(targetEntity: Gerer::class, mappedBy: 'employe')]
    private Collection $employe;

    public function __construct()
    {
        $this->userBadges = new ArrayCollection();
        $this->travail = new ArrayCollection();
        $this->manageur = new ArrayCollection();
        $this->employe = new ArrayCollection();
        $this->date_inscription = new \DateTime();
        $this->date_derniere_modification = new \DateTime();
        $this->compte_actif = true;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_ADMIN';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->date_naissance;
    }

    public function setDateNaissance(?\DateTimeInterface $date_naissance): static
    {
        $this->date_naissance = $date_naissance;

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

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTimeInterface $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }


    public function getHorraire(): ?\DateTimeInterface
    {
        return $this->horraire;
    }

    public function setHorraire(?\DateTimeInterface $horraire): static
    {
        $this->horraire = $horraire;

        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(?\DateTimeInterface $heure_debut): static
    {
        $this->heure_debut = $heure_debut;

        return $this;
    }

    public function getJoursSemaineTravaille(): ?int
    {
        return $this->jours_semaine_travaille;
    }

    public function setJoursSemaineTravaille(?int $jours_semaine_travaille): static
    {
        $this->jours_semaine_travaille = $jours_semaine_travaille;

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): static
    {
        $this->poste = $poste;

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
            $userBadge->setUtilisateur($this);
        }

        return $this;
    }

    public function removeUserBadge(UserBadge $userBadge): static
    {
        if ($this->userBadges->removeElement($userBadge)) {
            // set the owning side to null (unless already changed)
            if ($userBadge->getUtilisateur() === $this) {
                $userBadge->setUtilisateur(null);
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
            $travail->setUtilisateur($this);
        }

        return $this;
    }

    public function removeTravail(Travailler $travail): static
    {
        if ($this->travail->removeElement($travail)) {
            // set the owning side to null (unless already changed)
            if ($travail->getUtilisateur() === $this) {
                $travail->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Gerer>
     */
    public function getManageur(): Collection
    {
        return $this->manageur;
    }

    public function addManageur(Gerer $manageur): static
    {
        if (!$this->manageur->contains($manageur)) {
            $this->manageur->add($manageur);
            $manageur->setManageur($this);
        }

        return $this;
    }

    public function removeManageur(Gerer $manageur): static
    {
        if ($this->manageur->removeElement($manageur)) {
            // set the owning side to null (unless already changed)
            if ($manageur->getManageur() === $this) {
                $manageur->setManageur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Gerer>
     */
    public function getEmploye(): Collection
    {
        return $this->employe;
    }

    public function addEmploye(Gerer $employe): static
    {
        if (!$this->employe->contains($employe)) {
            $this->employe->add($employe);
            $employe->setEmploye($this);
        }

        return $this;
    }

    public function removeEmploye(Gerer $employe): static
    {
        if ($this->employe->removeElement($employe)) {
            // set the owning side to null (unless already changed)
            if ($employe->getEmploye() === $this) {
                $employe->setEmploye(null);
            }
        }

        return $this;
    }

    public function getPrincipalService(): ?Service
    {
        foreach ($this->travail as $travail) {
            if ($travail->isIsPrincipal()) {
                return $travail->getService();
            }
        }
        return null;
    }

    public function getPrincipalTravail(): ?Travailler
    {
        foreach ($this->travail as $travail) {
            if ($travail->isIsPrincipal()) {
                return $travail;
            }
        }
        return null;
    }

    public function getSecondaryServices(): array
    {
        $services = [];
        foreach ($this->travail as $travail) {
            if (!$travail->isIsPrincipal() && $travail->getService()) {
                $services[] = $travail->getService();
            }
        }
        return $services;
    }

    public function getDateDerniereConnexion(): ?\DateTimeInterface
    {
        return $this->date_derniere_connexion;
    }

    public function setDateDerniereConnexion(?\DateTimeInterface $date_derniere_connexion): static
    {
        $this->date_derniere_connexion = $date_derniere_connexion;
        return $this;
    }

    public function getDateDerniereModification(): ?\DateTimeInterface
    {
        return $this->date_derniere_modification;
    }

    public function setDateDerniereModification(?\DateTimeInterface $date_derniere_modification): static
    {
        $this->date_derniere_modification = $date_derniere_modification;
        return $this;
    }

    public function isCompteActif(): bool
    {
        return $this->compte_actif;
    }

    public function setCompteActif(bool $compte_actif): static
    {
        $this->compte_actif = $compte_actif;
        if (!$compte_actif && !$this->date_suppression_prevue) {
            // Set deletion date to 5 years from now when deactivating
            $this->date_suppression_prevue = (new \DateTime())->add(new \DateInterval('P5Y'));
        }
        return $this;
    }

    public function getDateSuppressionPrevue(): ?\DateTimeInterface
    {
        return $this->date_suppression_prevue;
    }

    public function setDateSuppressionPrevue(?\DateTimeInterface $date_suppression_prevue): static
    {
        $this->date_suppression_prevue = $date_suppression_prevue;
        return $this;
    }

    /**
     * Check if user should be permanently deleted (5 years after deactivation)
     */
    public function shouldBeDeleted(): bool
    {
        return !$this->compte_actif && 
               $this->date_suppression_prevue && 
               $this->date_suppression_prevue <= new \DateTime();
    }

    /**
     * Deactivate user account for GDPR compliance
     */
    public function deactivate(): static
    {
        $this->compte_actif = false;
        $this->date_suppression_prevue = (new \DateTime())->add(new \DateInterval('P5Y'));
        $this->date_derniere_modification = new \DateTime();
        return $this;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): static
    {
        $this->date_derniere_connexion = new \DateTime();
        return $this;
    }

    /**
     * Update last modification timestamp
     */
    public function updateLastModification(): static
    {
        $this->date_derniere_modification = new \DateTime();
        return $this;
    }

}
