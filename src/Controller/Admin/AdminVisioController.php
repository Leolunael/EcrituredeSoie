<?php

namespace App\Controller\Admin;

use App\Entity\Visio;
use App\Form\AdminVisioType;  // ✅ CHANGÉ : Utiliser AdminVisioType au lieu de VisioType
use App\Repository\VisioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/visios')]
#[IsGranted('ROLE_ADMIN')]
class AdminVisioController extends AbstractController
{
    #[Route('/', name: 'admin_visio_list')]
    public function list(Request $request, VisioRepository $visioRepo): Response
    {
        $statut = $request->query->get('statut', 'all');

        if ($statut === 'actif') {
            $visios = $visioRepo->findBy(['isArchive' => false], ['dateVisio' => 'DESC']);
        } elseif ($statut === 'archive') {
            $visios = $visioRepo->findBy(['isArchive' => true], ['dateVisio' => 'DESC']);
        } else {
            $visios = $visioRepo->findBy([], ['dateVisio' => 'DESC']);
        }

        return $this->render('admin/adminAtelier.html.twig', [
            'ateliers' => [],
            'visios' => $visios,
            'lettres' => [],
            'vollons' => [],
            'statut' => $statut
        ]);
    }

    #[Route('/nouveau', name: 'admin_visio_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $visio = new Visio();
        $form = $this->createForm(AdminVisioType::class, $visio);  // ✅ CHANGÉ
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($visio);
            $em->flush();

            $this->addFlash('success', 'La visio a été créée avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/visioNew.html.twig', [
            'form' => $form,
            'visio' => $visio
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_visio_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Visio $visio, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminVisioType::class, $visio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si cet atelier est mis à la une, on retire l'éventuel ancien (sauf lui-même)
            if ($visio->isALaUne()) {
                $this->retirerAncienALaUne($em, $visio->getId());
            }

            $em->flush();

            $this->addFlash('success', 'L\'atelier a été modifié avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/visioEdit.html.twig', [
            'form' => $form,
            'visio' => $visio,
        ]);
    }

    #[Route('/{id}', name: 'admin_visio_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Visio $visio): Response
    {
        return $this->render('admin/visioShow.html.twig', [
            'visio' => $visio,
        ]);
    }

    #[Route('/mettre-a-la-une/{id}', name: 'admin_visio_mettre_a_la_une', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function mettreALaUne(Visio $visio, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('a_la_une_' . $visio->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->retirerAncienALaUne($em, $visio->getId());

        // Basculer : si déjà à la une, on le retire ; sinon on le met à la une
        $visio->setALaUne(!$visio->isALaUne());
        $em->flush();

        $message = $visio->isALaUne()
            ? 'La visio a été mis à la une !'
            : 'La visio a été retiré de la une.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_visio_list');
    }

    #[Route('/toggle-archive/{id}', name: 'admin_visio_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleArchive(Visio $visio, EntityManagerInterface $em): Response
    {
        $visio->setIsArchive(!$visio->isArchive());
        $em->flush();

        $message = $visio->isArchive()
            ? 'La visio a été archivée.'
            : 'La visio a été désarchivée.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/supprimer/{id}', name: 'admin_visio_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Visio $visio, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visio->getId(), $request->request->get('_token'))) {
            $em->remove($visio);
            $em->flush();

            $this->addFlash('success', 'La visio a été supprimée.');
        }

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/{id}/archive', name: 'admin_visio_archive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function archive(Request $request, Visio $visio, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('archive'.$visio->getId(), $request->request->get('_token'))) {
            $visio->setIsArchive(true);
            $em->flush();

            $this->addFlash('success', 'Visio archivée avec succès');
        }

        return $this->redirectToRoute('admin_visio_show', ['id' => $visio->getId()]);
    }

    private function retirerAncienALaUne(EntityManagerInterface $em, ?int $excludeId): void
    {
        $qb = $em->getRepository(Visio::class)
            ->createQueryBuilder('a')
            ->andWhere('a.aLaUne = true');

        if ($excludeId !== null) {
            $qb->andWhere('a.id != :id')
                ->setParameter('id', $excludeId);
        }

        $ancienALaUne = $qb->getQuery()->getOneOrNullResult();

        if ($ancienALaUne) {
            $ancienALaUne->setALaUne(false);
        }
    }
}
