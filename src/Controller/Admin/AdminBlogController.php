<?php

namespace App\Controller\Admin;

use App\Document\BlogCommentaire;
use App\Document\Blog;
use App\Form\BlogType;
use App\Service\ImageResizerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/blog')]
#[IsGranted('ROLE_ADMIN')]
class AdminBlogController extends AbstractController
{
    #[Route('/', name: 'admin_blog')]
    public function index(DocumentManager $dm): Response
    {
        $blogs = $dm->getRepository(Blog::class)
            ->createQueryBuilder()
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('admin/blog.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_blog_nouveau')]
    public function nouveau(Request $request, DocumentManager $dm, ImageResizerService $imageResizer): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image avec redimensionnement
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                try {
                    // Définir le répertoire de destination
                    $uploadDir = $this->getParameter('blogs_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Configurer le service avec le bon répertoire
                    $imageResizer->setUploadDirectory($uploadDir);

                    // Redimensionner et sauvegarder l'image (max 720x1280)
                    $fileName = $imageResizer->resize($imageFile);
                    $blog->setImage($fileName);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/blogForm.html.twig', [
                        'form' => $form->createView(),
                        'blog' => $blog,
                    ]);
                }
            }

            // Si on met ce blog à la une, retirer les autres
            if ($blog->isALaUne()) {
                $blogsALaUne = $dm->getRepository(Blog::class)
                    ->createQueryBuilder()
                    ->field('aLaUne')->equals(true)
                    ->field('id')->notEqual($blog->getId() ?? 'new')
                    ->sort('dateModification', 'ASC')
                    ->getQuery()
                    ->execute()
                    ->toArray();

                if (count($blogsALaUne) >= 3) {
                    $aRetirer = array_slice($blogsALaUne, 0, -2);
                    foreach ($aRetirer as $autreBlog) {
                        $autreBlog->setALaUne(false);
                    }
                }
            }

            $dm->persist($blog);
            $dm->flush();

            $this->addFlash('success', 'Le blog a été créé avec succès !');
            return $this->redirectToRoute('admin_blog');
        }

        return $this->render('admin/blogForm.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_blog_modifier')]
    public function modifier(string $id, Request $request, DocumentManager $dm, ImageResizerService $imageResizer): Response
    {
        $blog = $dm->getRepository(Blog::class)->find($id);

        if (!$blog) {
            throw $this->createNotFoundException('Ce blog n\'existe pas.');
        }

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blog->setDateModification(new \DateTime());
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                try {
                    // Supprimer l'ancienne image si elle existe
                    if ($blog->getImage()) {
                        $oldImagePath = $this->getParameter('blogs_images_directory') . '/' . $blog->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    // Définir le répertoire de destination
                    $uploadDir = $this->getParameter('blogs_images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Configurer le service et redimensionner l'image
                    $imageResizer->setUploadDirectory($uploadDir);
                    $fileName = $imageResizer->resize($imageFile);
                    $blog->setImage($fileName);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    return $this->render('admin/blogForm.html.twig', [
                        'form' => $form->createView(),
                        'blog' => $blog,
                    ]);
                }
            }

            // Si on met ce blog à la une, retirer les autres
            if ($blog->isALaUne()) {
                $autresBlogs = $dm->getRepository(Blog::class)
                    ->createQueryBuilder()
                    ->field('aLaUne')->equals(true)
                    ->field('id')->notEqual($id)
                    ->getQuery()
                    ->execute();

                foreach ($autresBlogs as $autreBlog) {
                    $autreBlog->setALaUne(false);
                }
            }

            $dm->flush();

            $this->addFlash('success', 'Le blog a été modifié avec succès !');
            return $this->redirectToRoute('admin_blog');
        }

        return $this->render('admin/blogForm.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_blog_supprimer', methods: ['POST'])]
    public function supprimer(string $id, DocumentManager $dm): Response
    {
        $blog = $dm->getRepository(Blog::class)->find($id);

        if ($blog) {
            // Supprimer l'image associée si elle existe
            if ($blog->getImage()) {
                $imagePath = $this->getParameter('blogs_images_directory').'/'.$blog->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $dm->remove($blog);
            $dm->flush();
            $this->addFlash('success', 'Le blog a été supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_blog');
    }

    #[Route('/{id}/mettre-a-la-une', name: 'app_admin_blog_mettre_a_la_une')]
    public function mettreALaUne(string $id, DocumentManager $dm): Response
    {
        $blog = $dm->getRepository(Blog::class)->find($id);

        if ($blog) {
            $blogsALaUne = $dm->getRepository(Blog::class)
                ->createQueryBuilder()
                ->field('aLaUne')->equals(true)
                ->field('id')->notEqual($id)
                ->sort('dateModification', 'ASC')
                ->getQuery()
                ->execute()
                ->toArray();

            if (count($blogsALaUne) >= 3) {
                $blogsALaUne[0]->setALaUne(false);
            }

            $blog->setALaUne(true);
            $dm->flush();
            $this->addFlash('success', 'Le blog a été mis à la une !');
        }

        return $this->redirectToRoute('admin_blog');
    }

    #[Route('/commentaires', name: 'app_admin_blogCommentaires')]
    public function commentaires(DocumentManager $dm): Response
    {
        $commentaires = $dm->getRepository(BlogCommentaire::class)
            ->createQueryBuilder()
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('admin/blogCom.html.twig', [
            'commentaires' => $commentaires,
            'blog' => null,
        ]);
    }

    #[Route('/commentaires/{id}/supprimer', name: 'app_admin_blogCommentaire_supprimer', methods: ['POST'])]
    public function supprimerCommentaire(string $id, DocumentManager $dm): Response
    {
        $commentaire = $dm->getRepository(BlogCommentaire::class)->find($id);

        if ($commentaire) {
            if (!$commentaire->isReponse()) {
                $reponses = $dm->getRepository(BlogCommentaire::class)
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

        return $this->redirectToRoute('app_admin_blogCommentaires');
    }
}
