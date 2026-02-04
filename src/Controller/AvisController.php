<?php

namespace App\Controller;

use App\Document\Avis;
use App\Form\AvisType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(Request $request, DocumentManager $dm): Response
    {
        // Récupérer uniquement les avis approuvés
        $avisList = $dm->getRepository(Avis::class)->findBy(
            ['approuve' => true],
            ['dateCreation' => 'DESC']
        );

        // Formulaire pour soumettre un nouvel avis
        $avis = new Avis();
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // L'avis n'est pas approuvé par défaut (approuve = false)
            $dm->persist($avis);
            $dm->flush();

            $this->addFlash('success', 'Votre avis a été soumis et sera publié après validation. Merci !');
            return $this->redirectToRoute('app_avis');
        }

        return $this->render('avis/index.html.twig', [
            'avisList' => $avisList,
            'form' => $form->createView(),
        ]);
    }
}
