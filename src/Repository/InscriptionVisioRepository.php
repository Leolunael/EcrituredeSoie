<?php

namespace App\Repository;

use App\Entity\InscriptionVisio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscriptionVisio>
 */
class InscriptionVisioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionVisio::class);
    }

    /**
     * Trouve toutes les inscriptions pour un visio donné
     */
    public function findByVisio(int $visioId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.visio = :visio')
            ->setParameter('visio', $visioId)
            ->orderBy('i.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'inscriptions pour un visio
     */
    public function countByVisio(int $visioId): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.visio = :visio')
            ->setParameter('visio', $visioId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
