<?php

namespace App\Controller;

use App\Document\Information;
use App\Entity\Admin;
use App\Entity\Atelier;
use App\Entity\Intro;
use App\Entity\User;
use App\Entity\Visio;
use App\Entity\Vollon;
use App\Entity\Lettre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Document\Texte;
use App\Document\Blog;
use App\Document\Avis;
use Doctrine\ODM\MongoDB\DocumentManager;



final class HomeController extends AbstractController
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

        $atelierALaUne = $em->createQueryBuilder()
            ->select('a')
            ->from(Atelier::class, 'a')
            ->where('a.aLaUne = :aLaUne')
            ->setParameter('aLaUne', true)
            ->setMaxResults(3)
            ->getQuery()
            ->getOneOrNullResult();

        $visioALaUne = $em->createQueryBuilder()
            ->select('a')
            ->from(Visio::class, 'a')
            ->where('a.aLaUne = :aLaUne')
            ->setParameter('aLaUne', true)
            ->setMaxResults(3)
            ->getQuery()
            ->getOneOrNullResult();

        $vollonALaUne = $em->createQueryBuilder()
            ->select('a')
            ->from(Vollon::class, 'a')
            ->where('a.aLaUne = :aLaUne')
            ->setParameter('aLaUne', true)
            ->setMaxResults(3)
            ->getQuery()
            ->getOneOrNullResult();

        $lettreALaUne = $em->createQueryBuilder()
            ->select('a')
            ->from(Lettre::class, 'a')
            ->where('a.aLaUne = :aLaUne')
            ->setParameter('aLaUne', true)
            ->setMaxResults(3)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('home/index.html.twig', [
            'informations' => $informations,
            'controller_name' => 'HomeController',
            'textesALaUne' => $textesALaUne,
            'blogsALaUne' => $blogsALaUne,
            'intros' => $intros,
            'intro' => $intro,
            'atelierALaUne' => $atelierALaUne,
            'visioALaUne'  => $visioALaUne,
            'vollonALaUne' => $vollonALaUne,
            'lettreALaUne' => $lettreALaUne,
        ]);
    }

}
