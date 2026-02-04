<?php

namespace App\Controller\Admin;

use App\Entity\Intro;
use App\Form\IntroType;
use App\Repository\IntroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/intro')]
#[IsGranted('ROLE_ADMIN')]
class AdminIntroController extends AbstractController
{
    #[Route('/', name: 'admin_intro', methods: ['GET'])]
    public function index(IntroRepository $introRepository, EntityManagerInterface $em): Response
    {
        $intros = $introRepository->findAll();


        return $this->render('admin/intro.html.twig', [
            'intros' => $intros,
        ]);
    }

    #[Route('/nouveau', name: 'admin_introForm')]
    public function nouveau(Request $request, IntroRepository $introRepository, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $intro = new Intro();
        $form = $this->createForm(IntroType::class, $intro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('intros_images_directory'),
                        $newFilename
                    );
                    $intro->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->persist($intro);
            $em->flush();

            $this->addFlash('success', 'L\'information a été créée avec succès !');
            return $this->redirectToRoute('admin_intro');
        }

        return $this->render('admin/introForm.html.twig', [
            'form' => $form->createView(),
            'intro' => $intro,
        ]);

    }

    #[Route('/{id}/deleteImage', name: 'admin_intro_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request, Intro $intro, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-image' . $intro->getId(), $request->request->get('_token'))) {
            if ($intro->getImage()) {
                $imagePath = $this->getParameter('intros_images_directory') . '/' . $intro->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $intro->setImage(null);
                $em->flush();

                $this->addFlash('success', 'L\'image a été supprimée avec succès');
            }
        }

        return $this->redirectToRoute('admin_introForm');
    }

    #[Route('/{id}/modifier', name: 'app_admin_intro_modifier')]
    public function modifier(string $id, Request $request, Intro $intro, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $intro = $em->getRepository(Intro::class)->find($id);

        if (!$intro) {
            throw $this->createNotFoundException('Cette information n\'existe pas.');
        }

        $form = $this->createForm(IntroType::class, $intro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                if ($intro->getImage()) {
                    $oldImagePath = $this->getParameter('intros_images_directory') . '/' . $intro->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('intros_images_directory'),
                        $newFilename
                    );
                    $intro->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $em->flush();

            $this->addFlash('success', 'L\'information a été modifiée avec succès !');
            return $this->redirectToRoute('admin_intro');
        }

        return $this->render('admin/introForm.html.twig', [
            'form' => $form->createView(),
            'intro' => $intro,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_intro_supprimer', methods: ['POST'])]
    public function supprimer(string $id, Intro $intro, EntityManagerInterface $em): Response
    {
        $intro = $em->getRepository(Intro::class)->find($id);

        if ($intro) {
            if ($intro->getImage()) {
                $imagePath = $this->getParameter('intros_images_directory').'/'.$intro->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $em->remove($intro);
            $em->flush();
            $this->addFlash('success', 'L\'information a été supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_intro');
    }

}
