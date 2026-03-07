<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    // On teste qu'un utilisateur NON connecté est redirigé vers la page de connexion
    // denyAccessUnlessGranted doit bloquer l'accès aux anonymes
    public function testPageUserRedirigeVersLoginSiNonConnecte(): void
    {
        $client = static::createClient();
        $client->request('GET', '/user');

        // Un utilisateur non connecté doit être redirigé (code 302)
        $this->assertResponseRedirects();

        // La redirection doit pointer vers la page de login
        $this->assertResponseHeaderSame('Location', '/login');
    }


}
