<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use App\Contracts\HasDashboardView;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use App\Domain\Identity\SystemRole;

class User extends Authenticatable implements MustVerifyEmailContract,  HasDashboardView
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmail, Notifiable;

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'phone', 'password'];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    public function getDashboardViewPrefix(): string
    {
        $roles = [
            SystemRole::Owner->value,
            SystemRole::Administrator->value,
            SystemRole::OrderManager->value,
            SystemRole::CatalogManager->value,
            SystemRole::Support->value,
            SystemRole::Customer->value,
        ];

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return 'default';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** @return BelongsTo<Shop, $this> */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /** @return HasOne<Shop, $this> */
    public function ownedShop(): HasOne
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    /** @return HasOne<ShopOnboarding, $this> */
    public function shopOnboarding(): HasOne
    {
        return $this->hasOne(ShopOnboarding::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('slug', $role);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->contains(
            fn (Role $role): bool => $role->permissions->contains('name', $permission),
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
