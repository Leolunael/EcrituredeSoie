<?php

namespace App\Controller;

use App\Document\Permanent;
use App\Form\PermanentInscriptionType;
use App\Repository\PermanentRepository;
use App\Repository\PermPresentationRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/permanent')]
class PermanentController extends AbstractController
{
    #[Route('/', name: 'app_permanent_accueil')]
    public function accueil(PermPresentationRepository $presentationRepo): Response
    {
        // ✅ Si l'utilisateur est déjà connecté en tant que Permanent, rediriger vers les posts
        if ($this->getUser() && $this->isGranted('ROLE_PERMANENT')) {
            return $this->redirectToRoute('app_post_index');
        }

        $presentation = $presentationRepo->getPresentation();

        // Si aucune présentation n'existe, créer des valeurs par défaut
        if (!$presentation) {
            $presentation = new \stdClass();
            $presentation->titre = "Rejoignez notre communauté";
            $presentation->description = "L'Espace Permanent vous permet de publier des posts avec images, PDF, vidéos et musiques, commenter et échanger avec la communauté.";
            $presentation->avantages = "Publier des posts avec images, PDF, vidéos et musiques\nCommenter et échanger avec la communauté\nPartager vos contenus multimédias\nAccéder à un espace privilégié";
        }

        return $this->render('permanent/accueil.html.twig', [
            'presentation' => $presentation,
        ]);
    }

    #[Route('/inscription', name: 'app_permanent_inscription')]
    public function inscription(
        Request $request,
        DocumentManager $dm,
        UserPasswordHasherInterface $passwordHasher,
        PermanentRepository $permanentRepository
    ): Response {
        // ✅ Si déjà connecté en tant que Permanent, rediriger vers les posts
        if ($this->getUser() && $this->isGranted('ROLE_PERMANENT')) {
            return $this->redirectToRoute('app_post_index');
        }

        $permanent = new Permanent();
        $form = $this->createForm(PermanentInscriptionType::class, $permanent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà
            $existingPermanent = $permanentRepository->findByEmail($permanent->getEmail());
            if ($existingPermanent) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('permanent/inscription.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $permanent,
                $permanent->getPassword()
            );
            $permanent->setPassword($hashedPassword);

            // Sauvegarder
            $dm->persist($permanent);
            $dm->flush();

            $this->addFlash('success', 'Inscription réussie ! Veuillez procéder au paiement.');

            // Redirection vers HelloAsso
            return $this->redirect($this->generateHelloAssoUrl($permanent));
        }

        return $this->render('permanent/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/paiement/retour', name: 'app_permanent_paiement_retour')]
    public function paiementRetour(Request $request, DocumentManager $dm): Response
    {
        // Récupérer les paramètres de retour HelloAsso
        $transactionId = $request->query->get('transaction_id');
        $permanentEmail = $request->query->get('email');
        $status = $request->query->get('status');

        if ($status === 'success' && $transactionId && $permanentEmail) {
            $permanent = $dm->getRepository(Permanent::class)->findOneBy(['email' => $permanentEmail]);

            if ($permanent) {
                $permanent->setPaiementEffectue(true);
                $permanent->setHelloAssoTransactionId($transactionId);
                $permanent->setDatePaiement(new \DateTime());

                $dm->flush();

                $this->addFlash('success', 'Paiement effectué avec succès ! Votre compte est maintenant actif.');
                return $this->redirectToRoute('app_login');
            }
        }

        $this->addFlash('error', 'Une erreur est survenue lors du paiement.');
        return $this->redirectToRoute('app_permanent_accueil');
    }

    private function generateHelloAssoUrl(Permanent $permanent): string
    {
        // Configuration HelloAsso - À adapter selon votre compte
        $baseUrl = 'https://www.helloasso.com/associations/ecriture-de-soie';

        // Paramètres à passer à HelloAsso
        $params = http_build_query([
            'email' => $permanent->getEmail(),
            'firstName' => $permanent->getPrenom(),
            'lastName' => $permanent->getNom(),
            'amount' => 5000, // Montant en centimes (50,00€)
            'returnUrl' => $this->generateUrl('app_permanent_paiement_retour', [
                'email' => $permanent->getEmail()
            ], 0) // 0 pour URL absolue
        ]);

        return $baseUrl . '?' . $params;
    }
}
