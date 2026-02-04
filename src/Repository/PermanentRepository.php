<?php

namespace App\Repository;

use App\Document\Permanent;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class PermanentRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        parent::__construct($dm, $dm->getUnitOfWork(), $dm->getClassMetadata(Permanent::class));
    }

    /**
     * Trouve un permanent par son email
     */
    public function findByEmail(string $email): ?Permanent
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Compte le nombre total de permanents
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * Compte le nombre de permanents archivés
     */
    public function countArchives(): int
    {
        return $this->createQueryBuilder()
            ->field('archive')->equals(true)
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * Compte le nombre de permanents actifs (non archivés)
     */
    public function countActifs(): int
    {
        return $this->createQueryBuilder()
            ->field('archive')->equals(false)
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * Compte le nombre de permanents avec paiement en attente
     */
    public function countPaiementsEnAttente(): int
    {
        return $this->createQueryBuilder()
            ->field('paiementEffectue')->equals(false)
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * Trouve les inscriptions récentes
     */
    public function findRecentInscriptions(int $limit = 10): array
    {
        return $this->createQueryBuilder()
            ->sort('dateInscription', 'DESC')
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Trouve les permanents par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder()
            ->field('roles')->in([$role])
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Recherche des permanents par nom, prénom ou email
     */
    public function search(string $terme): array
    {
        $regex = new \MongoDB\BSON\Regex($terme, 'i');

        return $this->createQueryBuilder()
            ->addOr($this->createQueryBuilder()->field('nom')->equals($regex))
            ->addOr($this->createQueryBuilder()->field('prenom')->equals($regex))
            ->addOr($this->createQueryBuilder()->field('email')->equals($regex))
            ->sort('nom', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Trouve les permanents avec paiements effectués
     */
    public function findPaiementsEffectues(): array
    {
        return $this->createQueryBuilder()
            ->field('paiementEffectue')->equals(true)
            ->sort('datePaiement', 'DESC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Trouve les permanents avec paiements en attente
     */
    public function findPaiementsEnAttente(): array
    {
        return $this->createQueryBuilder()
            ->field('paiementEffectue')->equals(false)
            ->sort('dateInscription', 'DESC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Trouve les permanents actifs (non archivés)
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder()
            ->field('archive')->equals(false)
            ->sort('nom', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Trouve les permanents archivés
     */
    public function findArchives(): array
    {
        return $this->createQueryBuilder()
            ->field('archive')->equals(true)
            ->sort('nom', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Statistiques avancées
     */
    public function getStatistiques(): array
    {
        return [
            'total' => $this->countTotal(),
            'actifs' => $this->countActifs(),
            'archives' => $this->countArchives(),
            'paiements_effectues' => $this->createQueryBuilder()
                ->field('paiementEffectue')->equals(true)
                ->count()
                ->getQuery()
                ->execute(),
            'paiements_en_attente' => $this->countPaiementsEnAttente(),
            'admins' => $this->createQueryBuilder()
                ->field('roles')->in(['ROLE_ADMIN'])
                ->count()
                ->getQuery()
                ->execute(),
        ];
    }
}
