<?php

namespace App\Repository;

use App\Document\Post;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class PostRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        parent::__construct($dm, $dm->getUnitOfWork(), $dm->getClassMetadata(Post::class));
    }

    public function findAllPublished(): array
    {
        return $this->findBy(
            ['publie' => true],
            ['dateCreation' => 'DESC']
        );
    }

    public function findByAuteur(string $auteurId): array
    {
        return $this->findBy(
            ['auteurId' => $auteurId],
            ['dateCreation' => 'DESC']
        );
    }

    public function countTotal(): int
    {
        return $this->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    public function countPublished(): int
    {
        return $this->createQueryBuilder()
            ->field('publie')->equals(true)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder()
            ->field('publie')->equals(true)
            ->sort('dateCreation', 'DESC')
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
