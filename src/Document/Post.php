<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: 'posts')]
class Post
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

    #[MongoDB\Field(type: 'string')]
    private ?string $auteurId = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $auteurNom = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateCreation = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $dateModification = null;

    #[MongoDB\Field(type: 'bool')]
    private bool $publie = true;

    #[MongoDB\Field(type: 'string')]
    private ?string $image = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $pdf = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $video = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $audio = null;

    #[MongoDB\EmbedMany(targetDocument: PermanentCom::class)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
        $this->commentaires = new ArrayCollection();
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

    public function setAuteurNom(?string $auteurNom): self
    {
        $this->auteurNom = $auteurNom;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        if (is_array($this->dateCreation)) {
            if (isset($this->dateCreation['date'])) {
                $this->dateCreation = new \DateTime($this->dateCreation['date']);
            } else {
                $this->dateCreation = new \DateTime();
            }
        }

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

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;
        return $this;
    }

    public function getAudio(): ?string
    {
        return $this->audio;
    }

    public function setAudio(?string $audio): self
    {
        $this->audio = $audio;
        return $this;
    }

    public function getCommentaires(): Collection
    {
        if (is_array($this->commentaires)) {
            $cleanCommentaires = [];
            foreach ($this->commentaires as $comment) {
                if (is_array($comment) && isset($comment['dateCreation']) && is_array($comment['dateCreation'])) {
                    continue;
                }
                $cleanCommentaires[] = $comment;
            }
            $this->commentaires = new ArrayCollection($cleanCommentaires);
        }

        if ($this->commentaires === null) {
            $this->commentaires = new ArrayCollection();
        }

        return $this->commentaires;
    }

    public function setCommentaires(collection $commentaires): self
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    public function addCommentaire(PermanentCom $commentaire): self
    {
        $this->commentaires->add($commentaire);
        return $this;
    }

    public function removeCommentaire(PermanentCom $commentaire): self
    {
        $this->commentaires->removeElement($commentaire);
        return $this;
    }

    /**
     * Organise les commentaires en hiérarchie parent-enfant
     * Retourne un tableau avec les commentaires racines et leurs réponses
     */
    public function getCommentairesOrganises(): array
    {
        $organises = [];
        $tousLesCommentaires = $this->getCommentaires()->toArray();

        // Créer un index des commentaires par ID pour un accès rapide
        $index = [];
        foreach ($tousLesCommentaires as $commentaire) {
            $index[$commentaire->getId()] = [
                'commentaire' => $commentaire,
                'reponses' => []
            ];
        }

        // Organiser en hiérarchie
        foreach ($tousLesCommentaires as $commentaire) {
            $parentId = $commentaire->getParentId();

            if (empty($parentId)) {
                // C'est un commentaire racine (pas de parent)
                $organises[$commentaire->getId()] = &$index[$commentaire->getId()];
            } else {
                // C'est une réponse, l'ajouter au parent s'il existe
                if (isset($index[$parentId])) {
                    $index[$parentId]['reponses'][] = $commentaire;
                }
            }
        }

        return $organises;
    }
}
