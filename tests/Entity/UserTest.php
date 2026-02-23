<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    // On teste que setEmail et getEmail fonctionnent correctement
    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('vanessa.dumaillet@free.fr');

        $this->assertEquals('vanessa.dumaillet@free.fr', $user->getEmail());
    }

    // On teste que setPrenom et getPrenom fonctionnent correctement
    public function testSetAndGetPrenom(): void
    {
        $user = new User();
        $user->setPrenom('Vanessa');

        $this->assertEquals('Vanessa', $user->getPrenom());
    }

    // On teste que tout utilisateur a automatiquement ROLE_USER
    // C'est une règle métier importante dans votre application
    public function testRolesParDefautContientRoleUser(): void
    {
        $user = new User();

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    // On teste que getUserIdentifier retourne bien l'email
    // Symfony utilise cette méthode pour identifier l'utilisateur connecté
    public function testGetUserIdentifierRetourneEmail(): void
    {
        $user = new User();
        $user->setEmail('vanessa.dumaillet@free.fr');

        $this->assertEquals('vanessa.dumaillet@free.fr', $user->getUserIdentifier());
    }

    // On teste que isVerified est false par défaut
    // Un nouvel utilisateur ne doit pas être vérifié automatiquement
    public function testIsVerifiedFalseParDefaut(): void
    {
        $user = new User();

        $this->assertFalse($user->isVerified());
    }

    // On teste la méthode isDeleted
    // Un utilisateur sans deletedAt ne doit pas être considéré comme supprimé
    public function testIsDeletedFalseParDefaut(): void
    {
        $user = new User();

        $this->assertFalse($user->isDeleted());
    }

    // On teste la méthode anonymize qui anonymise les données RGPD
    // C'est une règle métier très importante pour la conformité RGPD
    public function testAnonymizeEffaceLesDonneesPersonnelles(): void
    {
        $user = new User();
        $user->setPrenom('vanessa');
        $user->setNom('dumaillet');
        $user->setEmail('vanessa.dumaillet@free.fr');
        $user->setPassword('monmotdepasse');

        $user->anonymize();

        // Après anonymisation, le prénom doit être générique
        $this->assertEquals('Utilisateur', $user->getPrenom());
        // Le nom doit être générique
        $this->assertEquals('Supprimé', $user->getNom());
        // L'utilisateur doit être marqué comme supprimé
        $this->assertTrue($user->isDeleted());
        // L'email ne doit plus être celui d'origine
        $this->assertNotEquals('vanessa.dumaillet@free.fr', $user->getEmail());
    }

    // On teste que setTelephone fonctionne correctement
    public function testSetAndGetTelephone(): void
    {
        $user = new User();
        $user->setTelephone('0612345678');

        $this->assertEquals('0612345678', $user->getTelephone());
    }

    // On teste que le téléphone peut être null (champ optionnel)
    public function testTelephonePeutEtreNull(): void
    {
        $user = new User();

        $this->assertNull($user->getTelephone());
    }
}
