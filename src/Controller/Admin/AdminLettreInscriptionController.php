<?php

namespace App\Controller\Admin;

use App\Entity\Lettre;
use App\Entity\InscriptionLettre;
use App\Form\InscriptionLettreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/lettre/inscription')]
#[IsGranted('ROLE_ADMIN')]
class AdminLettreInscriptionController extends AbstractController
{
    #[Route('/nouveau/{lettreId}', name: 'admin_lettre_inscription_new', requirements: ['lettreId' => '\d+'])]
    public function new(int $lettreId, Request $request, EntityManagerInterface $em): Response
    {
        $lettre = $em->getRepository(Lettre::class)->find($lettreId);

        if (!$lettre) {
            throw $this->createNotFoundException('Lettre non trouvée');
        }

        $inscription = new InscriptionLettre();
        $inscription->setLettre($lettre);

        $form = $this->createForm(InscriptionLettreType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription ajoutée avec succès');
            return $this->redirectToRoute('admin_lettre_show', ['id' => $lettreId]);
        }

        return $this->render('admin/lettreInscription.html.twig', [
            'form' => $form,
            'lettre' => $lettre,
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_lettre_inscription_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, InscriptionLettre $inscription, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InscriptionLettreType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Inscription modifiée avec succès');
            return $this->redirectToRoute('admin_lettre_show', ['id' => $inscription->getLettre()->getId()]);
        }

        return $this->render('admin/lettreInscrEdit.html.twig', [
            'form' => $form,
            'inscription' => $inscription,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'admin_lettre_inscription_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, InscriptionLettre $inscription, EntityManagerInterface $em): Response
    {
        $lettreId = $inscription->getLettre()->getId();

        if ($this->isCsrfTokenValid('delete'.$inscription->getId(), $request->request->get('_token'))) {
            $em->remove($inscription);
            $em->flush();

            $this->addFlash('success', 'Inscription supprimée avec succès');
        }

        return $this->redirectToRoute('admin_lettre_show', ['id' => $lettreId]);
    }
}
