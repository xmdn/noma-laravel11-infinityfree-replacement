<?php

namespace App\Models;

use App\Domain\Tenancy\ShopStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Concerns\ImplementsTenant;

class Shop extends Model implements IsTenant
{
    use HasUlids;
    use ImplementsTenant;
    use UsesMultitenancyConfig;

    /** @var list<string> */
    protected $fillable = [
        'owner_id',
        'onboarding_id',
        'name',
        'slug',
        'domain',
        'database',
        'status',
        'is_accessible',
        'blocked_at',
        'blocked_reason',
        'currency',
        'timezone',
    ];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<ShopOnboarding, $this> */
    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(ShopOnboarding::class, 'onboarding_id');
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<CatalogCategory, $this> */
    public function categories(): HasMany
    {
        return $this->hasMany(CatalogCategory::class);
    }

    /** @return HasMany<CatalogProduct, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(CatalogProduct::class);
    }

    /** @return HasMany<Promotion, $this> */
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function publicUrl(?string $path = null): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $port = parse_url($baseUrl, PHP_URL_PORT);
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'http';
        $host = $this->domain ?: $this->slug.'.'.(parse_url($baseUrl, PHP_URL_HOST) ?: 'localhost');
        $authority = $host.($port ? ':'.$port : '');

        return $scheme.'://'.$authority.'/'.ltrim((string) $path, '/');
    }

    protected function casts(): array
    {
        return [
            'status' => ShopStatus::class,
            'is_accessible' => 'boolean',
            'blocked_at' => 'datetime',
        ];
    }
}
