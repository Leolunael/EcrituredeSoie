<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\EmbeddedDocument]
class PermanentCom
{
    #[MongoDB\Field(type: 'string')]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le contenu est requis')]
    #[Assert\Length(min: 2, max: 2900)]
    private ?string $contenu = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $auteurId = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom de l\'auteur est requis')]
    private ?string $auteurNom = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $parentId = null; // Pour les réponses

    public function __construct()
    {
        $this->id = uniqid();
        $this->dateCreation = new \DateTime();
    }

    // Getters et Setters
    public function getId(): ?string
    {
        return $this->id;
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

    public function getAuteurId(): ?string
    {
        return $this->auteurId;
    }

    public function setAuteurId(?string $auteurId): self
    {
        $this->auteurId = $auteurId;
        return $this;
    }

    public function getAuteurNom(): ?string
    {
        return $this->auteurNom;
    }

    public function setAuteurNom(string $auteurNom): self
    {
        $this->auteurNom = $auteurNom;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        // Si c'est un tableau (problème MongoDB), on le convertit
        if (is_array($this->dateCreation)) {
            if (isset($this->dateCreation['date'])) {
                $this->dateCreation = new \DateTime($this->dateCreation['date']);
            } else {
                $this->dateCreation = new \DateTime();
            }
        }

        // Si c'est null, on initialise
        if ($this->dateCreation === null) {
            $this->dateCreation = new \DateTime();
        }

        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): self
    {
        $this->parentId = $parentId;
        return $this;
    }
}
