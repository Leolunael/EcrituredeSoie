<?php

namespace App\Controller\Admin;

use App\Entity\InscriptionAtelier;
use App\Entity\Atelier;
use App\Form\InscriptionAtelierType;
use App\Repository\InscriptionAtelierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/inscription')]
class AdminInscriptionController extends AbstractController
{
    #[Route('/new/{atelierId}', name: 'admin_inscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $atelierId, EntityManagerInterface $em): Response
    {
        $atelier = $em->getRepository(Atelier::class)->find($atelierId);

        if (!$atelier) {
            throw $this->createNotFoundException('Atelier non trouvé');
        }

        $inscription = new InscriptionAtelier();
        $inscription->setAtelier($atelier);
        $inscription->setDateInscription(new \DateTime());

        $form = $this->createForm(InscriptionAtelierType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription ajoutée avec succès');
            return $this->redirectToRoute('admin_atelier_show', ['id' => $atelierId]);
        }

        return $this->render('admin/atelierInscription.html.twig', [
            'form' => $form,
            'atelier' => $atelier,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_inscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InscriptionAtelier $inscription, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InscriptionAtelierType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Inscription modifiée avec succès');
            return $this->redirectToRoute('admin_atelier_show', ['id' => $inscription->getAtelier()->getId()]);
        }

        return $this->render('admin/atelierInscrEdit.html.twig', [
            'form' => $form,
            'inscription' => $inscription,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_inscription_delete', methods: ['POST'])]
    public function delete(Request $request, InscriptionAtelier $inscription, EntityManagerInterface $em): Response
    {
        $atelierId = $inscription->getAtelier()->getId();

        if ($this->isCsrfTokenValid('delete'.$inscription->getId(), $request->request->get('_token'))) {
            $em->remove($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription supprimée avec succès');
        }

        return $this->redirectToRoute('admin_atelier_show', ['id' => $atelierId]);
    }
}
