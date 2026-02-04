<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/contact')]
#[IsGranted('ROLE_ADMIN')]
class AdminContactController extends AbstractController
{
    #[Route('/', name: 'admin_contactList')]
    public function index(Request $request, ContactRepository $contactRepo): Response
    {
        // Récupération des filtres
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', 'all'); // all, traite, non_traite

        // Construction de la requête
        $qb = $contactRepo->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');

        // Filtre par recherche (nom ou email)
        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :search OR c.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par statut
        if ($statut === 'traite') {
            $qb->andWhere('c.traite = :traite')
                ->setParameter('traite', true);
        } elseif ($statut === 'non_traite') {
            $qb->andWhere('c.traite = :traite')
                ->setParameter('traite', false);
        }

        $contacts = $qb->getQuery()->getResult();

        return $this->render('admin/contactList.html.twig', [
            'contacts' => $contacts,
            'search' => $search,
            'statut' => $statut,
        ]);
    }

    #[Route('/{id}', name: 'admin_contactDetail', requirements: ['id' => '\d+'])]
    public function show(Contact $contact, EntityManagerInterface $em): Response
    {
        // Marquer comme lu automatiquement
        if (!$contact->isLu()) {
            $contact->setLu(true);
            $em->flush();
        }

        return $this->render('admin/contactDetail.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/traiter/{id}', name: 'admin_contact_traiter', methods: ['POST'])]
    public function traiter(Contact $contact, EntityManagerInterface $em): Response
    {
        $contact->setTraite(!$contact->isTraite());
        $em->flush();

        $message = $contact->isTraite()
            ? 'Le contact a été marqué comme traité.'
            : 'Le contact a été marqué comme non traité.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_contactList');
    }

    #[Route('/supprimer/{id}', name: 'admin_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $em->remove($contact);
            $em->flush();

            $this->addFlash('success', 'Le contact a été supprimé.');
        }

        return $this->redirectToRoute('admin_contactList');
    }
}
