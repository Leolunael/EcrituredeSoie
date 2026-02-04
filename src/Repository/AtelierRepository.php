<?php

namespace App\Repository;

use App\Entity\Atelier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Atelier>
 */
class AtelierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Atelier::class);
    }

    /**
     * Trouve les ateliers à venir dans les X prochains jours
     */
    public function findUpcoming(int $days = 30): array
    {
        $now = new \DateTime();
        $future = (new \DateTime())->modify("+{$days} days");

        return $this->createQueryBuilder('a')
            ->where('a.actif = :actif')
            ->andWhere('a.date BETWEEN :now AND :future')
            ->setParameter('actif', true)
            ->setParameter('now', $now)
            ->setParameter('future', $future)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'ateliers par titre, description ou type
     */
    public function search(?string $query, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.actif = :actif')
            ->setParameter('actif', true);

        if ($query) {
            $qb->andWhere('a.titre LIKE :query OR a.description LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($type && in_array($type, ['atelier', 'visio', 'courrier'])) {
            $qb->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ateliers par mois
     */
    public function findByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('a')
            ->where('a.actif = :actif')
            ->andWhere('a.date BETWEEN :start AND :end')
            ->setParameter('actif', true)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ateliers avec places disponibles
     */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.actif = :actif')
            ->andWhere('a.type = :type')
            ->andWhere('a.placesDisponibles > 0')
            ->andWhere('a.date >= :now')
            ->setParameter('actif', true)
            ->setParameter('type', 'atelier')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les ateliers par type
     */
    public function countByType(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.type, COUNT(a.id) as total')
            ->where('a.actif = :actif')
            ->setParameter('actif', true)
            ->groupBy('a.type')
            ->getQuery()
            ->getResult();

        // Reformater le résultat en tableau associatif
        $counts = [
            'atelier' => 0,
            'visio' => 0,
            'courrier' => 0
        ];

        foreach ($result as $row) {
            $counts[$row['type']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Trouve les ateliers les plus récents
     */
    public function findLatest(int $limit = 6): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ateliers passés
     */
    public function findPast(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.date < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
