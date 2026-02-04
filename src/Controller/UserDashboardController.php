<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\InscriptionAtelier;
use App\Entity\InscriptionVisio;
use App\Entity\InscriptionLettre;
use App\Form\UserProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserDashboardController extends AbstractController
{
    #[Route('/mon-espace', name: 'user_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les inscriptions de l'utilisateur
        $inscriptions = $user->getToutesLesInscriptions();

        return $this->render('user_dashboard/index.html.twig', [
            'user' => $user,
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/mon-espace/profil', name: 'user_profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        $form = $this->createForm(UserProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('user_profil');
        }

        return $this->render('user_dashboard/profil.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/mon-espace/inscription/atelier/{id}', name: 'user_inscription_atelier_detail')]
    public function inscriptionAtelierDetail(InscriptionAtelier $inscription): Response
    {
        // Vérifier que l'inscription appartient bien à l'utilisateur connecté
        if ($inscription->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette inscription.');
        }

        return $this->render('user_dashboard/detail.html.twig', [
            'type' => 'Atelier',
            'inscription' => $inscription,
            'evenement' => $inscription->getAtelier(),
        ]);
    }

    #[Route('/mon-espace/inscription/visio/{id}', name: 'user_inscription_visio_detail')]
    public function inscriptionVisioDetail(InscriptionVisio $inscription): Response
    {
        // Vérifier que l'inscription appartient bien à l'utilisateur connecté
        if ($inscription->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette inscription.');
        }

        return $this->render('user_dashboard/detail.html.twig', [
            'type' => 'Visio',
            'inscription' => $inscription,
            'evenement' => $inscription->getVisio(),
        ]);
    }

    #[Route('/mon-espace/inscription/lettre/{id}', name: 'user_inscription_lettre_detail')]
    public function inscriptionLettreDetail(InscriptionLettre $inscription): Response
    {
        // Vérifier que l'inscription appartient bien à l'utilisateur connecté
        if ($inscription->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette inscription.');
        }

        return $this->render('user_dashboard/detail.html.twig', [
            'type' => 'Lettre',
            'inscription' => $inscription,
            'evenement' => $inscription->getLettre(),
        ]);
    }
}
