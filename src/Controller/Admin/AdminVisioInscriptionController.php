<?php

namespace App\Controller\Admin;

use App\Entity\Visio;
use App\Entity\InscriptionVisio;
use App\Form\InscriptionVisioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/visio/inscription')]
#[IsGranted('ROLE_ADMIN')]
class AdminVisioInscriptionController extends AbstractController
{
    #[Route('/nouveau/{visioId}', name: 'admin_visio_inscription_new', requirements: ['visioId' => '\d+'])]
    public function new(int $visioId, Request $request, EntityManagerInterface $em): Response
    {
        $visio = $em->getRepository(Visio::class)->find($visioId);

        if (!$visio) {
            throw $this->createNotFoundException('Visio non trouvée');
        }

        $inscription = new InscriptionVisio();
        $inscription->setVisio($visio);
        $inscription->setDateInscription(new \DateTime());

        $form = $this->createForm(InscriptionVisioType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription ajoutée avec succès');
            return $this->redirectToRoute('admin_visio_show', ['id' => $visioId]);
        }

        return $this->render('admin/visioInscription.html.twig', [
            'form' => $form,
            'visio' => $visio,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_visio_inscription_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, InscriptionVisio $inscription, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InscriptionVisioType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Inscription modifiée avec succès');
            return $this->redirectToRoute('admin_visio_show', ['id' => $inscription->getVisio()->getId()]);
        }

        return $this->render('admin/visioInscrEdit.html.twig', [
            'form' => $form,
            'inscription' => $inscription,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_visio_inscription_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, InscriptionVisio $inscription, EntityManagerInterface $em): Response
    {
        $visioId = $inscription->getVisio()->getId();

        if ($this->isCsrfTokenValid('delete'.$inscription->getId(), $request->request->get('_token'))) {
            $em->remove($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription supprimée avec succès');
        }

        return $this->redirectToRoute('admin_visio_show', ['id' => $visioId]);
    }
}
