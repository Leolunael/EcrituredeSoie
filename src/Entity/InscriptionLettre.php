<?php

namespace App\Entity;

use App\Repository\InscriptionLettreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Security\UserInterface;

#[ORM\Entity(repositoryClass: InscriptionLettreRepository::class)]
class InscriptionLettre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $adressePostale = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $moyenPaiement = null;

    // Relation avec Lettre (IMPORTANTE : c'est ça qui permet plusieurs inscriptions)
    #[ORM\ManyToOne(targetEntity: Lettre::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Lettre $lettre = null;

    // Relation optionnelle avec User MySQL (null si c'est un Permanent)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inscriptionsLettres')]
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

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(string $adressePostale): static
    {
        $this->adressePostale = $adressePostale;
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

    public function getMoyenPaiement(): ?string
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement(string $moyenPaiement): static
    {
        $this->moyenPaiement = $moyenPaiement;
        return $this;
    }

    public function getLettre(): ?Lettre
    {
        return $this->lettre;
    }

    public function setLettre(?Lettre $lettre): static
    {
        $this->lettre = $lettre;
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
        return $this->prenom . ' ' . $this->nom;
    }
}
