<?php

namespace App\Controller\Admin;

use App\Repository\PermanentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        PermanentRepository $permanentRepository,
        PostRepository $postRepository
    ): Response {
        $admin = $this->getUser();

        // Statistiques des permanents
        $statsPermaments = [
            'total' => $permanentRepository->countTotal(),
            'actifs' => $permanentRepository->countTotal() - $permanentRepository->countArchives(),
            'archives' => $permanentRepository->countArchives(),
            'paiementsEnAttente' => $permanentRepository->countPaiementsEnAttente(),
        ];

        // Statistiques des posts
        $allPosts = $postRepository->findAll();
        $totalCommentaires = 0;
        foreach ($allPosts as $post) {
            $totalCommentaires += count($post->getCommentaires());
        }

        $statsPosts = [
            'total' => count($allPosts),
            'commentaires' => $totalCommentaires,
        ];

        // Dernières inscriptions
        $recentInscriptions = $permanentRepository->findRecentInscriptions(5);

        // Posts récents
        $recentPosts = $postRepository->findBy([], ['dateCreation' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'admin' => $admin,
            'statsPermanents' => $statsPermaments,
            'statsPosts' => $statsPosts,
            'recentInscriptions' => $recentInscriptions,
            'recentPosts' => $recentPosts,
        ]);
    }
}
