<?php

namespace App\Controller;

use App\Document\Permanent;
use App\Entity\InscriptionAtelier;
use App\Entity\InscriptionVisio;
use App\Entity\InscriptionLettre;
use App\Form\PermanentProfilType;
use App\Repository\PermanentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PERMANENT')]
class PermanentDashboardController extends AbstractController
{
    #[Route('/permanent/dashboard', name: 'permanent_dashboard')]
    public function dashboard(
        PermanentRepository $permanentRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // Si c'est un admin, rediriger vers le dashboard admin
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_post_index');
        }

        // Récupérer les informations du permanent connecté
        $permanent = $permanentRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        if (!$permanent) {
            throw $this->createNotFoundException('Permanent non trouvé.');
        }

        // Récupérer toutes les inscriptions du permanent via son externalUserId
        $permanentId = $permanent->getId();

        $inscriptionsAteliers = $em->getRepository(InscriptionAtelier::class)
            ->findBy(['externalUserId' => $permanentId]);

        $inscriptionsVisios = $em->getRepository(InscriptionVisio::class)
            ->findBy(['externalUserId' => $permanentId]);

        $inscriptionsLettres = $em->getRepository(InscriptionLettre::class)
            ->findBy(['externalUserId' => $permanentId]);

        // Formater les inscriptions pour le template
        $inscriptions = [];

        foreach ($inscriptionsAteliers as $inscription) {
            $inscriptions[] = [
                'type' => 'Atelier',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        foreach ($inscriptionsVisios as $inscription) {
            $inscriptions[] = [
                'type' => 'Visio',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        foreach ($inscriptionsLettres as $inscription) {
            $inscriptions[] = [
                'type' => 'Lettre',
                'inscription' => $inscription,
                'date' => $inscription->getDateInscription(),
            ];
        }

        // Trier par date décroissante
        usort($inscriptions, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return $this->render('permanent/dashboard.html.twig', [
            'permanent' => $permanent,
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/permanent/profil', name: 'permanent_profil')]
    public function profil(
        Request $request,
        PermanentRepository $permanentRepository,
        DocumentManager $dm
    ): Response {
        $user = $this->getUser();
        $permanent = $permanentRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        if (!$permanent) {
            throw $this->createNotFoundException('Permanent non trouvé.');
        }

        $form = $this->createForm(PermanentProfilType::class, $permanent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('permanent_profil');
        }

        return $this->render('permanent/profil.html.twig', [
            'permanent' => $permanent,
            'form' => $form,
        ]);
    }

    #[Route('/permanent/inscription/{type}/{id}', name: 'permanent_inscription_detail')]
    public function inscriptionDetail(
        string $type,
        int $id,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // Récupérer l'inscription selon le type
        switch ($type) {
            case 'atelier':
                $inscription = $em->getRepository(InscriptionAtelier::class)->find($id);
                $evenement = $inscription?->getAtelier();
                $typeLabel = 'Atelier';
                break;
            case 'visio':
                $inscription = $em->getRepository(InscriptionVisio::class)->find($id);
                $evenement = $inscription?->getVisio();
                $typeLabel = 'Visio';
                break;
            case 'lettre':
                $inscription = $em->getRepository(InscriptionLettre::class)->find($id);
                $evenement = $inscription?->getLettre();
                $typeLabel = 'Lettre';
                break;
            default:
                throw $this->createNotFoundException('Type d\'inscription invalide.');
        }

        if (!$inscription) {
            throw $this->createNotFoundException('Inscription non trouvée.');
        }

        // Vérifier que l'inscription appartient bien au permanent connecté
        if ($inscription->getExternalUserId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette inscription.');
        }

        return $this->render('permanent/inscription_detail.html.twig', [
            'inscription' => $inscription,
            'evenement' => $evenement,
            'type' => $typeLabel,
        ]);
    }
}
