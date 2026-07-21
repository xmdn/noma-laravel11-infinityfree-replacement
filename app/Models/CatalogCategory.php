<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CatalogCategory extends Model
{
    use HasUlids;

    protected $table = 'categories';

    /** @var list<string> */
    protected $fillable = ['shop_id', 'name', 'slug', 'is_active', 'sort_order'];

    /** @return BelongsTo<Shop, $this> */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** @return BelongsToMany<CatalogProduct, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(CatalogProduct::class, 'category_product', 'category_id', 'product_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
