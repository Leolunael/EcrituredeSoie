<?php

namespace App\Repository;

use App\Entity\Presentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PresentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presentation::class);
    }

    /**
     * Récupère tous les contenus actifs triés par ordre
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('c.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un contenu par sa clé
     */
    public function findByCle(string $cle): ?Presentation
    {
        return $this->createQueryBuilder('c')
            ->where('c.cle = :cle')
            ->andWhere('c.actif = :actif')
            ->setParameter('cle', $cle)
            ->setParameter('actif', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
