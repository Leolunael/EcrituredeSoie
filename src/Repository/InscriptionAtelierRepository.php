<?php

namespace App\Repository;

use App\Entity\InscriptionAtelier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscriptionAtelier>
 */
class InscriptionAtelierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionAtelier::class);
    }

    /**
     * Trouve toutes les inscriptions pour un atelier donné
     */
    public function findByAtelier(int $atelierId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.atelier = :atelier')
            ->setParameter('atelier', $atelierId)
            ->orderBy('i.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'inscriptions pour un atelier
     */
    public function countByAtelier(int $atelierId): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.atelier = :atelier')
            ->setParameter('atelier', $atelierId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
