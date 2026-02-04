<?php

namespace App\Controller;

use App\Service\ImageResizerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImageUploadController extends AbstractController
{
    #[Route('/upload/image', name: 'upload_image', methods: ['POST'])]
    public function upload(
        Request $request,
        ImageResizerService $imageResizer,
        ValidatorInterface $validator
    ): Response {
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return $this->json(['error' => 'Aucune image fournie'], 400);
        }

        // Validation du fichier
        $violations = $validator->validate($uploadedFile, [
            new Assert\NotNull(['message' => 'Veuillez sélectionner une image']),
            new Assert\File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                ],
                'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WebP)',
            ])
        ]);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 400);
        }

        try {
            // Redimensionner et sauvegarder l'image
            $fileName = $imageResizer->resize($uploadedFile);

            return $this->json([
                'success' => true,
                'fileName' => $fileName,
                'message' => 'Image uploadée et redimensionnée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du traitement de l\'image: ' . $e->getMessage()
            ], 500);
        }
    }
}
