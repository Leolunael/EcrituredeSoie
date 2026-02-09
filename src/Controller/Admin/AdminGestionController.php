<?php

namespace App\Controller\Admin;

use App\Repository\AtelierRepository;
use App\Repository\LettreRepository;
use App\Repository\VisioRepository;
use App\Repository\VollonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminGestionController extends AbstractController
{
    #[Route('/gestion', name: 'admin_gestion')]
    public function list(
        Request $request,
        AtelierRepository $atelierRepo,
        VisioRepository $visioRepo,
        VollonRepository $vollonRepo,
        LettreRepository $lettreRepo
    ): Response
    {
        $statut = $request->query->get('statut', 'all');

        // Charger les ateliers
        if ($statut === 'actif') {
            $ateliers = $atelierRepo->findBy(['isArchive' => false], ['dateAtelier' => 'DESC']);
        } elseif ($statut === 'archive') {
            $ateliers = $atelierRepo->findBy(['isArchive' => true], ['dateAtelier' => 'DESC']);
        } else {
            $ateliers = $atelierRepo->findBy([], ['dateAtelier' => 'DESC']);
        }

        // Charger les visios
        if ($statut === 'actif') {
            $visios = $visioRepo->findBy(['isArchive' => false], ['dateVisio' => 'DESC']);
        } elseif ($statut === 'archive') {
            $visios = $visioRepo->findBy(['isArchive' => true], ['dateVisio' => 'DESC']);
        } else {
            $visios = $visioRepo->findBy([], ['dateVisio' => 'DESC']);
        }

        // Charger Vollon
        if ($statut === 'actif') {
            $vollons = $vollonRepo->findBy(['isArchive' => false], ['dateVollon' => 'DESC']);
        } elseif ($statut === 'archive') {
            $vollons = $vollonRepo->findBy(['isArchive' => true], ['dateVollon' => 'DESC']);
        } else {
            $vollons = $vollonRepo->findBy([], ['dateVollon' => 'DESC']);
        }

        // Charger les lettres
        if ($statut === 'actif') {
            $lettres = $lettreRepo->findBy(['isArchive' => false], ['id' => 'DESC']);
        } elseif ($statut === 'archive') {
            $lettres = $lettreRepo->findBy(['isArchive' => true], ['id' => 'DESC']);
        } else {
            $lettres = $lettreRepo->findBy([], ['id' => 'DESC']);
        }

        return $this->render('admin/AdminAtelier.html.twig', [
            'ateliers' => $ateliers,
            'visios' => $visios,
            'vollons' => $vollons,
            'lettres' => $lettres,
            'statut' => $statut
        ]);
    }
}
