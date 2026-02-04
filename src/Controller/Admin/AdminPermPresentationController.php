<?php

namespace App\Controller\Admin;

use App\Entity\PermPresentation;
use App\Form\PermPresentationType;
use App\Repository\PermPresentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminPermPresentationController extends AbstractController
{
    #[Route('/permanent/presentation', name: 'admin_permanent_presentation')]
    public function index(
        Request $request,
        PermPresentationRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $presentation = $repo->getPresentation();

        if (!$presentation) {
            $presentation = new PermPresentation();
            $presentation->setTitre('Rejoignez notre communauté');
            $presentation->setDescription('L\'Espace Permanent vous permet de publier des posts avec images, PDF, vidéos et musiques, commenter et échanger avec la communauté.');
            $presentation->setAvantages("Publier des posts avec images, PDF, vidéos et musiques\nCommenter et échanger avec la communauté\nPartager vos contenus multimédias\nAccéder à un espace privilégié");
        }

        $form = $this->createForm(PermPresentationType::class, $presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $presentation->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($presentation);
            $em->flush();

            $this->addFlash('success', 'La présentation a été mise à jour avec succès !');
            return $this->redirectToRoute('admin_permanent_presentation');
        }

        return $this->render('admin/permanent/presentation.html.twig', [
            'form' => $form->createView(),
            'presentation' => $presentation,
        ]);
    }
}
