<?php

namespace App\Controller\Admin;

use App\Document\Avis;
use App\Document\Texte;
use App\Document\Blog;
use App\Document\Information;
use App\Repository\ContactRepository;
use App\Repository\AtelierRepository;
use App\Repository\PresentationRepository;
use App\Service\VisitorTrackerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(
        ContactRepository $contactRepo,
        AtelierRepository $atelierRepo,
        PresentationRepository $presentationRepo,
        DocumentManager   $dm,
        VisitorTrackerService $visitorTracker,
    ): Response
    {
        // Calculer les statistiques
        $stats = [
            'nouveauxAvis' => count($dm->getRepository(Avis::class)->findBy(['approuve' => false])),
            'nouveauxContacts' => $contactRepo->count(['traite' => false]),
            'totalAvisEnAttente' => count($dm->getRepository(Avis::class)->findBy(['approuve' => false])),
            'totalContactsNonTraites' => $contactRepo->count(['traite' => false]),
        ];

        // Derniers éléments pour aperçu rapide (3 au lieu de 5)
        $derniers = [
            'avis' => $dm->getRepository(Avis::class)->findBy(['approuve' => false], ['dateCreation' => 'DESC'], 3),
            'contacts' => $contactRepo->findBy(['traite' => false], ['createdAt' => 'DESC'], 3),
            'ateliers' => $atelierRepo->findBy([], ['id' => 'DESC'], 3),
            'textes' => $dm->getRepository(Texte::class)->findBy([], ['dateCreation' => 'DESC'], 3),
            'blogs' => $dm->getRepository(Blog::class)->findBy([], ['dateCreation' => 'DESC'], 3),
            'presentations' => $presentationRepo->findBy([], ['id' => 'DESC'], 3),
            'informations' => $dm->getRepository(Information::class)
                ->createQueryBuilder()
                ->sort('ordre', 'ASC')
                ->limit(3)
                ->getQuery()
                ->execute()
                ->toArray(),
        ];

        $visitorStats = $visitorTracker->getStatistics();

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'derniers' => $derniers,
            'visitorStats' => $visitorStats,
        ]);
    }
}
