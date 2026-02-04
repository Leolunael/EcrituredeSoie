<?php

namespace App\Controller;

use App\Document\Commentaire;
use App\Document\Texte;
use App\Form\CommentaireType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TexteController extends AbstractController
{
    #[Route('/texte', name: 'texte_index')]
    public function liste(DocumentManager $dm): Response
    {
        $textes = $dm->getRepository(Texte::class)
            ->createQueryBuilder()
            ->field('publie')->equals(true)
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('texte/index.html.twig', [
            'textes' => $textes,
        ]);
    }

    #[Route('/texte/{id}', name: 'app_texte_detail')]
    public function detail(string $id, Request $request, DocumentManager $dm): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if (!$texte || !$texte->isPublie()) {
            throw $this->createNotFoundException('Ce texte n\'existe pas.');
        }

        // Formulaire de commentaire
        $commentaire = new Commentaire();
        $commentaire->setTexteId($id);
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->persist($commentaire);
            $dm->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès !');
            return $this->redirectToRoute('app_texte_detail', ['id' => $id]);
        }

        // Récupérer TOUS les commentaires approuvés pour ce texte
        $tousLesCommentaires = $dm->getRepository(Commentaire::class)
            ->createQueryBuilder()
            ->field('texteId')->equals($id)
            ->field('approuve')->equals(true)
            ->sort('dateCreation', 'ASC')
            ->getQuery()
            ->execute();

        // Organiser les commentaires de façon récursive
        $commentairesOrganises = $this->organiserCommentairesRecursifs($tousLesCommentaires);

        return $this->render('texte/detail.html.twig', [
            'texte' => $texte,
            'form' => $form->createView(),
            'commentairesOrganises' => $commentairesOrganises,
        ]);
    }

    /**
     * Organise les commentaires de façon récursive pour gérer plusieurs niveaux
     */
    private function organiserCommentairesRecursifs($commentaires): array
    {
        // Créer un tableau indexé par ID pour accès rapide
        $commentairesParId = [];
        foreach ($commentaires as $commentaire) {
            $commentairesParId[$commentaire->getIdString()] = [
                'commentaire' => $commentaire,
                'reponses' => []
            ];
        }

        // Construire la hiérarchie
        $commentairesRacines = [];
        foreach ($commentaires as $commentaire) {
            $idString = $commentaire->getIdString();
            $parentId = $commentaire->getCommentaireParentId();

            if ($parentId === null) {
                // C'est un commentaire racine (sans parent)
                $commentairesRacines[] = &$commentairesParId[$idString];
            } else {
                // C'est une réponse, l'ajouter aux réponses de son parent
                if (isset($commentairesParId[$parentId])) {
                    $commentairesParId[$parentId]['reponses'][] = &$commentairesParId[$idString];
                }
            }
        }

        return $commentairesRacines;
    }

    #[Route('/texte/{texteId}/repondre/{commentaireId}', name: 'app_commentaire_repondre', methods: ['POST'])]
    public function repondre(string $texteId, string $commentaireId, Request $request, DocumentManager $dm): Response
    {
        $contenu = $request->request->get('contenu');
        $auteur = $request->request->get('auteur');

        if (empty($contenu) || empty($auteur)) {
            $this->addFlash('error', 'Tous les champs sont requis.');
            return $this->redirectToRoute('app_texte_detail', ['id' => $texteId]);
        }

        $reponse = new Commentaire();
        $reponse->setTexteId($texteId);
        $reponse->setCommentaireParentId($commentaireId);
        $reponse->setAuteur($auteur);
        $reponse->setContenu($contenu);

        $dm->persist($reponse);
        $dm->flush();

        $this->addFlash('success', 'Votre réponse a été ajoutée avec succès !');
        return $this->redirectToRoute('app_texte_detail', ['id' => $texteId]);
    }
}
