<?php

namespace App\Controller\Admin;

use App\Entity\Presentation;
use App\Form\PresentationType;
use App\Repository\PresentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/presentations', name: 'admin_presentation_')]
#[IsGranted('ROLE_ADMIN')]
class AdminPresentationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(PresentationRepository $presentationRepository): Response
    {
        $presentations = $presentationRepository->findAll();

        return $this->render('admin/presentation.html.twig', [
            'presentations' => $presentations,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $presentation = new Presentation();
        $form = $this->createForm(PresentationType::class, $presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($presentation);
            $entityManager->flush();

            $this->addFlash('success', 'La présentation a été créée avec succès.');

            return $this->redirectToRoute('admin_presentation_index');
        }

        return $this->render('admin/presentationNew.html.twig', [
            'presentation' => $presentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Presentation $presentation): Response
    {
        // Si l’ID n’existe pas, Symfony renverra automatiquement une 404.
        return $this->render('admin/presentationShow.html.twig', [
            'presentation' => $presentation,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Presentation $presentation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PresentationType::class, $presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La présentation a été modifiée avec succès.');

            return $this->redirectToRoute('admin_presentation_index');
        }

        return $this->render('admin/presentationEdit.html.twig', [
            'presentation' => $presentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Presentation $presentation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $presentation->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($presentation);
            $entityManager->flush();

            $this->addFlash('success', 'La présentation a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('admin_presentation_index');
    }


    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(Request $request, Presentation $presentation, EntityManagerInterface $entityManager): Response
    {
        // CSRF pour sécuriser l’action
        if (!$this->isCsrfTokenValid('toggle' . $presentation->getId(), (string)$request->request->get('_token'))) {
            $this->addFlash('error', 'Le token CSRF est invalide. Action annulée.');
            return $this->redirectToRoute('admin_presentation_index');
        }

        // Basculer l’état (ex. isPublished)
        $presentation->setActif(!$presentation->isActif());
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'La présentation a été %s.',
            $presentation->isActif() ? 'Actif' : 'Inactif'
        ));

        return $this->redirectToRoute('admin_presentation_index');
    }
}
