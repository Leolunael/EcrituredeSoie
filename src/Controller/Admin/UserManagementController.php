<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'admin_users_index')]
    public function index(): Response
    {
        $stats = [
            'users' => count($this->userRepository->findAll())
        ];

        return $this->render('admin/users/index.html.twig', [
            'stats' => $stats
        ]);
    }

    #[Route('/registered', name: 'admin_users_registered')]
    public function listRegisteredUsers(): Response
    {
        // Exclure les utilisateurs anonymisés de la liste
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/users/list_registered.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/user/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show_user.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/users/edit_user.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            // Supprimer les demandes de réinitialisation de mot de passe liées
            $connection = $this->entityManager->getConnection();
            try {
                $connection->executeStatement(
                    'DELETE FROM reset_password_request WHERE user_id = :userId',
                    ['userId' => $user->getId()]
                );
            } catch (\Exception $e) {
                // Si la table n'existe pas ou autre erreur, on continue
            }

            // Anonymiser l'utilisateur au lieu de le supprimer
            $user->anonymize();

            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur anonymisé avec succès. Ses données personnelles ont été supprimées mais ses inscriptions sont conservées pour les statistiques.');
        }

        return $this->redirectToRoute('admin_users_registered');
    }

}
