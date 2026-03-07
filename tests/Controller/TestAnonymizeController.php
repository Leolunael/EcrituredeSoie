<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestAnonymizeController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/test-anonymize/{id}', name: 'test_anonymize')]
    public function testAnonymize(int $id): Response
    {
        $user = $this->userRepository->find($id);

        // Vérifie que l'utilisateur existe
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur $id introuvable");
        }

        // Sauvegarde les données AVANT
        $avant = [
            'email'      => $user->getEmail(),
            'prenom'     => $user->getPrenom(),
            'nom'        => $user->getNom(),
            'roles'      => $user->getRoles(),
            'isVerified' => $user->isVerified(),
        ];

        // Anonymise
        $user->anonymize();
        $this->entityManager->flush();

        // Données APRÈS
        $apres = [
            'email'      => $user->getEmail(),
            'prenom'     => $user->getPrenom(),
            'nom'        => $user->getNom(),
            'roles'      => $user->getRoles(),
            'isVerified' => $user->isVerified(),
            'deletedAt'  => $user->getDeletedAt(),
        ];

        // Affiche avant/après dans le navigateur
        dd([
            'AVANT' => $avant,
            'APRES' => $apres,
        ]);
    }
}
