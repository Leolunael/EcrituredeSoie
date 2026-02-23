<?php

namespace App\Tests\Document;

use App\Document\Avis;
use PHPUnit\Framework\TestCase;

class AvisTest extends TestCase
{
    // On teste que approuve est false par défaut
    // Un avis ne doit pas être approuvé automatiquement, l'admin doit valider
    public function testAvisNonApprouveParDefaut(): void
    {
        $avis = new Avis();

        $this->assertFalse($avis->isApprouve());
    }

    // On teste que hasReponse est false par défaut
    // Un nouvel avis n'a pas encore de réponse
    public function testAvisSansReponseParDefaut(): void
    {
        $avis = new Avis();

        $this->assertFalse($avis->hasReponse());
    }

    // On teste que la date de création est automatiquement définie
    // Le constructeur doit initialiser la date
    public function testDateCreationEstDefinie(): void
    {
        $avis = new Avis();

        $this->assertNotNull($avis->getDateCreation());
        $this->assertInstanceOf(\DateTimeInterface::class, $avis->getDateCreation());
    }

    // On teste setNote et getNote
    public function testSetAndGetNote(): void
    {
        $avis = new Avis();
        $avis->setNote(5);

        $this->assertEquals(5, $avis->getNote());
    }

    // On teste setNom et getNom
    public function testSetAndGetNom(): void
    {
        $avis = new Avis();
        $avis->setNom('Marie');

        $this->assertEquals('Marie', $avis->getNom());
    }

    // On teste setCommentaire et getCommentaire
    public function testSetAndGetCommentaire(): void
    {
        $avis = new Avis();
        $avis->setCommentaire('Excellent atelier, je recommande !');

        $this->assertEquals('Excellent atelier, je recommande !', $avis->getCommentaire());
    }

    // On teste que setApprouve fonctionne
    public function testSetApprouve(): void
    {
        $avis = new Avis();
        $avis->setApprouve(true);

        $this->assertTrue($avis->isApprouve());
    }

    // On teste hasReponse quand une réponse est définie
    // C'est la méthode métier la plus importante de cette classe
    public function testHasReponseRetourneTrueQuandReponseDefinie(): void
    {
        $avis = new Avis();
        $avis->setReponse('Merci pour votre avis !');

        $this->assertTrue($avis->hasReponse());
    }

    // On teste hasReponse avec une chaîne vide
    // Une réponse vide ne doit pas compter comme une vraie réponse
    public function testHasReponseRetourneFalseAvecChaineVide(): void
    {
        $avis = new Avis();
        $avis->setReponse('');

        $this->assertFalse($avis->hasReponse());
    }

    // On teste setEmail avec une valeur null (email optionnel)
    public function testEmailPeutEtreNull(): void
    {
        $avis = new Avis();
        $avis->setEmail(null);

        $this->assertNull($avis->getEmail());
    }
}
