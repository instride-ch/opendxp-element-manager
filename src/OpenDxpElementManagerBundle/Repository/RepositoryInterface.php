<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Repository;

interface RepositoryInterface
{
    public function find(int|string $id): ?object;

    /** @return object[] */
    public function findAll(): array;

    /** @return object[] */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function findOneBy(array $criteria): ?object;
}
