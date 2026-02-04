<?php

namespace App\Controller\Admin;

use App\Document\Permanent;
use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Liste des permanents - utilise votre template existant
     */
    #[Route('/permanents', name: 'admin_permanents_index')]
    public function listPermanents(DocumentManager $dm): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $permanents = $dm->getRepository(Permanent::class)->findAll();

        // Utilise votre template existant: templates/admin/permanent/index.html.twig
        return $this->render('admin/permanent/index.html.twig', [
            'permanents' => $permanents,
        ]);
    }

    /**
     * Vue d'ensemble des utilisateurs - utilise votre template existant
     */
    #[Route('/users', name: 'admin_users_index')]
    public function usersOverview(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $em->getRepository(User::class)->findAll();

        $stats = [
            'users' => count($users),
        ];

        // Utilise votre template existant: templates/admin/user/index.html.twig
        return $this->render('admin/user/index.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * Liste détaillée des utilisateurs enregistrés
     * Ce template inclut les boutons de promotion
     */
    #[Route('/users/registered', name: 'admin_users_registered')]
    public function listRegisteredUsers(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $em->getRepository(User::class)->findAll();

        // Utilise le template avec les boutons de promotion
        return $this->render('admin/user/registered.html.twig', [
            'users' => $users,
        ]);
    }
}
