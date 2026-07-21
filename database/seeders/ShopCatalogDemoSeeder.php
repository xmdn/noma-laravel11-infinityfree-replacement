<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\CatalogProduct;
use App\Models\ProductMedia;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopCatalogDemoSeeder extends Seeder
{
    /** @var list<string> */
    private array $categoryPool = [
        'Living Room', 'Kitchen', 'Lighting', 'Storage', 'Office', 'Bedroom', 'Bathroom', 'Outdoor',
        'Bags', 'Footwear', 'Outerwear', 'Knitwear', 'Accessories', 'Ceramics', 'Glassware', 'Stationery',
        'Wellness', 'Travel', 'Kids', 'Pets', 'Audio', 'Decor', 'Textiles', 'Tabletop', 'Hardware',
        'Garden', 'Books', 'Gifts', 'Cleaning', 'Pantry', 'Coffee', 'Fragrance', 'Fitness', 'Jewelry',
        'Skincare', 'Tools', 'Art Prints', 'Candles', 'Smart Home', 'Seasonal',
    ];

    /** @var list<string> */
    private array $imagePool = [
        'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=85',
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=85',
    ];

    /** @var list<string> */
    private array $colorPool = ['#171814', '#d2cec0', '#a14228', '#314033', '#e0ad46', '#4a5568', '#efe6d1', '#243b53'];

    public function run(): void
    {
        Shop::query()->orderBy('created_at')->each(function (Shop $shop): void {
            DB::transaction(function () use ($shop): void {
                $targetCategories = random_int(20, 40);
                $targetProducts = random_int(50, 100);

                $this->fillCategories($shop, $targetCategories);
                $this->fillProducts($shop, $targetProducts);
            });
        });
    }

    private function fillCategories(Shop $shop, int $targetCount): void
    {
        $existingCount = CatalogCategory::query()->where('shop_id', $shop->id)->count();
        $needed = max(0, $targetCount - $existingCount);

        collect($this->categoryPool)
            ->shuffle()
            ->take($needed)
            ->values()
            ->each(function (string $name, int $index) use ($shop, $existingCount): void {
                CatalogCategory::query()->firstOrCreate(
                    ['shop_id' => $shop->id, 'slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'is_active' => true,
                        'sort_order' => $existingCount + $index + 1,
                    ],
                );
            });
    }

    private function fillProducts(Shop $shop, int $targetCount): void
    {
        $categories = CatalogCategory::query()->where('shop_id', $shop->id)->where('is_active', true)->get();
        $existingCount = CatalogProduct::query()->where('shop_id', $shop->id)->count();
        $needed = max(0, $targetCount - $existingCount);

        for ($i = 1; $i <= $needed; $i++) {
            $category = $categories->random();
            $name = fake()->unique()->words(random_int(2, 4), true);
            $slug = Str::slug($name.'-'.$shop->slug.'-'.$existingCount.'-'.$i);
            $imageUrl = $this->imagePool[array_rand($this->imagePool)];

            /** @var CatalogProduct $product */
            $product = CatalogProduct::query()->create([
                'shop_id' => $shop->id,
                'slug' => $slug,
                'name' => Str::headline($name),
                'description' => fake()->paragraphs(random_int(2, 3), true),
                'price_minor' => random_int(1800, 125000),
                'currency' => $shop->currency,
                'primary_image_url' => $imageUrl,
                'colors' => collect($this->colorPool)->shuffle()->take(random_int(2, 4))->values()->all(),
                'is_featured' => random_int(1, 5) === 1,
                'is_new' => random_int(1, 4) === 1,
                'status' => 'active',
                'published_at' => now()->subDays(random_int(0, 120)),
            ]);

            $product->categories()->sync($categories->random(random_int(1, min(3, $categories->count())))->pluck('id')->all());

            ProductMedia::query()->create([
                'product_id' => $product->id,
                'disk' => 'remote',
                'path' => $imageUrl.'&product='.$product->id,
                'source_url' => $imageUrl,
                'mime_type' => 'image/jpeg',
                'size_bytes' => random_int(120000, 600000),
                'checksum_sha256' => hash('sha256', $imageUrl.$product->id),
                'position' => 0,
                'alt_text' => $product->name,
            ]);
        }
    }
}
