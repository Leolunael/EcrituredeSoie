<?php

namespace App\Controller;

use App\Document\BlogCommentaire;
use App\Document\Blog;
use App\Form\BlogComType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function liste(DocumentManager $dm): Response
    {
        $blogs = $dm->getRepository(Blog::class)
            ->createQueryBuilder()
            ->field('publie')->equals(true)
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('blog/index.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/blog/{id}', name: 'app_blog_detail')]
    public function detail(string $id, Request $request, DocumentManager $dm): Response
    {
        $blog = $dm->getRepository(Blog::class)->find($id);

        if (!$blog || !$blog->isPublie()) {
            throw $this->createNotFoundException('Ce blog n\'existe pas.');
        }

        // Formulaire de commentaire
        $commentaire = new BlogCommentaire();
        $commentaire->setBlogId($id);
        $form = $this->createForm(BlogComType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->persist($commentaire);
            $dm->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès !');
            return $this->redirectToRoute('app_blog_detail', ['id' => $id]);
        }

        // Récupérer TOUS les commentaires approuvés pour ce blog
        $tousLesCommentaires = $dm->getRepository(BlogCommentaire::class)
            ->createQueryBuilder()
            ->field('blogId')->equals($id)
            ->field('approuve')->equals(true)
            ->sort('dateCreation', 'ASC')
            ->getQuery()
            ->execute();

        // Organiser les commentaires comme dans Post (parents + réponses)
        $commentairesParents = [];
        $reponsesMap = [];

        foreach ($tousLesCommentaires as $comm) {
            if ($comm->getCommentaireParentId() === null) {
                // Commentaire parent
                $commentairesParents[] = $comm;
            } else {
                // Réponse - grouper par parent ID
                $parentId = $comm->getCommentaireParentId();
                if (!isset($reponsesMap[$parentId])) {
                    $reponsesMap[$parentId] = [];
                }
                $reponsesMap[$parentId][] = $comm;
            }
        }

        // Créer la structure organisée (comme dans Post)
        $commentairesOrganises = [];
        foreach ($commentairesParents as $parent) {
            $parentIdString = $parent->getIdString();
            $commentairesOrganises[] = [
                'commentaire' => $parent,
                'reponses' => $reponsesMap[$parentIdString] ?? []
            ];
        }

        return $this->render('blog/detail.html.twig', [
            'blog' => $blog,
            'form' => $form->createView(),
            'commentairesOrganises' => $commentairesOrganises,
        ]);
    }

    #[Route('/blog/{blogId}/repondre/{commentaireId}', name: 'app_blogCommentaire_repondre', methods: ['POST'])]
    public function repondre(string $blogId, string $commentaireId, Request $request, DocumentManager $dm): Response
    {
        $contenu = $request->request->get('contenu');
        $auteur = $request->request->get('auteur');

        if (empty($contenu) || empty($auteur)) {
            $this->addFlash('error', 'Tous les champs sont requis.');
            return $this->redirectToRoute('app_blog_detail', ['id' => $blogId]);
        }

        $reponse = new BlogCommentaire();
        $reponse->setBlogId($blogId);
        $reponse->setCommentaireParentId($commentaireId);
        $reponse->setAuteur($auteur);
        $reponse->setContenu($contenu);

        $dm->persist($reponse);
        $dm->flush();

        $this->addFlash('success', 'Votre réponse a été ajoutée avec succès !');
        return $this->redirectToRoute('app_blog_detail', ['id' => $blogId]);
    }
}
