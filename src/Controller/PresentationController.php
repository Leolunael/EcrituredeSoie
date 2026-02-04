<?php

namespace App\Controller;

use App\Repository\PresentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PresentationController extends AbstractController
{
    #[Route('/presentation', name: 'presentation_index', methods: ['GET'])]
    public function index(PresentationRepository $presentationRepository): Response
    {
        $presentations = $presentationRepository->findBy(
            ['actif' => true],
            ['ordre' => 'ASC']
        );

        $groupedPresentations = [];
        foreach ($presentations as $presentation) {
            $titre = $presentation->getTitre();
            if (!isset($groupedPresentations[$titre])) {
                $groupedPresentations[$titre] = [
                    'titre' => $titre,
                    'items' => []
                ];
            }
            $groupedPresentations[$titre]['items'][] = $presentation;
        }

        return $this->render('presentation/index.html.twig', [
            'groupedPresentations' => array_values($groupedPresentations),
        ]);
    }
}
