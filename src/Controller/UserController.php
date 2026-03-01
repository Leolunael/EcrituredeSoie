<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function testPageUserRedirigeVersLoginSiNonConnecte(): void
    {
        $client = static::createClient();
        $client->request('GET', '/user');

        $this->assertResponseRedirects();

        // Affichez l'URL réelle pour déboguer
        dump($client->getResponse()->headers->get('Location'));

        $this->assertResponseHeaderSame('Location', '/');
    }
}
