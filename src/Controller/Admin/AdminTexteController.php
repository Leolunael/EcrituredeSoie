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

        return $this->render('admin/Texte.html.twig', [
            'textes' => $textes,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_texte_nouveau')]
    public function nouveau(Request $request, DocumentManager $dm, ImageResizerService $imageResizer): Response
    {
        $texte = new Texte();
        $form = $this->createForm(TexteType::class, $texte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image avec redimensionnement
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    // Définir le répertoire de destination
                    $uploadDir = $this->getParameter('textes_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Configurer le service avec le bon répertoire
                    $imageResizer->setUploadDirectory($uploadDir);

                    // Redimensionner et sauvegarder l'image (max 720x1280)
                    $fileName = $imageResizer->resize($imageFile);
                    $texte->setImage($fileName);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/TextForm.html.twig', [
                        'form' => $form->createView(),
                        'texte' => $texte,
                    ]);
                }
            }

            // Si on met ce texte à la une, retirer les autres
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
//            $contenu = $texte->getContenu();
//            $protection = "\n\nÂ© Tous droits rÃ©servÃ©s. Ce texte est protÃ©gÃ© par le droit d'auteur.";
//            $texte->setContenu($contenu . $auteur . $protection);

            $dm->persist($texte);
            $dm->flush();

            $this->addFlash('success', 'Le texte a été créé avec succès !');
            return $this->redirectToRoute('admin_texte');
        }

        return $this->render('admin/TextForm.html.twig', [
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
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                try {
                    // Supprimer l'ancienne image si elle existe
                    if ($texte->getImage()) {
                        $oldImagePath = $this->getParameter('textes_images_directory') . '/' . $texte->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    // Définir le répertoire de destination
                    $uploadDir = $this->getParameter('textes_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Configurer le service et redimensionner l'image
                    $imageResizer->setUploadDirectory($uploadDir);
                    $fileName = $imageResizer->resize($imageFile);
                    $texte->setImage($fileName);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/TextForm.html.twig', [
                        'form' => $form->createView(),
                        'texte' => $texte,
                    ]);
                }
            }

            // Si on met ce texte à la une, retirer les autres
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

        return $this->render('admin/TextForm.html.twig', [
            'form' => $form->createView(),
            'texte' => $texte,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_texte_supprimer', methods: ['POST'])]
    public function supprimer(string $id, DocumentManager $dm): Response
    {
        $texte = $dm->getRepository(Texte::class)->find($id);

        if ($texte) {
            // Supprimer l'image associée si elle existe
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
        $commentaires = $dm->getRepository(Commentaire::class)
            ->createQueryBuilder()
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('admin/TextCommentaire.html.twig', [
            'commentaires' => $commentaires,
            'texte' => null,
        ]);
    }

    #[Route('/commentaires/{id}/supprimer', name: 'app_admin_commentaire_supprimer', methods: ['POST'])]
    public function supprimerCommentaire(string $id, DocumentManager $dm): Response
    {
        $commentaire = $dm->getRepository(Commentaire::class)->find($id);

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

        return $this->redirectToRoute('app_admin_commentaires');
    }
}
