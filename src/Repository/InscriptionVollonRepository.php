<?php

namespace App\Repository;

use App\Entity\InscriptionVollon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscriptionVollon>
 */
class InscriptionVollonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionVollon::class);
    }

    /**
     * Trouve toutes les inscriptions pour un atelier donné
     */
    public function findByVollon(int $vollonId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.vollon = :vollon')
            ->setParameter('vollon', $vollonId)
            ->orderBy('i.dateInscription', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByVollon(int $vollonId): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.vollon = :vollon')
            ->setParameter('vollon', $vollonId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
