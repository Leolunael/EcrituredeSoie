<?php

namespace App\Entity;

use App\Repository\IntroRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntroRepository::class)]
class Intro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'string')]
    private string $imagePosition = 'droite';

    #[ORM\Column(type: 'boolean')]
    private ?bool $actif = true;

    public function __construct()
    {
        $this->imagePosition = 'droite';
        $this->image = null;
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

}
