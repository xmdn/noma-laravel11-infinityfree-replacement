<?php

namespace App\Listeners;

use App\Application\Tenancy\ProvisionOwnerShop;
use App\Domain\Tenancy\OnboardingStatus;
use App\Models\ShopOnboarding;
use Illuminate\Auth\Events\Verified;
use Throwable;

final class ProvisionOwnerShopAfterVerification
{
    public function __construct(private readonly ProvisionOwnerShop $provisionOwnerShop) {}

    public function handle(Verified $event): void
    {
        try {
            $this->provisionOwnerShop->handle($event->user);
        } catch (Throwable $exception) {
            ShopOnboarding::query()->where('user_id', $event->user->getKey())->update([
                'status' => OnboardingStatus::Failed->value,
                'last_error' => $exception->getMessage(),
            ]);

            report($exception);
        }
    }
}
