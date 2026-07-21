<?php

namespace App\Application\Tenancy;

use App\Domain\Tenancy\OnboardingStatus;
use App\Domain\Tenancy\ShopStatus;
use App\Models\Shop;
use App\Models\ShopOnboarding;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class ProvisionOwnerShop
{
    public function __construct(private ProvisionTenantDatabase $provisionTenantDatabase) {}

    public function handle(User $owner): ?Shop
    {
        if (! $owner->hasVerifiedEmail()) {
            return null;
        }

        $shop = DB::transaction(function () use ($owner): ?Shop {
            $onboarding = ShopOnboarding::query()
                ->where('user_id', $owner->getKey())
                ->lockForUpdate()
                ->first();

            if (! $onboarding) {
                return null;
            }

            $shop = Shop::query()->where('owner_id', $owner->getKey())->first();

            if (! $shop) {
                $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
                $shop = Shop::query()->create([
                    'owner_id' => $owner->getKey(),
                    'onboarding_id' => $onboarding->getKey(),
                    'name' => $onboarding->shop_name,
                    'slug' => $onboarding->shop_slug,
                    'domain' => $onboarding->shop_slug.'.'.$host,
                    'database' => 'tenant_'.Str::of($onboarding->shop_slug)->replace('-', '_')->toString(),
                    'status' => ShopStatus::Active,
                    'is_accessible' => true,
                ]);
            }

            $owner->forceFill(['shop_id' => $shop->getKey()])->save();
            $onboarding->forceFill([
                'status' => OnboardingStatus::Provisioned,
                'last_error' => null,
                'completed_at' => now(),
            ])->save();

            return $shop;
        });

        if ($shop) {
            $this->provisionTenantDatabase->handle($shop);
        }

        return $shop;
    }
}
