<?php

namespace App\Controller;

use App\Document\Post;
use App\Document\PermanentCom;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/posts')]
#[IsGranted('ROLE_PERMANENT')]
class PostController extends AbstractController
{
//    #[Route('/', name: 'app_post_index')]
//    public function index(DocumentManager $dm): Response
//    {
//        $posts = $dm->getRepository(Post::class)->findAll();
//
//        return $this->render('post/index.html.twig', [
//            'posts' => $posts,
//        ]);
//    }

    #[Route('/', name: 'app_post_index')]
    public function index(DocumentManager $dm): Response
    {
        // DEBUG: Afficher les informations de l'utilisateur
        dump('User:', $this->getUser());
        dump('Roles:', $this->getUser()?->getRoles());
        dump('Is Granted ROLE_PERMANENT:', $this->isGranted('ROLE_PERMANENT'));
        dump('Is Granted ROLE_USER:', $this->isGranted('ROLE_USER'));

        $posts = $dm->getRepository(Post::class)->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/commenter', name: 'app_post_comment', methods: ['POST'])]
    public function comment(
        Post $post,
        Request $request,
        DocumentManager $dm
    ): Response {
        $contenu = $request->request->get('contenu');
        $parentId = $request->request->get('parent_id');

        if (empty($contenu)) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        $user = $this->getUser();

        // Créer l'objet PermanentCom
        $commentaire = new PermanentCom();
        $commentaire->setContenu($contenu);
        $commentaire->setAuteurId($user->getId());
        $commentaire->setAuteurNom($user->getNom() . ' ' . $user->getPrenom());

        if ($parentId) {
            $commentaire->setParentId($parentId);
        }

        $post->addCommentaire($commentaire);
        $dm->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès.');
        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }
}
