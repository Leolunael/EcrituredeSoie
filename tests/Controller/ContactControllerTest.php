<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    // On teste que la page contact s'affiche correctement
    // Le contrôleur doit retourner un code 200
    public function testPageContactEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
    }

    // On teste que le formulaire de contact est bien présent sur la page
    // Le template doit contenir un formulaire
    public function testFormulaireContactEstPresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertSelectorExists('form');
    }



    // On teste qu'un formulaire bien rempli envoie l'email et redirige
    // C'est le cas nominal : l'utilisateur remplit tout correctement
    public function testSoumissionFormulaireVideRestePageContact(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        // Soumettre avec des champs vides MAIS un CSRF token valide
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Envoyer')->form();

        $client->submit($form, [
            'contact[name]' => '',
            'contact[email]' => '',
            'contact[subject]' => '',
            'contact[message]' => '',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // On teste que la redirection après soumission mène bien à la page contact
    // Et que le message flash de succès est affiché
    public function testMessageSuccesApresSoumission(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $client->submitForm('Envoyer', [
            'contact[name]'      => 'Marie Dupont',
            'contact[email]'     => 'marie@exemple.fr',
            'contact[telephone]' => '0612345678',
            'contact[subject]'   => 'Question sur les ateliers',
            'contact[message]'   => 'Bonjour, je souhaite avoir des informations.',
        ]);

        // On suit la redirection
        $client->followRedirect();

        // Le message de succès doit apparaître sur la page
        $this->assertSelectorTextContains(
            'body',
            'Votre message a été envoyé avec succès !'
        );
    }
}
