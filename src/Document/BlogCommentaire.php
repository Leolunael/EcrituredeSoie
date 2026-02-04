<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: 'blogcommentaires')]
class BlogCommentaire
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le blog ID est requis')]
    private ?string $blogId = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $auteur = null;

    #[MongoDB\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le commentaire est requis')]
    #[Assert\Length(min: 2, max: 2000)]
    private ?string $contenu = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $commentaireParentId = null;

    #[MongoDB\Field(type: 'bool')]
    private bool $approuve = true;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBlogId(): ?string
    {
        return $this->blogId;
    }

    public function setBlogId(string $blogId): self
    {
        $this->blogId = $blogId;
        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(string $auteur): self
    {
        $this->auteur = $auteur;
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

    public function getCommentaireParentId(): ?string
    {
        return $this->commentaireParentId;
    }

    public function setCommentaireParentId(?string $commentaireParentId): self
    {
        $this->commentaireParentId = $commentaireParentId;
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

    public function isReponse(): bool
    {
        return $this->commentaireParentId !== null;
    }

    // NOUVELLE MÉTHODE pour obtenir l'ID sous forme de chaîne
    public function getIdString(): string
    {
        return (string) $this->id;
    }
}
