<?php

namespace App\Tests\Controller;

use App\Document\Information;
use App\Entity\Admin;
use App\Entity\Intro;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Document\Texte;
use App\Document\Blog;
use App\Document\Avis;
use Doctrine\ODM\MongoDB\DocumentManager;



final class HomeControllerTest extends WebTestCase
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em, UserPasswordHasherInterface $hasher, DocumentManager $dm): Response
    {

        $intros = $em->getRepository(Intro::class)->findBy(
            ['actif' => true],
            [],
            3
        );

        $intro = !empty($intros) ? $intros[0] : null;

        $informations = $dm->getRepository(Information::class)
            ->createQueryBuilder()
            ->field('actif')->equals(true)
            ->sort('ordre', 'ASC')
            ->limit(3)
            ->getQuery()
            ->execute();

        $textesALaUne = $dm->getRepository(Texte::class)
            ->createQueryBuilder()
            ->field('publie')->equals(true)
            ->field('aLaUne')->equals(true)
            ->sort('dateModification', 'DESC')
            ->limit(3)
            ->getQuery()
            ->execute();

        $blogsALaUne = $dm->getRepository(Blog::class)
            ->createQueryBuilder()
            ->field('aLaUne')->equals(true)
            ->field('publie')->equals(true)
            ->sort('dateModification', 'DESC')
            ->limit(3)
            ->getQuery()
            ->execute();

        $avisALaUne = $dm->getRepository(Avis::class)
            ->createQueryBuilder()
            ->field('valide')->equals(true)
            ->field('aLaUne')->equals(true)
            ->sort('dateModification', 'DESC')
            ->limit(3)
            ->getQuery()
            ->execute();

        return $this->render('home/index.html.twig', [
            'informations' => $informations,
            'controller_name' => 'HomeController',
            'textesALaUne' => $textesALaUne,
            'blogsALaUne' => $blogsALaUne,
            'intros' => $intros,
            'intro' => $intro,
        ]);
    }

}
