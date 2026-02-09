<?php

namespace App\Controller;

use App\Entity\InscriptionAtelier;
use App\Entity\InscriptionLettre;
use App\Entity\InscriptionVisio;
use App\Entity\InscriptionVollon;
use App\Form\InscriptionAtelierType;
use App\Form\InscriptionLettreType;
use App\Form\InscriptionVisioType;
use App\Form\InscriptionVollonType;
use App\Repository\AtelierRepository;
use App\Repository\LettreRepository;
use App\Repository\VisioRepository;
use App\Repository\VollonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AteliersController extends AbstractController
{
    // ==================== PAGE D'ACCUEIL (TOUT) ====================

    #[Route('/ateliers', name: 'app_ateliers')]
    public function list(
        AtelierRepository $atelierRepo,
        VisioRepository $visioRepo,
        VollonRepository $vollonRepo,
        LettreRepository $lettreRepo
    ): Response {
        $ateliers = $atelierRepo->findBy(
            ['isArchive' => false],
            ['dateAtelier' => 'ASC']
        );

        $visios = $visioRepo->findBy(
            ['isArchive' => false],
            ['dateVisio' => 'ASC']
        );

        $vollons = $vollonRepo->findBy(
            ['isArchive' => false],
            ['dateVollon' => 'ASC']
        );

        $lettres = $lettreRepo->findBy(
            ['isArchive' => false]
        );

        return $this->render('ateliers/index.html.twig', [
            'ateliers' => $ateliers,
            'visios' => $visios,
            'vollons' => $vollons,
            'lettres' => $lettres,
        ]);
    }

    // ==================== ATELIERS ====================

    #[Route('/ateliers/{id}', name: 'app_atelier_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        Request $request,
        AtelierRepository $atelierRepo,
        EntityManagerInterface $em
    ): Response {
        $atelier = $atelierRepo->find($id);

        if (!$atelier) {
            throw $this->createNotFoundException('Cet atelier n\'existe pas.');
        }

        if ($atelier->isArchive()) {
            throw $this->createNotFoundException('Cet atelier n\'est plus disponible.');
        }

        $inscription = new InscriptionAtelier();
        $inscription->setAtelier($atelier);

        // Si l'utilisateur est connecté, pré-remplir les informations
        if ($this->getUser()) {
            $user = $this->getUser();
            // ✅ CHANGÉ: setUser() → setUserFromInterface()
            $inscription->setUserFromInterface($user);

            if (method_exists($user, 'getNom')) {
                $inscription->setName($user->getNom());
            }
            if (method_exists($user, 'getPrenom')) {
                $inscription->setPrenom($user->getPrenom());
            }
            $inscription->setEmail($user->getEmail());
        }

        $form = $this->createForm(InscriptionAtelierType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setDateInscription(new \DateTime());
            $inscription->setAtelier($atelier);

            // Associer l'utilisateur si connecté
            if ($this->getUser()) {
                // ✅ CHANGÉ: setUser() → setUserFromInterface()
                $inscription->setUserFromInterface($this->getUser());
            }

            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Votre inscription a bien été enregistrée !');

            if ($inscription->getMoyenPaiement() === 'cb' && $atelier->getLienHelloAsso()) {
                return $this->redirect($atelier->getLienHelloAsso());
            }

            return $this->redirectToRoute('app_atelier_show', [
                'id' => $atelier->getId(),
                'inscrit' => true
            ]);
        }

        return $this->render('ateliers/show.html.twig', [
            'atelier' => $atelier,
            'form' => $form,
            'inscrit' => $request->query->get('inscrit', false)
        ]);
    }

    // ==================== VISIOS ====================

    #[Route('/visio', name: 'app_visio')]
    public function listVisios(
        AtelierRepository $atelierRepo,
        VisioRepository $visioRepo,
        VollonRepository $vollonRepo,
        LettreRepository $lettreRepo
    ): Response {
        // Charger tous les éléments pour éviter les erreurs dans le template
        $ateliers = $atelierRepo->findBy(
            ['isArchive' => false],
            ['dateAtelier' => 'ASC']
        );

        $visios = $visioRepo->findBy(
            ['isArchive' => false],
            ['dateVisio' => 'ASC']
        );

        $vollons = $vollonRepo->findBy(
            ['isArchive' => false],
            ['dateVollon' => 'ASC']
        );

        $lettres = $lettreRepo->findBy(
            ['isArchive' => false]
        );

        return $this->render('ateliers/index.html.twig', [
            'ateliers' => $ateliers,
            'visios' => $visios,
            'vollons' => $vollons,
            'lettres' => $lettres,
        ]);
    }

    #[Route('/visio/{id}', name: 'app_visio_show', requirements: ['id' => '\d+'])]
    public function showVisio(
        int $id,
        Request $request,
        VisioRepository $visioRepo,
        EntityManagerInterface $em
    ): Response {
        $visio = $visioRepo->find($id);

        if (!$visio) {
            throw $this->createNotFoundException('Cette visio n\'existe pas.');
        }

        if ($visio->isArchive()) {
            throw $this->createNotFoundException('Cette visio n\'est plus disponible.');
        }

        $inscription = new InscriptionVisio();
        $inscription->setVisio($visio);

        // Si l'utilisateur est connecté, pré-remplir les informations
        if ($this->getUser()) {
            $user = $this->getUser();
            // ✅ CHANGÉ: setUser() → setUserFromInterface()
            $inscription->setUserFromInterface($user);

            if (method_exists($user, 'getNom')) {
                $inscription->setName($user->getNom());
            }
            if (method_exists($user, 'getPrenom')) {
                $inscription->setPrenom($user->getPrenom());
            }
            $inscription->setEmail($user->getEmail());
        }

        $form = $this->createForm(InscriptionVisioType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setDateInscription(new \DateTime());
            $inscription->setVisio($visio);

            // Associer l'utilisateur si connecté
            if ($this->getUser()) {
                // ✅ CHANGÉ: setUser() → setUserFromInterface()
                $inscription->setUserFromInterface($this->getUser());
            }

            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Votre inscription à la visio a bien été enregistrée !');

            if ($inscription->getMoyenPaiement() === 'cb' && $visio->getLienHelloAsso()) {
                return $this->redirect($visio->getLienHelloAsso());
            }

            return $this->redirectToRoute('app_visio_show', [
                'id' => $visio->getId(),
                'inscrit' => true
            ]);
        }

        return $this->render('ateliers/visio.html.twig', [
            'visio' => $visio,
            'form' => $form,
            'inscrit' => $request->query->get('inscrit', false)
        ]);
    }

    // ==================== VOLLON ====================

    #[Route('/vollon', name: 'app_vollon')]
    public function listVollon(
        AtelierRepository $atelierRepo,
        VisioRepository $visioRepo,
        VollonRepository $vollonRepo,
        LettreRepository $lettreRepo
    ): Response {
        // Charger tous les éléments pour éviter les erreurs dans le template
        $ateliers = $atelierRepo->findBy(
            ['isArchive' => false],
            ['dateAtelier' => 'ASC']
        );

        $visios = $visioRepo->findBy(
            ['isArchive' => false],
            ['dateVisio' => 'ASC']
        );

        $vollons = $vollonRepo->findBy(
            ['isArchive' => false],
            ['dateVollon' => 'ASC']
        );

        $lettres = $lettreRepo->findBy(
            ['isArchive' => false]
        );

        return $this->render('ateliers/index.html.twig', [
            'ateliers' => $ateliers,
            'visios' => $visios,
            'vollons' => $vollons,
            'lettres' => $lettres,
        ]);
    }

    #[Route('/vollon/{id}', name: 'app_vollon_show', requirements: ['id' => '\d+'])]
    public function showVollon(
        int $id,
        Request $request,
        VollonRepository $vollonRepo,
        EntityManagerInterface $em
    ): Response {
        $vollon = $vollonRepo->find($id);

        if (!$vollon) {
            throw $this->createNotFoundException('Cet atelier n\'existe pas.');
        }

        if ($vollon->isArchive()) {
            throw $this->createNotFoundException('Cet atelier n\'est plus disponible.');
        }

        $inscription = new InscriptionVollon();
        $inscription->setVollon($vollon);

        // Si l'utilisateur est connecté, pré-remplir les informations
        if ($this->getUser()) {
            $user = $this->getUser();
            // ✅ CHANGÉ: setUser() → setUserFromInterface()
            $inscription->setUserFromInterface($user);

            if (method_exists($user, 'getName')) {
                $inscription->setName($user->getName());
            }
            if (method_exists($user, 'getPrenom')) {
                $inscription->setPrenom($user->getPrenom());
            }
            $inscription->setEmail($user->getEmail());
        }

        $form = $this->createForm(InscriptionVollonType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setDateInscription(new \DateTime());
            $inscription->setVollon($vollon);

            // Associer l'utilisateur si connecté
            if ($this->getUser()) {
                // ✅ CHANGÉ: setUser() → setUserFromInterface()
                $inscription->setUserFromInterface($this->getUser());
            }

            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Votre inscription à Vollon a bien été enregistrée !');

            if ($inscription->getMoyenPaiement() === 'cb' && $vollon->getLienHelloAsso()) {
                return $this->redirect($vollon->getLienHelloAsso());
            }

            return $this->redirectToRoute('app_vollon_show', [
                'id' => $vollon->getId(),
                'inscrit' => true
            ]);
        }

        return $this->render('ateliers/vollon.html.twig', [
            'vollon' => $vollon,
            'form' => $form,
            'inscrit' => $request->query->get('inscrit', false)
        ]);
    }

    // ==================== COURRIERS ====================

    #[Route('/lettres', name: 'app_lettre')]
    public function listLettres(
        AtelierRepository $atelierRepo,
        VisioRepository $visioRepo,
        VollonRepository $vollonRepo,
        LettreRepository $lettreRepo
    ): Response {
        // Charger tous les éléments pour éviter les erreurs dans le template
        $ateliers = $atelierRepo->findBy(
            ['isArchive' => false],
            ['dateAtelier' => 'ASC']
        );

        $visios = $visioRepo->findBy(
            ['isArchive' => false],
            ['dateVisio' => 'ASC']
        );

        $vollons = $vollonRepo->findBy(
            ['isArchive' => false],
            ['dateVollon' => 'ASC']
        );

        $lettres = $lettreRepo->findBy(
            ['isArchive' => false]
        );

        return $this->render('ateliers/index.html.twig', [
            'ateliers' => $ateliers,
            'visios' => $visios,
            'vollons' => $vollons,
            'lettres' => $lettres,
        ]);
    }

    #[Route('/lettres/{id}', name: 'app_lettre_show', requirements: ['id' => '\d+'])]
    public function showLettres(
        int $id,
        Request $request,
        LettreRepository $lettreRepo,
        EntityManagerInterface $em
    ): Response {
        $lettre = $lettreRepo->find($id);

        if (!$lettre) {
            throw $this->createNotFoundException('Cette lettre n\'existe pas.');
        }

        if ($lettre->isArchive()) {
            throw $this->createNotFoundException('Cette lettre n\'est plus disponible.');
        }

        $inscription = new InscriptionLettre();
        $inscription->setLettre($lettre);

        // Si l'utilisateur est connecté, pré-remplir les informations
        if ($this->getUser()) {
            $user = $this->getUser();
            // ✅ CHANGÉ: setUser() → setUserFromInterface()
            $inscription->setUserFromInterface($user);

            if (method_exists($user, 'getNom')) {
                $inscription->setNom($user->getNom()); // ✅ Attention: setNom() pas setName() pour Lettre
            }
            if (method_exists($user, 'getPrenom')) {
                $inscription->setPrenom($user->getPrenom());
            }
            $inscription->setEmail($user->getEmail());
        }

        $form = $this->createForm(InscriptionLettreType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->setLettre($lettre);

            // Associer l'utilisateur si connecté
            if ($this->getUser()) {
                // ✅ CHANGÉ: setUser() → setUserFromInterface()
                $inscription->setUserFromInterface($this->getUser());
            }

            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Votre inscription au courrier a bien été enregistrée !');

            if ($inscription->getMoyenPaiement() === 'cb' && $lettre->getLienHelloAsso()) {
                return $this->redirect($lettre->getLienHelloAsso());
            }

            return $this->redirectToRoute('app_lettre_show', [
                'id' => $lettre->getId(),
                'inscrit' => true
            ]);
        }

        return $this->render('ateliers/lettre.html.twig', [
            'lettre' => $lettre,
            'form' => $form,
            'inscrit' => $request->query->get('inscrit', false)
        ]);
    }
}
