<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cart extends Model
{
    use HasUlids;

    protected $fillable = [
        'session_id',
        'user_id',
        'status',
        'currency',
        'discount_snapshot',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_snapshot' => 'array',
            'expires_at' => 'immutable_datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
