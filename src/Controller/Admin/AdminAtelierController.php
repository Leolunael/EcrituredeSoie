<?php

namespace App\Controller\Admin;

use App\Entity\Atelier;
use App\Form\AtelierType;
use App\Repository\AtelierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ateliers')]
#[IsGranted('ROLE_ADMIN')]
class AdminAtelierController extends AbstractController
{
    #[Route('/', name: 'admin_atelier_list')]
    public function list(Request $request, AtelierRepository $atelierRepo): Response
    {
        $statut = $request->query->get('statut', 'all');

        if ($statut === 'actif') {
            $ateliers = $atelierRepo->findBy(['isArchive' => false], ['dateAtelier' => 'DESC']);
        } elseif ($statut === 'archive') {
            $ateliers = $atelierRepo->findBy(['isArchive' => true], ['dateAtelier' => 'DESC']);
        } else {
            $ateliers = $atelierRepo->findBy([], ['dateAtelier' => 'DESC']);
        }

        return $this->render('admin/adminAtelier.html.twig', [
            'ateliers' => $ateliers,
            'visios' => [],
            'lettres' => [],
            'vollons' => [],
            'statut' => $statut
        ]);
    }

    #[Route('/nouveau', name: 'admin_atelier_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $atelier = new Atelier();
        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le nouvel atelier est mis à la une, on retire l'ancien
            if ($atelier->isALaUne()) {
                $this->retirerAncienALaUne($em, null);
            }

            $em->persist($atelier);
            $em->flush();

            $this->addFlash('success', 'L\'atelier a été créé avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/atelierNew.html.twig', [
            'form' => $form,
            'atelier' => $atelier,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_atelier_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Atelier $atelier, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($atelier->isALaUne()) {
                $this->retirerAncienALaUne($em, $atelier->getId());
            }

            $em->flush();

            $this->addFlash('success', 'L\'atelier a été modifié avec succès.');

            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/atelierEdit.html.twig', [
            'form' => $form,
            'atelier' => $atelier,
        ]);
    }

    #[Route('/{id}', name: 'admin_atelier_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Atelier $atelier): Response
    {
        return $this->render('admin/atelierShow.html.twig', [
            'atelier' => $atelier,
        ]);
    }

    #[Route('/mettre-a-la-une/{id}', name: 'admin_atelier_mettre_a_la_une', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function mettreALaUne(Atelier $atelier, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('a_la_une_' . $atelier->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Retirer l'atelier actuellement à la une (sauf si c'est déjà celui-ci)
        $this->retirerAncienALaUne($em, $atelier->getId());

        // Basculer : si déjà à la une, on le retire ; sinon on le met à la une
        $atelier->setALaUne(!$atelier->isALaUne());
        $em->flush();

        $message = $atelier->isALaUne()
            ? 'L\'atelier a été mis à la une !'
            : 'L\'atelier a été retiré de la une.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_atelier_list');
    }

    #[Route('/toggle-archive/{id}', name: 'admin_atelier_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleArchive(Atelier $atelier, EntityManagerInterface $em): Response
    {
        $atelier->setIsArchive(!$atelier->isArchive());
        $em->flush();

        $message = $atelier->isArchive()
            ? 'L\'atelier a été archivé.'
            : 'L\'atelier a été désarchivé.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/supprimer/{id}', name: 'admin_atelier_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Atelier $atelier, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$atelier->getId(), $request->request->get('_token'))) {
            $em->remove($atelier);
            $em->flush();

            $this->addFlash('success', 'L\'atelier a été supprimé.');
        }

        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/{id}/archive', name: 'admin_atelier_archive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function archive(Request $request, Atelier $atelier, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('archive'.$atelier->getId(), $request->request->get('_token'))) {
            $atelier->setIsArchive(true);
            $em->flush();

            $this->addFlash('success', 'Atelier archivé avec succès');
        }

        return $this->redirectToRoute('admin_atelier_show', ['id' => $atelier->getId()]);
    }


    private function retirerAncienALaUne(EntityManagerInterface $em, ?int $excludeId): void
    {
        $qb = $em->getRepository(Atelier::class)
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
