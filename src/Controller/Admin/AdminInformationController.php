<?php

namespace App\Controller\Admin;

use App\Document\Information;
use App\Form\InformationType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/information')]
#[IsGranted('ROLE_ADMIN')]
class AdminInformationController extends AbstractController
{
    #[Route('/', name: 'admin_information')]
    public function index(DocumentManager $dm): Response
    {
        $informations = $dm->getRepository(Information::class)
            ->createQueryBuilder()
            ->sort('ordre', 'ASC')
            ->getQuery()
            ->execute();

        return $this->render('admin/information.html.twig', [
            'informations' => $informations,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_information_nouveau')]
    public function nouveau(Request $request, DocumentManager $dm, SluggerInterface $slugger): Response
    {
        $information = new Information();
        $form = $this->createForm(InformationType::class, $information);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('informations_images_directory'),
                        $newFilename
                    );
                    $information->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $dm->persist($information);
            $dm->flush();

            $this->addFlash('success', 'L\'information a été créée avec succès !');
            return $this->redirectToRoute('admin_information');
        }

        return $this->render('admin/informationForm.html.twig', [
            'form' => $form->createView(),
            'information' => $information,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_information_modifier')]
    public function modifier(string $id, Request $request, DocumentManager $dm, SluggerInterface $slugger): Response
    {
        $information = $dm->getRepository(Information::class)->find($id);

        if (!$information) {
            throw $this->createNotFoundException('Cette information n\'existe pas.');
        }

        $form = $this->createForm(InformationType::class, $information);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                if ($information->getImage()) {
                    $oldImagePath = $this->getParameter('informations_images_directory').'/'.$information->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('informations_images_directory'),
                        $newFilename
                    );
                    $information->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $dm->flush();

            $this->addFlash('success', 'L\'information a été modifiée avec succès !');
            return $this->redirectToRoute('admin_information');
        }

        return $this->render('admin/informationForm.html.twig', [
            'form' => $form->createView(),
            'information' => $information,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_information_supprimer', methods: ['POST'])]
    public function supprimer(string $id, DocumentManager $dm): Response
    {
        $information = $dm->getRepository(Information::class)->find($id);

        if ($information) {
            if ($information->getImage()) {
                $imagePath = $this->getParameter('informations_images_directory').'/'.$information->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $dm->remove($information);
            $dm->flush();
            $this->addFlash('success', 'L\'information a été supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_information');
    }
}
