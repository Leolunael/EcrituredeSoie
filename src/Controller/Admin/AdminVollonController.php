<?php

namespace App\Controller\Admin;

use App\Entity\Vollon;
use App\Form\VollonType;
use App\Repository\VollonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/Vollon')]
#[IsGranted('ROLE_ADMIN')]
class AdminVollonController extends AbstractController
{
    #[Route('/', name: 'admin_vollon_list')]
    public function list(Request $request, VollonRepository $vollonRepo): Response
    {
        $statut = $request->query->get('statut', 'all');

        if ($statut === 'actif') {
            $vollons = $vollonRepo->findBy(['isArchive' => false], ['dateVollon' => 'DESC']);
        } elseif ($statut === 'archive') {
            $vollons = $vollonRepo->findBy(['isArchive' => true], ['dateVollon' => 'DESC']);
        } else {
            $vollons = $vollonRepo->findBy([], ['dateVollon' => 'DESC']);
        }

        return $this->render('admin/adminAtelier.html.twig', [
            'ateliers' => [],
            'vollons' => $vollons,
            'visios' => [],
            'lettres' => [],
            'statut' => $statut
        ]);
    }

    #[Route('/nouveau', name: 'admin_vollon_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $vollon = new Vollon();
        $form = $this->createForm(VollonType::class, $vollon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($vollon);
            $em->flush();

            $this->addFlash('success', 'L\'atelier a été créé avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/vollonNew.html.twig', [
            'form' => $form,
            'vollon' => $vollon,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_vollon_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Vollon $vollon, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(VollonType::class, $vollon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'L\'atelier a été modifié avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/vollonEdit.html.twig', [
            'form' => $form,
            'vollon' => $vollon,
        ]);
    }

    #[Route('/{id}', name: 'admin_vollon_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Vollon $vollon): Response
    {
        return $this->render('admin/vollonShow.html.twig', [
            'vollon' => $vollon,
        ]);
    }

    #[Route('/toggle-archive/{id}', name: 'admin_vollon_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleArchive(Vollon $vollon, EntityManagerInterface $em): Response
    {
        $vollon->setIsArchive(!$vollon->isArchive());
        $em->flush();

        $message = $vollon->isArchive()
            ? 'L\'atelier a été archivé.'
            : 'L\'atelier a été désarchivé.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/supprimer/{id}', name: 'admin_vollon_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Vollon $vollon, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vollon->getId(), $request->request->get('_token'))) {
            $em->remove($vollon);
            $em->flush();

            $this->addFlash('success', 'L\'atelier a été supprimé.');
        }

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/{id}/archive', name: 'admin_vollon_archive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function archive(Request $request, Vollon $vollon, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('archive'.$vollon->getId(), $request->request->get('_token'))) {
            $vollon->setIsArchive(true);
            $em->flush();

            $this->addFlash('success', 'Atelier archivé avec succès');
        }

        return $this->redirectToRoute('admin_vollon_show', ['id' => $vollon->getId()]);
    }
}
