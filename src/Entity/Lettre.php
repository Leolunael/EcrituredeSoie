<?php

namespace App\Entity;

use App\Repository\LettreRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: LettreRepository::class)]
class Lettre
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

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $lienHelloAsso = null;

    #[ORM\OneToMany(targetEntity: InscriptionLettre::class, mappedBy: 'lettre', orphanRemoval: true)]
    private Collection $inscriptions;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isArchive = false;

    public function __construct()
    {
        $this->isArchive = false;
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

    public function getInformations(): ?string
    {
        return $this->informations;
    }

    public function setInformations(?string $informations): self
    {
        $this->informations = $informations;
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
     * @return Collection<int, InscriptionLettre>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(InscriptionLettre $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setLettre($this);
        }
        return $this;
    }

    public function removeInscription(InscriptionLettre $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getLettre() === $this) {
                $inscription->setLettre(null);
            }
        }
        return $this;
    }

}
