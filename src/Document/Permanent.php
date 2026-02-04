<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Security\UserInterface as AppUserInterface;

#[MongoDB\Document(collection: 'permanents')]
class Permanent implements AppUserInterface, PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id]
    private ?string $id = null; // ✅ CHANGÉ: ?int → ?string pour MongoDB

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $nom = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le prénom est requis')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $prenom = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'Email invalide')]
    private ?string $email = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $password = null;

    #[MongoDB\Field(type: 'collection')]
    private array $roles = ['ROLE_PERMANENT'];

    #[MongoDB\Field(type: 'string')]
    private ?string $telephone = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateInscription = null;

    #[MongoDB\Field(type: 'bool')]
    private bool $actif = true;

    #[MongoDB\Field(type: 'bool')]
    private bool $paiementEffectue = false;

    #[MongoDB\Field(type: 'string')]
    private ?string $helloAssoTransactionId = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $datePaiement = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_PERMANENT';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données sensibles temporaires, effacez-les ici
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function isPaiementEffectue(): bool
    {
        return $this->paiementEffectue;
    }

    public function setPaiementEffectue(bool $paiementEffectue): self
    {
        $this->paiementEffectue = $paiementEffectue;
        return $this;
    }

    public function getHelloAssoTransactionId(): ?string
    {
        return $this->helloAssoTransactionId;
    }

    public function setHelloAssoTransactionId(?string $helloAssoTransactionId): self
    {
        $this->helloAssoTransactionId = $helloAssoTransactionId;
        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
