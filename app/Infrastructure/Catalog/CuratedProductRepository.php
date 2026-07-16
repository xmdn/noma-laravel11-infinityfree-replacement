<?php

namespace App\Infrastructure\Catalog;

use App\Domain\Catalog\Category;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Shared\Money;

final class CuratedProductRepository implements ProductRepository
{
    public function all(): array
    {
        return [
            new Product('arc-lamp', 'Arc Table Lamp', Category::Living, 'A sculptural pool of warm light in powder-coated steel.', new Money(18900), 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1200&q=85', ['#171713', '#d8d0c0'], true, true),
            new Product('field-tote', 'Field Tote 02', Category::Carry, 'Structured everyday carry in water-resistant recycled canvas.', new Money(12800), 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1200&q=85', ['#2f3527', '#c2aa82'], true),
            new Product('form-chair', 'Form Lounge Chair', Category::Living, 'Low-profile comfort with an ash frame and woven wool seat.', new Money(64000), 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1200&q=85', ['#b6865b', '#393b37'], true),
            new Product('studio-knit', 'Studio Merino Knit', Category::Wear, 'Relaxed heavyweight merino with a clean saddle shoulder.', new Money(16400), 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=1200&q=85', ['#d8d0c0', '#252721'], false, true),
            new Product('stack-trays', 'Stack Trays', Category::Objects, 'Modular aluminum trays for the desk, hallway, or bedside.', new Money(7600), 'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=1200&q=85', ['#d45f3f', '#d0c8b8']),
            new Product('everyday-cap', 'Everyday Cap', Category::Wear, 'Six-panel organic twill with a soft, unstructured crown.', new Money(5800), 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&w=1200&q=85', ['#1f2420', '#e2d9c9']),
            new Product('column-vase', 'Column Vase', Category::Objects, 'Hand-finished stoneware designed for a single considered stem.', new Money(9200), 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=1200&q=85', ['#c9b89a', '#6e7465'], false, true),
            new Product('day-pack', 'Day Pack 18L', Category::Carry, 'A compact technical pack with a padded device sleeve.', new Money(14800), 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=1200&q=85', ['#20231f', '#9d8d70']),
        ];
    }

    public function find(string $id): ?Product
    {
        return collect($this->all())->first(fn (Product $product) => $product->id === $id);
    }

    public function findMany(array $ids): array
    {
        $requested = array_flip($ids);

        return collect($this->all())
            ->filter(fn (Product $product) => isset($requested[$product->id]))
            ->mapWithKeys(fn (Product $product) => [$product->id => $product])
            ->all();
    }
}
