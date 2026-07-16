<?php

namespace App\Domain\Catalog;

interface ProductRepository
{
    /** @return list<Product> */
    public function all(): array;

    public function find(string $id): ?Product;

    /**
     * @param  list<string>  $ids
     * @return array<string, Product>
     */
    public function findMany(array $ids): array;
}
