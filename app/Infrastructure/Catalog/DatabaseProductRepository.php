<?php

namespace App\Infrastructure\Catalog;

use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Shared\Money;
use App\Models\CatalogProduct;

final class DatabaseProductRepository implements ProductRepository
{
    public function all(?string $shopId = null): array
    {
        if ($shopId === null) {
            return [];
        }

        return CatalogProduct::query()
            ->with('categories')
            ->where('shop_id', $shopId)
            ->where('status', 'active')
            ->where('is_blocked', false)
            ->whereNotNull('published_at')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (CatalogProduct $product): Product => $this->toDomain($product))
            ->all();
    }

    public function find(string $id): ?Product
    {
        $product = CatalogProduct::query()
            ->with('categories')
            ->whereKey($id)
            ->where('status', 'active')
            ->where('is_blocked', false)
            ->whereNotNull('published_at')
            ->first();

        return $product instanceof CatalogProduct ? $this->toDomain($product) : null;
    }

    public function findMany(array $ids): array
    {
        return CatalogProduct::query()
            ->with('categories')
            ->whereKey($ids)
            ->where('status', 'active')
            ->where('is_blocked', false)
            ->whereNotNull('published_at')
            ->get()
            ->mapWithKeys(fn (CatalogProduct $product): array => [$product->id => $this->toDomain($product)])
            ->all();
    }

    private function toDomain(CatalogProduct $product): Product
    {
        $category = $product->categories->first()?->name ?? 'All';

        return new Product(
            id: $product->id,
            name: $product->name,
            category: $category,
            description: $product->description,
            price: new Money((int) $product->price_minor, $product->currency),
            image: $this->safeImageUrl($product->primary_image_url),
            colors: $product->colors ?? [],
            featured: $product->is_featured,
            new: $product->is_new,
        );
    }

    private function safeImageUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (is_string($host) && str_ends_with($host, 'gstatic.com')) {
            return $this->placeholderImage();
        }

        return $url;
    }

    private function placeholderImage(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1460"><rect width="1200" height="1460" fill="#e4e1d7"/><circle cx="820" cy="430" r="260" fill="#d8d0c0"/><path d="M160 980h880v220H160z" fill="#b55536" opacity=".72"/><path d="M240 280h420v560H240z" fill="#f2f0e9" opacity=".82"/></svg>';

        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
    }
}
