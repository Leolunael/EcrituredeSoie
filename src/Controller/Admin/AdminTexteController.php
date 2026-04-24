<?php

namespace App\Controller\Admin;

use App\Document\Commentaire;
use App\Document\Texte;
use App\Form\TexteType;
use App\Service\ImageResizerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/texte')]
#[IsGranted('ROLE_ADMIN')]
class AdminTexteController extends AbstractController
{
    #[Route('/', name: 'admin_texte')]
    public function index(DocumentManager $dm): Response
    {
        $textes = $dm->getRepository(Texte::class)
            ->createQueryBuilder()
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        // Récupérer tous les commentaires non approuvés en une seule requête
        $commentairesEnAttente = $dm->getRepository(Commentaire::class)
            ->createQueryBuilder()
            ->field('approuve')->equals(false)
            ->getQuery()
            ->execute();

        // Indexer par texteId => nombre de commentaires en attente
        $nbEnAttenteParTexte = [];
        foreach ($commentairesEnAttente as $commentaire) {
            $tid = $commentaire->getTexteId();
            if ($tid) {
                $nbEnAttenteParTexte[$tid] = ($nbEnAttenteParTexte[$tid] ?? 0) + 1;
            }
        }

        return $this->render('admin/texte.html.twig', [
            'textes'              => $textes,
            'nbEnAttenteParTexte' => $nbEnAttenteParTexte,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_texte_nouveau')]
    public function nouveau(Request $request, DocumentManager $dm, ImageResizerService $imageResizer): Response
    {
        $texte = new Texte();
        $form = $this->createForm(TexteType::class, $texte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    $uploadDir = $this->getParameter('textes_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $imageResizer->setUploadDirectory($uploadDir);
                    $fileName = $imageResizer->resize($imageFile);
                    $texte->setImage($fileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/textForm.html.twig', [
                        'form' => $form->createView(),
                        'texte' => $texte,
                    ]);
                }
            }

            if ($texte->isALaUne()) {
                $textesALaUne = $dm->getRepository(Texte::class)
                    ->createQueryBuilder()
                    ->field('aLaUne')->equals(true)
                    ->field('id')->notEqual($texte->getId() ?? 'new')
                    ->sort('dateModification', 'ASC')
                    ->getQuery()
                    ->execute()
                    ->toArray();

                if (count($textesALaUne) >= 3) {
                    $aRetirer = array_slice($textesALaUne, 0, -2);
                    foreach ($aRetirer as $autreTexte) {
                        $autreTexte->setALaUne(false);
                    }
                }
            }

            $dm->persist($texte);
            $dm->flush();

            $this->addFlash('success', 'Le texte a été créé avec succès !');
            return $this->redirectToRoute('admin_texte');
        }

        return $this->render('admin/textForm.html.twig', [
            'form' => $form->createView(),
            'texte' => $texte,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_texte_modifier')]
    public function modifier(string $id, Request $request, DocumentManager $dm, ImageResizerService $imageResizer): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if (!$texte) {
            throw $this->createNotFoundException('Ce texte n\'existe pas.');
        }

        $form = $this->createForm(TexteType::class, $texte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $texte->setDateModification(new \DateTime());

            if ($request->request->get('remove_image')) {
                if ($texte->getImage()) {
                    $oldImagePath = $this->getParameter('textes_images_directory') . '/' . $texte->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $texte->setImage(null);
                }
            }

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    if ($texte->getImage()) {
                        $oldImagePath = $this->getParameter('textes_images_directory') . '/' . $texte->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $uploadDir = $this->getParameter('textes_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $imageResizer->setUploadDirectory($uploadDir);
                    $fileName = $imageResizer->resize($imageFile);
                    $texte->setImage($fileName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/textForm.html.twig', [
                        'form' => $form->createView(),
                        'texte' => $texte,
                    ]);
                }
            }

            if ($texte->isALaUne()) {
                $autresTextes = $dm->getRepository(Texte::class)
                    ->createQueryBuilder()
                    ->field('aLaUne')->equals(true)
                    ->field('id')->notEqual($id)
                    ->getQuery()
                    ->execute();

                foreach ($autresTextes as $autreTexte) {
                    $autreTexte->setALaUne(false);
                }
            }

            $dm->flush();

            $this->addFlash('success', 'Le texte a été modifié avec succès !');
            return $this->redirectToRoute('admin_texte');
        }

        return $this->render('admin/textForm.html.twig', [
            'form' => $form->createView(),
            'texte' => $texte,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_texte_supprimer', methods: ['POST'])]
    public function supprimer(string $id, DocumentManager $dm): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if ($texte) {
            if ($texte->getImage()) {
                $imagePath = $this->getParameter('textes_images_directory').'/'.$texte->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $dm->remove($texte);
            $dm->flush();
            $this->addFlash('success', 'Le texte a été supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_texte');
    }

    #[Route('/{id}/mettre-a-la-une', name: 'app_admin_texte_mettre_a_la_une')]
    public function mettreALaUne(string $id, DocumentManager $dm): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if ($texte) {
            $textesALaUne = $dm->getRepository(Texte::class)
                ->createQueryBuilder()
                ->field('aLaUne')->equals(true)
                ->field('id')->notEqual($id)
                ->sort('dateModification', 'ASC')
                ->getQuery()
                ->execute()
                ->toArray();

            if (count($textesALaUne) >= 3) {
                $textesALaUne[0]->setALaUne(false);
            }

            $texte->setALaUne(true);
            $dm->flush();
            $this->addFlash('success', 'Le texte a été mis à la une !');
        }

        return $this->redirectToRoute('admin_texte');
    }

    #[Route('/commentaires', name: 'app_admin_commentaires')]
    public function commentaires(DocumentManager $dm): Response
    {
        return $this->redirectToRoute('admin_texte');
    }

    #[Route('/{id}/commentaires', name: 'app_admin_commentaires_texte')]
    public function commentairesParTexte(string $id, DocumentManager $dm): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if (!$texte) {
            throw $this->createNotFoundException('Ce texte n\'existe pas.');
        }

        $tousCommentaires = $dm->getRepository(Commentaire::class)
            ->createQueryBuilder()
            ->field('texteId')->equals($id)
            ->sort('dateCreation', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();

        $commentairesPrincipaux = [];
        $reponses = [];
        foreach ($tousCommentaires as $c) {
            if ($c->isReponse()) {
                $reponses[$c->getCommentaireParentId()][] = $c;
            } else {
                $commentairesPrincipaux[] = $c;
            }
        }

        return $this->render('admin/textCommentaire.html.twig', [
            'texte'                  => $texte,
            'commentairesPrincipaux' => $commentairesPrincipaux,
            'reponses'               => $reponses,
            'tousCommentaires'       => $tousCommentaires,
        ]);
    }

    #[Route('/admin/commentaire/{id}/valider', name: 'app_admin_commentaire_valider', methods: ['POST'])]
    public function valider(Commentaire $commentaire, DocumentManager $dm, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('valider_' . $commentaire->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $commentaire->setApprouve(!$commentaire->isApprouve());
        $dm->flush();

        $message = $commentaire->isApprouve()
            ? 'Commentaire validé et publié.'
            : 'Commentaire retiré de la publication.';

        $this->addFlash('success', $message);

        $texteId = $commentaire->getTexteId();
        return $this->redirectToRoute('app_admin_commentaires_texte', ['id' => $texteId]);
    }

    #[Route('/commentaires/{id}/supprimer', name: 'app_admin_commentaire_supprimer', methods: ['POST'])]
    public function supprimerCommentaire(string $id, DocumentManager $dm): Response
    {
        $commentaire = $dm->getRepository(Commentaire::class)->find($id);
        $texteId = $commentaire?->getTexteId();

        if ($commentaire) {
            if (!$commentaire->isReponse()) {
                $reponses = $dm->getRepository(Commentaire::class)
                    ->createQueryBuilder()
                    ->field('commentaireParentId')->equals($id)
                    ->getQuery()
                    ->execute();

                foreach ($reponses as $reponse) {
                    $dm->remove($reponse);
                }
            }

            $dm->remove($commentaire);
            $dm->flush();
            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_commentaires_texte', ['id' => $texteId]);
    }
}
