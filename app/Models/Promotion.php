<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasUlids;

    /** @var list<string> */
    protected $fillable = [
        'shop_id',
        'name',
        'code',
        'type',
        'value',
        'minimum_subtotal_minor',
        'maximum_discount_minor',
        'priority',
        'stacking_mode',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    /** @return BelongsTo<Shop, $this> */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
