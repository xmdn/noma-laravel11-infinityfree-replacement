<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogProduct extends Model
{
    use HasUlids;

    protected $table = 'products';

    /** @var list<string> */
    protected $fillable = [
        'shop_id',
        'slug',
        'name',
        'description',
        'price_minor',
        'currency',
        'primary_image_url',
        'colors',
        'is_featured',
        'is_new',
        'is_blocked',
        'blocked_at',
        'blocked_by',
        'blocked_reason',
        'status',
        'published_at',
    ];

    /** @return BelongsTo<Shop, $this> */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** @return BelongsToMany<CatalogCategory, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CatalogCategory::class, 'category_product', 'product_id', 'category_id');
    }

    /** @return HasMany<ProductMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'product_id')->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
