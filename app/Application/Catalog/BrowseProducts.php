<?php

namespace App\Application\Catalog;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductRepository;

final readonly class BrowseProducts
{
    public function __construct(private ProductRepository $products) {}

    /** @return list<array<string, mixed>> */
    public function handle(string $query, string $category, string $sort): array
    {
        return collect($this->products->all())
            ->when($category !== 'All', fn ($items) => $items->where('category.value', $category))
            ->when($query !== '', fn ($items) => $items->filter(
                fn (Product $product) => str_contains(
                    strtolower($product->name.' '.$product->description.' '.$product->category->value),
                    strtolower(trim($query)),
                ),
            ))
            ->when($sort === 'price-asc', fn ($items) => $items->sortBy('price.cents'))
            ->when($sort === 'price-desc', fn ($items) => $items->sortByDesc('price.cents'))
            ->when($sort === 'newest', fn ($items) => $items->sortByDesc('new'))
            ->map(fn (Product $product) => $this->present($product))
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function present(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->value,
            'description' => $product->description,
            'price' => $product->price->formatted(),
            'image' => $product->image,
            'colors' => $product->colors,
            'featured' => $product->featured,
            'new' => $product->new,
        ];
    }
}
