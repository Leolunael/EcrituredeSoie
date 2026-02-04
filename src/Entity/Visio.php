<?php

namespace App\Entity;

use App\Repository\VisioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Security\UserInterface as AppUserInterface;

#[ORM\Entity(repositoryClass: VisioRepository::class)]
class Visio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $informations = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateVisio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lienHelloAsso = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isArchive = false;

    #[ORM\OneToMany(targetEntity: InscriptionVisio::class, mappedBy: 'visio', orphanRemoval: true)]
    private Collection $inscriptions;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $placesMax = null;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getDateVisio(): ?\DateTimeInterface
    {
        return $this->dateVisio;
    }

    public function setDateVisio(\DateTimeInterface $dateVisio): static
    {
        $this->dateVisio = $dateVisio;
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

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getLienHelloAsso(): ?string
    {
        return $this->lienHelloAsso;
    }

    public function setLienHelloAsso(?string $lienHelloAsso): static
    {
        $this->lienHelloAsso = $lienHelloAsso;
        return $this;
    }

    public function isArchive(): bool
    {
        return $this->isArchive;
    }

    public function setIsArchive(bool $isArchive): static
    {
        $this->isArchive = $isArchive;
        return $this;
    }

    /**
     * @return Collection<int, InscriptionVisio>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(InscriptionVisio $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setVisio($this);
        }
        return $this;
    }

    public function removeInscription(InscriptionVisio $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getVisio() === $this) {
                $inscription->setVisio(null);
            }
        }
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

    // Méthode pour vérifier si c'est complet
    public function isComplet(): bool
    {
        if ($this->placesMax === null) {
            return false;
        }
        return $this->inscriptions->count() >= $this->placesMax;
    }

}
