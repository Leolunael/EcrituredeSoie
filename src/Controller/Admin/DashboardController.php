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

        $stats = [
            'nouveauxAvis' => count($dm->getRepository(Avis::class)->findBy(['approuve' => false])),
            'totalAvisEnAttente' => count($dm->getRepository(Avis::class)->findBy(['approuve' => false])),
        ];

        $visitorStats = $visitorTracker->getStatistics();

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'visitorStats' => $visitorStats,
        ]);
    }
}
