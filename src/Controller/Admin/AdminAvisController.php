<?php

namespace App\Controller\Admin;

use App\Document\Avis;
use App\Form\AvisReponseType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/avis')]
#[IsGranted('ROLE_ADMIN')]
class AdminAvisController extends AbstractController
{
    #[Route('/', name: 'admin_avisList')]
    public function index(DocumentManager $dm): Response
    {
        $avis = $dm->getRepository(Avis::class)
            ->createQueryBuilder()
            ->sort('dateCreation', 'DESC')
            ->getQuery()
            ->execute();

        return $this->render('admin/avisList.html.twig', [
            'avis' => $avis,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_avisEdit')]
    public function edit(string $id, Request $request, DocumentManager $dm): Response
    {
        $avis = $dm->getRepository(Avis::class)->find($id);

        if (!$avis) {
            throw $this->createNotFoundException('Avis non trouvé');
        }

        // Créer le formulaire avec les données existantes
        $form = $this->createForm(AvisReponseType::class, [
            'reponse' => $avis->getReponse()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si c'est une demande de suppression
            if ($request->request->get('delete_response')) {
                $avis->setReponse(null);
                $avis->setAuteurReponse(null);
                $avis->setDateReponse(null);

                $dm->flush();

                $this->addFlash('success', 'La réponse a été supprimée avec succès !');
                return $this->redirectToRoute('admin_avisList');
            }

            // Sinon, c'est une mise à jour ou une création de réponse
            $data = $form->getData();

            // Enregistrer la réponse
            $avis->setReponse($data['reponse']);
            $avis->setDateReponse(new \DateTime());
            $avis->setAuteurReponse($this->getUser()->getUserIdentifier());

            $dm->flush();

            $this->addFlash('success', 'Votre réponse a été enregistrée avec succès !');

            return $this->redirectToRoute('admin_avisList');
        }

        return $this->render('admin/avisEdit.html.twig', [
            'avis' => $avis,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/valider/{id}', name: 'admin_avis_valider', methods: ['POST'])]
    public function valider(string $id, Request $request, DocumentManager $dm): Response
    {
        // Validation du token CSRF
        if (!$this->isCsrfTokenValid('valider_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_avisList');
        }

        $avis = $dm->getRepository(Avis::class)->find($id);

        if ($avis) {
            $avis->setApprouve(!$avis->isApprouve());
            $dm->flush();

            $message = $avis->isApprouve() ? 'L\'avis a été validé !' : 'L\'avis a été invalidé !';
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('admin_avisList');
    }

    #[Route('/a-la-une/{id}', name: 'admin_avis_une', methods: ['POST'])]
    public function mettreALaUne(string $id, Request $request, DocumentManager $dm): Response
    {
        // Validation du token CSRF
        if (!$this->isCsrfTokenValid('une_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_avisList');
        }

        $avis = $dm->getRepository(Avis::class)->find($id);

        if ($avis) {
            $nouvelEtat = !$avis->isALaUne();

            if ($nouvelEtat) {
                // Si on met cet avis à la une, vérifier qu'on n'en a pas déjà 3
                $avisALaUne = $dm->getRepository(Avis::class)
                    ->createQueryBuilder()
                    ->field('aLaUne')->equals(true)
                    ->field('id')->notEqual($id)
                    ->sort('dateModification', 'ASC') // Les plus anciens en premier
                    ->getQuery()
                    ->execute()
                    ->toArray();

                // Si on a déjà 3 avis à la une, retirer le plus ancien
                if (count($avisALaUne) >= 3) {
                    $avisALaUne[0]->setALaUne(false);
                    $this->addFlash('info', 'L\'avis le plus ancien a été retiré de la une.');
                }

                $avis->setALaUne(true);
                $message = 'L\'avis a été mis à la une !';
            } else {
                $avis->setALaUne(false);
                $message = 'L\'avis a été retiré de la une.';
            }

            $dm->flush();
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('admin_avisList');
    }

    #[Route('/supprimer/{id}', name: 'admin_avis_delete', methods: ['POST'])]
    public function supprimer(string $id, Request $request, DocumentManager $dm): Response
    {
        // Validation du token CSRF
        if (!$this->isCsrfTokenValid('delete_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_avisList');
        }

        $avis = $dm->getRepository(Avis::class)->find($id);

        if ($avis) {
            $dm->remove($avis);
            $dm->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_avisList');
    }

    #[Route('/page-validation', name: 'admin_avis_validation_page')]
    public function pageValidation(DocumentManager $dm): Response
    {
        $avisEnAttente = $dm->getRepository(Avis::class)->findBy(['approuve' => false], ['dateCreation' => 'DESC']);

        return $this->render('admin/avis/validation.html.twig', [
            'avis_en_attente' => $avisEnAttente,
        ]);
    }
}
