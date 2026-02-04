<?php

namespace App\Controller\Admin;

use App\Document\Post;
use App\Service\ImageResizerService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/posts')]
#[IsGranted('ROLE_ADMIN')]
class AdminPermPostController extends AbstractController
{
    #[Route('/', name: 'admin_post_index', methods: ['GET'])]
    public function index(DocumentManager $dm): Response
    {
        $repository = $dm->getRepository(Post::class);
        $posts = $repository->findBy([], ['dateCreation' => 'DESC']);

        return $this->render('admin/permanent/postIndex.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        DocumentManager $dm,
        ImageResizerService $imageResizer,
        SluggerInterface $slugger
    ): Response {
        if ($request->isMethod('POST')) {
            try {
                $titre = $request->request->get('titre');
                $contenu = $request->request->get('contenu');

                // Validation basique
                if (empty($titre) || empty($contenu)) {
                    $this->addFlash('error', 'Le titre et le contenu sont obligatoires.');
                    return $this->render('admin/permanent/postNew.html.twig');
                }

                $post = new Post();
                $post->setTitre($titre);
                $post->setContenu($contenu);

                // Récupérer l'utilisateur connecté
                $user = $this->getUser();
                if ($user) {
                    $post->setAuteurId($user->getId());
                    $post->setAuteurNom($user->getEmail() ?? 'Administrateur');
                }

                // Traiter l'image avec redimensionnement
                $imageFile = $request->files->get('imageFile');
                if ($imageFile) {
                    try {
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $imageResizer->setUploadDirectory($uploadDir);
                        $fileName = $imageResizer->resize($imageFile);
                        $post->setImage($fileName);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    }
                }

                // Traiter le PDF
                $pdfFile = $request->files->get('pdfFile');
                if ($pdfFile) {
                    $pdfFileName = $this->uploadFile($pdfFile, 'posts/pdf', $slugger);
                    $post->setPdf($pdfFileName);
                }

                // Traiter la vidéo
                $videoFile = $request->files->get('videoFile');
                if ($videoFile) {
                    $videoFileName = $this->uploadFile($videoFile, 'posts/videos', $slugger);
                    $post->setVideo($videoFileName);
                }

                // Traiter l'audio
                $audioFile = $request->files->get('audioFile');
                if ($audioFile) {
                    $audioFileName = $this->uploadFile($audioFile, 'posts/audio', $slugger);
                    $post->setAudio($audioFileName);
                }

                $post->setDateCreation(new \DateTime());
                $post->setDateModification(new \DateTime());
                $post->setPublie(true);

                $dm->persist($post);
                $dm->flush();

                $this->addFlash('success', 'Le post a été créé avec succès.');
                return $this->redirectToRoute('admin_post_index');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du post : ' . $e->getMessage());
            }
        }

        return $this->render('admin/permanent/postNew.html.twig');
    }

    #[Route('/{id}', name: 'admin_post_show', methods: ['GET'])]
    public function show(string $id, DocumentManager $dm): Response
    {
        $repository = $dm->getRepository(Post::class);
        $post = $repository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }

        return $this->render('admin/permanent/postShow.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(
        string $id,
        Request $request,
        DocumentManager $dm,
        ImageResizerService $imageResizer,
        SluggerInterface $slugger
    ): Response {
        $repository = $dm->getRepository(Post::class);
        $post = $repository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }

        if ($request->isMethod('POST')) {
            try {
                $titre = $request->request->get('titre');
                $contenu = $request->request->get('contenu');

                if (empty($titre) || empty($contenu)) {
                    $this->addFlash('error', 'Le titre et le contenu sont obligatoires.');
                    return $this->render('admin/permanent/postEdit.html.twig', ['post' => $post]);
                }

                $post->setTitre($titre);
                $post->setContenu($contenu);
                $post->setDateModification(new \DateTime());

                // Traiter l'image avec redimensionnement
                $imageFile = $request->files->get('imageFile');
                if ($imageFile) {
                    try {
                        // Supprimer l'ancienne image
                        if ($post->getImage()) {
                            $oldImagePath = $this->getParameter('kernel.project_dir')
                                . '/public/uploads/posts/' . $post->getImage();
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }

                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $imageResizer->setUploadDirectory($uploadDir);
                        $fileName = $imageResizer->resize($imageFile);
                        $post->setImage($fileName);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors du traitement de l\'image : ' . $e->getMessage());
                    }
                }

                // Traiter le PDF
                $pdfFile = $request->files->get('pdfFile');
                if ($pdfFile) {
                    if ($post->getPdf()) {
                        $this->deleteFile('posts/pdf/' . $post->getPdf());
                    }
                    $pdfFileName = $this->uploadFile($pdfFile, 'posts/pdf', $slugger);
                    $post->setPdf($pdfFileName);
                }

                // Traiter la vidéo
                $videoFile = $request->files->get('videoFile');
                if ($videoFile) {
                    if ($post->getVideo()) {
                        $this->deleteFile('posts/videos/' . $post->getVideo());
                    }
                    $videoFileName = $this->uploadFile($videoFile, 'posts/videos', $slugger);
                    $post->setVideo($videoFileName);
                }

                // Traiter l'audio
                $audioFile = $request->files->get('audioFile');
                if ($audioFile) {
                    if ($post->getAudio()) {
                        $this->deleteFile('posts/audio/' . $post->getAudio());
                    }
                    $audioFileName = $this->uploadFile($audioFile, 'posts/audio', $slugger);
                    $post->setAudio($audioFileName);
                }

                $dm->flush();

                $this->addFlash('success', 'Le post a été modifié avec succès.');
                return $this->redirectToRoute('admin_post_show', ['id' => $post->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('admin/permanent/postEdit.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_post_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, DocumentManager $dm): Response
    {
        $repository = $dm->getRepository(Post::class);
        $post = $repository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            try {
                // Supprimer tous les fichiers associés
                if ($post->getImage()) {
                    $this->deleteFile('posts/' . $post->getImage());
                }
                if ($post->getPdf()) {
                    $this->deleteFile('posts/pdf/' . $post->getPdf());
                }
                if ($post->getVideo()) {
                    $this->deleteFile('posts/videos/' . $post->getVideo());
                }
                if ($post->getAudio()) {
                    $this->deleteFile('posts/audio/' . $post->getAudio());
                }

                $dm->remove($post);
                $dm->flush();

                $this->addFlash('success', 'Le post a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_post_index');
    }

    #[Route('/{postId}/comment/{commentId}/delete', name: 'admin_post_comment_delete', methods: ['POST'])]
    public function deleteComment(
        string $postId,
        string $commentId,
        Request $request,
        DocumentManager $dm
    ): Response {
        $repository = $dm->getRepository(Post::class);
        $post = $repository->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé');
        }

        if ($this->isCsrfTokenValid('delete-comment' . $commentId, $request->request->get('_token'))) {
            try {
                $commentaires = $post->getCommentaires();

                foreach ($commentaires as $key => $commentaire) {
                    if ($commentaire->getId() === $commentId) {
                        $commentaires->remove($key);
                        $dm->flush();
                        $this->addFlash('success', 'Le commentaire a été supprimé.');
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression du commentaire : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_post_show', ['id' => $postId]);
    }

    // Méthode utilitaire pour uploader les fichiers non-images
    private function uploadFile($file, string $subDir, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $subDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file->move($uploadDir, $fileName);

        return $fileName;
    }

    // Méthode utilitaire pour supprimer un fichier
    private function deleteFile(string $relativePath): void
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $relativePath;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
