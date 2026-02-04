<?php

namespace App\Entity;

use App\Repository\InscriptionAtelierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Security\UserInterface;

#[ORM\Entity(repositoryClass: InscriptionAtelierRepository::class)]
class InscriptionAtelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Type d'utilisateur: 'user', 'permanent', 'admin'
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $userType = null;

    // ID externe pour les permanents/admins (MongoDB, etc.)
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $externalUserId = null;

    // Données dupliquées pour faciliter l'affichage (dénormalisation)
    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $moyenPaiement = null;

    // Relation avec Atelier (IMPORTANTE : c'est ça qui permet plusieurs inscriptions)
    #[ORM\ManyToOne(targetEntity: Atelier::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Atelier $atelier = null;

    // Relation optionnelle avec User MySQL (null si c'est un Permanent)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inscriptionsAteliers')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(?string $userType): static
    {
        $this->userType = $userType;
        return $this;
    }

    public function getExternalUserId(): ?string
    {
        return $this->externalUserId;
    }

    public function setExternalUserId(?string $externalUserId): static
    {
        $this->externalUserId = $externalUserId;
        return $this;
    }

    /**
     * Récupère l'ID de l'utilisateur (MySQL User.id ou MongoDB Permanent.id)
     */
    public function getUserId(): ?string
    {
        if ($this->user !== null) {
            return (string) $this->user->getId();
        }
        return $this->externalUserId;
    }

    /**
     * Méthode helper pour setter n'importe quel type d'utilisateur
     * Accepte User (MySQL) ou Permanent (MongoDB)
     */
    public function setUserFromInterface(?UserInterface $userInterface): static
    {
        if ($userInterface === null) {
            $this->user = null;
            $this->externalUserId = null;
            $this->userType = null;
            return $this;
        }

        // Déterminer le type d'utilisateur
        if ($userInterface instanceof \App\Entity\User) {
            $this->userType = 'user';
            $this->user = $userInterface; // Relation MySQL
            $this->externalUserId = null;
        } elseif ($userInterface instanceof \App\Document\Permanent) {
            $this->userType = 'permanent';
            $this->user = null; // Pas de relation MySQL
            $this->externalUserId = (string) $userInterface->getId();
        } elseif ($userInterface instanceof \App\Entity\Admin) {
            $this->userType = 'admin';
            $this->user = null;
            $this->externalUserId = (string) $userInterface->getId();
        } else {
            $this->userType = 'unknown';
        }

        // Copier les données pour l'affichage
        $this->name = $userInterface->getNom();
        $this->prenom = $userInterface->getPrenom();
        $this->email = $userInterface->getEmail();

        // Telephone si disponible
        if (method_exists($userInterface, 'getTelephone')) {
            $this->telephone = $userInterface->getTelephone();
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
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
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getMoyenPaiement(): ?string
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement(?string $moyenPaiement): static
    {
        $this->moyenPaiement = $moyenPaiement;
        return $this;
    }

    public function getAtelier(): ?Atelier
    {
        return $this->atelier;
    }

    public function setAtelier(?Atelier $atelier): static
    {
        $this->atelier = $atelier;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Vérifie si l'inscription appartient à un utilisateur permanent
     */
    public function isPermanent(): bool
    {
        return $this->userType === 'permanent';
    }

    /**
     * Récupère le nom complet pour l'affichage
     */
    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->name;
    }
}
