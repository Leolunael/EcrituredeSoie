<?php

namespace App\Repository;

use App\Entity\PermPresentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PermPresentation>
 */
class PermPresentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermPresentation::class);
    }

    /**
     * Récupère la présentation active (il n'y en a qu'une seule)
     */
    public function getPresentation(): ?PermPresentation
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
