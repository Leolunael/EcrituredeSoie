<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: 'blogs')]
class Blog
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le titre est requis')]
    #[Assert\Length(min: 3, max: 200)]
    private ?string $titre = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le contenu est requis')]
    private ?string $contenu = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateModification = null;

    #[MongoDB\Field(type: 'bool')]
    private bool $aLaUne = false;

    #[MongoDB\Field(type: 'bool')]
    private bool $publie = true;

    #[MongoDB\Field(type: 'string')]
    private ?string $image = null;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private string $imageAlignment = 'right';

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function isALaUne(): bool
    {
        return $this->aLaUne;
    }

    public function setALaUne(bool $aLaUne): self
    {
        $this->aLaUne = $aLaUne;
        return $this;
    }

    public function isPublie(): bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): self
    {
        $this->publie = $publie;
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

    public function getImageAlignment(): ?string
    {
        return $this->imageAlignment;
    }

    public function setImageAlignment(?string $imageAlignment): self
    {
        $this->imageAlignment = $imageAlignment;
        return $this;
    }
}
