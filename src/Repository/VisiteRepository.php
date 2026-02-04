<?php

namespace App\Repository;

use App\Entity\Visite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }

    /**
     * Compte le nombre total de visites
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les visiteurs uniques (par IP)
     */
    public function countUniqueVisitors(): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.ipAddress)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les visites aujourd'hui
     */
    public function countToday(): int
    {
        $today = new \DateTime('today');

        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.dateVisite >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les visiteurs uniques aujourd'hui
     */
    public function countUniqueTodayVisitors(): int
    {
        $today = new \DateTime('today');

        return $this->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.ipAddress)')
            ->where('v.dateVisite >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les visites cette semaine
     */
    public function countThisWeek(): int
    {
        $startOfWeek = new \DateTime('monday this week');

        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.dateVisite >= :start')
            ->setParameter('start', $startOfWeek)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les visites ce mois
     */
    public function countThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month');

        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.dateVisite >= :start')
            ->setParameter('start', $startOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtient les statistiques par jour pour les 7 derniers jours
     */
    public function getStatsByDay(int $days = 7): array
    {
        $startDate = new \DateTime("-{$days} days");

        return $this->createQueryBuilder('v')
            ->select('SUBSTRING(v.dateVisite, 1, 10) as date, COUNT(v.id) as visites')
            ->where('v.dateVisite >= :start')
            ->setParameter('start', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtient les pages les plus visitées
     */
    public function getTopPages(int $limit = 10): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.url, COUNT(v.id) as visites')
            ->where('v.url IS NOT NULL')
            ->groupBy('v.url')
            ->orderBy('visites', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtient les dernières visites
     */
    public function getRecentVisites(int $limit = 10): array
    {
        return $this->createQueryBuilder('v')
            ->orderBy('v.dateVisite', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une IP a déjà visité aujourd'hui
     */
    public function hasVisitedToday(string $ip): bool
    {
        $today = new \DateTime('today');

        $count = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.ipAddress = :ip')
            ->andWhere('v.dateVisite >= :today')
            ->setParameter('ip', $ip)
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Nettoie les anciennes visites (optionnel)
     */
    public function cleanOldVisites(int $daysToKeep = 365): int
    {
        $dateLimit = new \DateTime("-{$daysToKeep} days");

        return $this->createQueryBuilder('v')
            ->delete()
            ->where('v.dateVisite < :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->execute();
    }
}
