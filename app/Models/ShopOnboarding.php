<?php

namespace App\Models;

use App\Domain\Tenancy\OnboardingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopOnboarding extends Model
{
    use HasUlids;

    /** @var list<string> */
    protected $fillable = ['user_id', 'shop_name', 'shop_slug', 'status', 'last_error', 'completed_at'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasOne<Shop, $this> */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'onboarding_id');
    }

    protected function casts(): array
    {
        return [
            'status' => OnboardingStatus::class,
            'completed_at' => 'immutable_datetime',
        ];
    }
}
