<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'avis')]
class Avis
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $nom = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $email;

    #[MongoDB\Field(type: 'int')]
    private ?int $note = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $commentaire = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    #[MongoDB\Field(type: 'bool')]
    private bool $approuve = false;

    // CHAMPS POUR LA RÉPONSE
    #[MongoDB\Field(type: 'string')]
    private ?string $reponse = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateReponse = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $auteurReponse = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    // Getters et Setters
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function isApprouve(): bool
    {
        return $this->approuve;
    }

    public function setApprouve(bool $approuve): self
    {
        $this->approuve = $approuve;
        return $this;
    }

    // GETTERS/SETTERS POUR LA RÉPONSE
    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

    public function setDateReponse(?\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }

    public function getAuteurReponse(): ?string
    {
        return $this->auteurReponse;
    }

    public function setAuteurReponse(?string $auteurReponse): self
    {
        $this->auteurReponse = $auteurReponse;
        return $this;
    }

    public function hasReponse(): bool
    {
        return $this->reponse !== null && $this->reponse !== '';
    }
}
