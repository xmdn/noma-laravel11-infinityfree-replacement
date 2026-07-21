<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_slug',
        'product_name',
        'quantity',
        'unit_price_minor',
        'discount_minor',
        'total_minor',
        'price_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_minor' => 'integer',
            'discount_minor' => 'integer',
            'total_minor' => 'integer',
            'price_snapshot' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}