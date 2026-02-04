<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: 'informations')]
class Information
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $titre = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $contenu = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $image = null;

    #[MongoDB\Field(type: 'string')]
    private string $imagePosition = 'droite';

    #[MongoDB\Field(type: 'bool')]
    private bool $actif = true;

    #[MongoDB\Field(type: 'int')]
    private int $ordre = 0;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->imagePosition = 'droite';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getImagePosition(): string
    {
        return $this->imagePosition;
    }

    public function setImagePosition(string $imagePosition): self
    {
        $this->imagePosition = $imagePosition;
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

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
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
}
