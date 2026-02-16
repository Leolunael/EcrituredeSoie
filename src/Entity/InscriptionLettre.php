<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Admin;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class InscriptionLettre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ✅ Relation avec User
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    // ✅ Relation avec Admin
    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Admin $admin = null;

    // Autres propriétés...
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adressePostale = null;

    // ✅ AJOUTEZ AUSSI CES PROPRIÉTÉS si elles sont utilisées
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $moyenPaiement = null;

    #[ORM\ManyToOne(targetEntity: Lettre::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Lettre $lettre = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): static
    {
        $this->admin = $admin;
        return $this;
    }

    // ✅ Méthode utilitaire pour récupérer User OU Admin
    public function getInscripteur(): User|Admin|null
    {
        return $this->user ?? $this->admin;
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

}
