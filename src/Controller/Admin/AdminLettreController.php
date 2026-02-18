<?php

namespace App\Controller\Admin;

use App\Entity\Lettre;
use App\Form\LettreType;
use App\Repository\LettreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/lettres')]
#[IsGranted('ROLE_ADMIN')]
class AdminLettreController extends AbstractController
{
    #[Route('/', name: 'admin_lettre_list')]
    public function list(Request $request, LettreRepository $lettreRepo): Response
    {
        $statut = $request->query->get('statut', 'all');

        if ($statut === 'actif') {
            $lettres = $lettreRepo->findBy(['isArchive' => false], ['id' => 'DESC']);
        } elseif ($statut === 'archive') {
            $lettres = $lettreRepo->findBy(['isArchive' => true], ['id' => 'DESC']);
        } else {
            $lettres = $lettreRepo->findBy([], ['id' => 'DESC']);
        }

        return $this->render('admin/adminAtelier.html.twig', [
            'ateliers' => [],
            'visios' => [],
            'lettres' => $lettres,
            'vollons' => [],
            'statut' => $statut
        ]);
    }

    #[Route('/nouveau', name: 'admin_lettre_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $lettre = new Lettre();
        $form = $this->createForm(LettreType::class, $lettre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lettre);
            $em->flush();

            $this->addFlash('success', 'La lettre a été créée avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/lettreNew.html.twig', [
            'form' => $form,
            'lettre' => $lettre
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_lettre_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Lettre $lettre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LettreType::class, $lettre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La lettre a été modifiée avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/lettreEdit.html.twig', [
            'form' => $form,
            'lettre' => $lettre,
        ]);
    }

    #[Route('/{id}', name: 'admin_lettre_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Lettre $lettre): Response
    {
        return $this->render('admin/lettreShow.html.twig', [
            'lettre' => $lettre,
        ]);
    }

    #[Route('/toggle-archive/{id}', name: 'admin_lettre_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleArchive(Lettre $lettre, EntityManagerInterface $em): Response
    {
        $lettre->setIsArchive(!$lettre->isArchive());
        $em->flush();

        $message = $lettre->isArchive()
            ? 'La lettre a été archivée.'
            : 'La lettre a été désarchivée.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/supprimer/{id}', name: 'admin_lettre_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Lettre $lettre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lettre->getId(), $request->request->get('_token'))) {
            $em->remove($lettre);
            $em->flush();

            $this->addFlash('success', 'La lettre a été supprimée.');
        }

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/{id}/archive', name: 'admin_lettre_archive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function archive(Request $request, Lettre $lettre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('archive'.$lettre->getId(), $request->request->get('_token'))) {
            $lettre->setIsArchive(true);
            $em->flush();

            $this->addFlash('success', 'Lettre archivée avec succès');
        }

        return $this->redirectToRoute('admin_lettre_show', ['id' => $lettre->getId()]);
    }
}
