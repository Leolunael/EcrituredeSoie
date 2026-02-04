<?php

namespace App\Controller\Admin;

use App\Document\Post;
use App\Document\Permanent;
use App\Repository\PermanentRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/permanent')]
#[IsGranted('ROLE_ADMIN')]
class AdminPermanentController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(PermanentRepository $permanentRepository, DocumentManager $dm): Response
    {
        // Récupérer l'utilisateur connecté
        $permanent = $this->getUser();

        // Statistiques des permanents
        $stats = [
            'total' => $permanentRepository->countTotal(),
            'actifs' => $permanentRepository->countActifs(),
            'paiementsEnAttente' => $permanentRepository->countPaiementsEnAttente(),
        ];

        // Récupérer les inscriptions récentes
        $recentInscriptions = $permanentRepository->findBy(
            [],
            ['dateInscription' => 'DESC'],
            10
        );

        // Récupérer les posts récents depuis MongoDB
        try {
            $postRepository = $dm->getRepository(Post::class);
            $recentPosts = $postRepository->findBy(
                [],
                ['dateCreation' => 'DESC'],
                5
            );

            // Ajouter le nombre total de posts dans les stats
            $stats['totalPosts'] = count($postRepository->findAll());
        } catch (\Exception $e) {
            // En cas d'erreur avec MongoDB, on initialise avec un tableau vide
            $recentPosts = [];
            $stats['totalPosts'] = 0;
        }

        return $this->render('admin/permanent/dashboard.html.twig', [
            'permanent' => $permanent,
            'stats' => $stats,
            'recentInscriptions' => $recentInscriptions,
            'recentPosts' => $recentPosts,
        ]);
    }

    #[Route('/', name: 'admin_permanent_index', methods: ['GET'])]
    public function index(PermanentRepository $permanentRepository): Response
    {
        $permanents = $permanentRepository->findBy([], ['dateInscription' => 'DESC']);

        return $this->render('admin/permanent/index.html.twig', [
            'permanents' => $permanents,
        ]);
    }

    #[Route('/{id}/changer-role', name: 'admin_permanent_changer_role', methods: ['POST'])]
    public function changerRole(Request $request, string $id, PermanentRepository $permanentRepository, DocumentManager $dm): Response
    {
        $permanent = $permanentRepository->find($id);

        if (!$permanent) {
            throw $this->createNotFoundException('Ce permanent n\'existe pas.');
        }

        $nouveauRole = $request->request->get('role');

        // Vérifier que le rôle est valide
        $rolesValides = ['ROLE_USER', 'ROLE_PERMANENT', 'ROLE_ADMIN'];
        if (!in_array($nouveauRole, $rolesValides)) {
            $this->addFlash('error', 'Rôle invalide.');
            return $this->redirectToRoute('admin_permanent_show', ['id' => $permanent->getId()]);
        }

        // Réinitialiser les rôles et ajouter le nouveau
        $roles = ['ROLE_USER']; // Rôle par défaut

        if ($nouveauRole === 'ROLE_PERMANENT') {
            $roles[] = 'ROLE_PERMANENT';
        } elseif ($nouveauRole === 'ROLE_ADMIN') {
            $roles[] = 'ROLE_PERMANENT';
            $roles[] = 'ROLE_ADMIN';
        }

        $permanent->setRoles($roles);

        // Sauvegarder avec MongoDB DocumentManager
        $dm->flush();

        $this->addFlash('success', 'Le rôle a été modifié avec succès.');

        return $this->redirectToRoute('admin_permanent_show', ['id' => $permanent->getId()]);
    }

    #[Route('/{id}/desactiver', name: 'admin_permanent_desactiver', methods: ['POST'])]
    public function desactiver(string $id, PermanentRepository $permanentRepository, DocumentManager $dm): Response
    {
        $permanent = $permanentRepository->find($id);

        if (!$permanent) {
            throw $this->createNotFoundException('Ce permanent n\'existe pas.');
        }

        $permanent->setActif(false);

        // Sauvegarder avec MongoDB DocumentManager
        $dm->flush();

        $this->addFlash('success', 'Le permanent a été désactivé avec succès.');

        return $this->redirectToRoute('admin_permanent_show', ['id' => $permanent->getId()]);
    }

    #[Route('/{id}/activer', name: 'admin_permanent_activer', methods: ['POST'])]
    public function activer(string $id, PermanentRepository $permanentRepository, DocumentManager $dm): Response
    {
        $permanent = $permanentRepository->find($id);

        if (!$permanent) {
            throw $this->createNotFoundException('Ce permanent n\'existe pas.');
        }

        // CORRECTION: setActif(true) au lieu de false !
        $permanent->setActif(true);

        // Sauvegarder avec MongoDB DocumentManager
        $dm->flush();

        $this->addFlash('success', 'Le permanent a été activé avec succès.');

        return $this->redirectToRoute('admin_permanent_show', ['id' => $permanent->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'admin_permanent_delete', methods: ['POST'])]
    public function delete(Request $request, string $id, PermanentRepository $permanentRepository, DocumentManager $dm): Response
    {
        $permanent = $permanentRepository->find($id);

        if (!$permanent) {
            throw $this->createNotFoundException('Ce permanent n\'existe pas.');
        }

        // Vérifier le token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$permanent->getId(), $request->request->get('_token'))) {
            // Supprimer avec MongoDB DocumentManager
            $dm->remove($permanent);
            $dm->flush();

            $this->addFlash('success', 'Le permanent a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_permanent_index');
    }

    // CETTE ROUTE DOIT ÊTRE EN DERNIER car elle capture tout /{id}
    #[Route('/{id}', name: 'admin_permanent_show', methods: ['GET'])]
    public function show(string $id, PermanentRepository $permanentRepository): Response
    {
        $permanent = $permanentRepository->find($id);

        if (!$permanent) {
            throw $this->createNotFoundException('Ce permanent n\'existe pas.');
        }

        return $this->render('admin/permanent/show.html.twig', [
            'permanent' => $permanent,
        ]);
    }
}
