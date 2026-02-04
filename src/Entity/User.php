<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Security\UserInterface as AppUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements AppUserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

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

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(
        targetEntity: InscriptionAtelier::class,
        mappedBy: 'user'
    )]
    private Collection $inscriptionsAteliers;

    #[ORM\OneToMany(
        targetEntity: InscriptionVisio::class,
        mappedBy: 'user'
    )]
    private Collection $inscriptionsVisios;

    #[ORM\OneToMany(
        targetEntity: InscriptionLettre::class,
        mappedBy: 'user'
    )]
    private Collection $inscriptionsLettres;

    // ✅ NOUVEAU : Indique si cet utilisateur est actuellement un permanent actif
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPermanent = false;

    // ✅ NOUVEAU : Référence vers l'ID MongoDB du Permanent (si existe)
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $mongodbId = null;

    public function __construct()
    {
        $this->inscriptionsAteliers = new ArrayCollection();
        $this->inscriptionsVisios = new ArrayCollection();
        $this->inscriptionsLettres = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
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
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if ($this->isPermanent) {
            $roles[] = 'ROLE_PERMANENT';
        }

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Anonymise les données personnelles de l'utilisateur
     * Conforme RGPD
     */
    public function anonymize(): self
    {
        $this->email = 'deleted_' . $this->id . '_' . uniqid() . '@anonymized.local';
        $this->prenom = 'Utilisateur';
        $this->nom = 'Supprimé';
        $this->password = bin2hex(random_bytes(32));
        $this->isVerified = false;
        $this->roles = [];
        $this->deletedAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, InscriptionAtelier>
     */
    public function getInscriptionsAteliers(): Collection
    {
        return $this->inscriptionsAteliers;
    }

    public function addInscriptionsAtelier(InscriptionAtelier $inscriptionsAtelier): static
    {
        if (!$this->inscriptionsAteliers->contains($inscriptionsAtelier)) {
            $this->inscriptionsAteliers->add($inscriptionsAtelier);
            $inscriptionsAtelier->setUser($this);
        }
        return $this;
    }

    public function removeInscriptionsAtelier(InscriptionAtelier $inscriptionsAtelier): static
    {
        if ($this->inscriptionsAteliers->removeElement($inscriptionsAtelier)) {
            if ($inscriptionsAtelier->getUser() === $this) {
                $inscriptionsAtelier->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, InscriptionVisio>
     */
    public function getInscriptionsVisios(): Collection
    {
        return $this->inscriptionsVisios;
    }

    public function addInscriptionsVisio(InscriptionVisio $inscriptionsVisio): static
    {
        if (!$this->inscriptionsVisios->contains($inscriptionsVisio)) {
            $this->inscriptionsVisios->add($inscriptionsVisio);
            $inscriptionsVisio->setUser($this);
        }
        return $this;
    }

    public function removeInscriptionsVisio(InscriptionVisio $inscriptionsVisio): static
    {
        if ($this->inscriptionsVisios->removeElement($inscriptionsVisio)) {
            if ($inscriptionsVisio->getUser() === $this) {
                $inscriptionsVisio->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, InscriptionLettre>
     */
    public function getInscriptionsLettres(): Collection
    {
        return $this->inscriptionsLettres;
    }

    public function addInscriptionsLettre(InscriptionLettre $inscriptionsLettre): static
    {
        if (!$this->inscriptionsLettres->contains($inscriptionsLettre)) {
            $this->inscriptionsLettres->add($inscriptionsLettre);
            $inscriptionsLettre->setUser($this);
        }
        return $this;
    }

    public function removeInscriptionsLettre(InscriptionLettre $inscriptionsLettre): static
    {
        if ($this->inscriptionsLettres->removeElement($inscriptionsLettre)) {
            if ($inscriptionsLettre->getUser() === $this) {
                $inscriptionsLettre->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Récupère toutes les inscriptions de l'utilisateur
     */
    public function getToutesLesInscriptions(): array
    {
        $inscriptions = [];

        foreach ($this->inscriptionsAteliers as $inscription) {
            $inscriptions[] = [
                'type' => 'Atelier',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        foreach ($this->inscriptionsVisios as $inscription) {
            $inscriptions[] = [
                'type' => 'Visio',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        foreach ($this->inscriptionsLettres as $inscription) {
            $inscriptions[] = [
                'type' => 'Lettre',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        // Trier par date décroissante
        usort($inscriptions, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return $inscriptions;
    }

    public function isPermanent(): bool
    {
        return $this->isPermanent;
    }

    public function setIsPermanent(bool $isPermanent): self
    {
        $this->isPermanent = $isPermanent;
        return $this;
    }

    public function getMongodbId(): ?string
    {
        return $this->mongodbId;
    }

    public function setMongodbId(?string $mongodbId): self
    {
        $this->mongodbId = $mongodbId;
        return $this;
    }
}
