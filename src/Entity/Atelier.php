<?php

namespace App\Entity;

use App\Repository\AtelierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: AtelierRepository::class)]
class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isArchive = false;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAtelier = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $placesMax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $informations = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lienHelloAsso = null;

    #[ORM\OneToMany(targetEntity: InscriptionAtelier::class, mappedBy: 'atelier', orphanRemoval: true)]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->dateAtelier = new \DateTime();
        $this->isArchive = false;
        $this->inscriptions = new ArrayCollection();
    }

    // Getters et Setters

    public function getId(): ?int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getDateAtelier(): ?\DateTimeInterface
    {
        return $this->dateAtelier;
    }

    public function setDateAtelier(\DateTimeInterface $dateAtelier): self
    {
        $this->dateAtelier = $dateAtelier;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getPlacesMax(): ?int
    {
        return $this->placesMax;
    }

    public function setPlacesMax(?int $placesMax): self
    {
        $this->placesMax = $placesMax;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function isArchive(): bool
    {
        return $this->isArchive;
    }

    public function setIsArchive(bool $isArchive): self
    {
        $this->isArchive = $isArchive;
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

    public function getInformations(): ?string
    {
        return $this->informations;
    }

    public function setInformations(?string $informations): self
    {
        $this->informations = $informations;
        return $this;
    }

    public function getLienHelloAsso(): ?string
    {
        return $this->lienHelloAsso;
    }

    public function setLienHelloAsso(?string $lienHelloAsso): self
    {
        $this->lienHelloAsso = $lienHelloAsso;
        return $this;
    }

    /**
     * @return Collection<int, InscriptionAtelier>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(InscriptionAtelier $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setAtelier($this);
        }
        return $this;
    }

    public function removeInscription(InscriptionAtelier $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getAtelier() === $this) {
                $inscription->setAtelier(null);
            }
        }
        return $this;
    }

    public function isComplet(): bool
    {
        if ($this->placesMax === null) {
            return false;
        }
        return $this->inscriptions->count() >= $this->placesMax;
    }

}
