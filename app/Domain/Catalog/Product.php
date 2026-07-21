<?php

namespace App\Domain\Catalog;

use App\Domain\Shared\Money;

final readonly class Product
{
    /** @param list<string> $colors */
    public function __construct(
        public string $id,
        public string $name,
        public string $category,
        public string $description,
        public Money $price,
        public string $image,
        public array $colors,
        public bool $featured = false,
        public bool $new = false,
    ) {}
}
