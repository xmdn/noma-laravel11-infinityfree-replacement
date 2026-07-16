<?php

namespace App\Domain\Cart;

interface CartRepository
{
    /** @return array<string, int> */
    public function quantities(): array;

    public function put(string $productId, int $quantity): void;

    public function remove(string $productId): void;

    public function clear(): void;
}
