<?php

namespace App\Repository;

use App\Document\Texte;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;


class TexteRepository
{
    private DocumentRepository $repository;

    public function __construct(DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(Texte::class);
    }

    public function createQueryBuilder(): Builder
    {
        return $this->repository->createQueryBuilder();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findApprouvesByTexte(string $texteId): array
    {
        return $this->repository->findBy(
            ['texte' => $texteId, 'approuve' => true],
            ['dateCreation' => 'ASC']
        );
    }
}
