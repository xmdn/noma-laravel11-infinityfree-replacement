<?php

namespace App\Infrastructure\Checkout;

use App\Domain\Checkout\Promotion as PromotionContract;
use App\Domain\Checkout\ThresholdPromotion;
use App\Domain\Shared\Money;
use App\Models\Promotion as PromotionModel;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Throwable;

final class DatabasePromotion implements PromotionContract
{
    /** @var array{label: string, discount: Money}|null */
    private ?array $lastResolved = null;

    public function discount(Money $subtotal): Money
    {
        $resolved = $this->resolveBestPromotion($subtotal);

        if ($resolved === null) {
            $this->lastResolved = null;

            return (new ThresholdPromotion(30000, 10))->discount($subtotal);
        }

        $this->lastResolved = $resolved;

        return $resolved['discount'];
    }

    public function label(): string
    {
        if ($this->lastResolved !== null) {
            return $this->lastResolved['label'];
        }

        $shop = Shop::current();

        if ($shop instanceof Shop) {
            $promotion = $this->resolveCurrentPromotion($shop);

            if ($promotion instanceof PromotionModel) {
                return $this->formatLabel($promotion);
            }
        }

        return (new ThresholdPromotion(30000, 10))->label();
    }

    /** @return array{label: string, minimum_subtotal: Money, starts_at: ?string, ends_at: ?string}|null */
    public function currentOffer(): ?array
    {
        $shop = Shop::current();

        if (! $shop instanceof Shop) {
            return null;
        }

        return $this->currentOfferForShop($shop);
    }

    /** @return array{label: string, minimum_subtotal: Money, starts_at: ?string, ends_at: ?string}|null */
    public function currentOfferForShop(Shop $shop): ?array
    {
        $promotion = $this->resolveCurrentPromotion($shop);

        if (! $promotion instanceof PromotionModel) {
            return null;
        }

        return [
            'label' => $this->formatLabel($promotion),
            'minimum_subtotal' => new Money((int) $promotion->minimum_subtotal_minor, $shop->currency ?: 'USD'),
            'starts_at' => $promotion->starts_at?->toIso8601String(),
            'ends_at' => $promotion->ends_at?->toIso8601String(),
        ];
    }

    /** @return array{label: string, discount: Money}|null */
    private function resolveBestPromotion(Money $subtotal): ?array
    {
        $shop = Shop::current();

        if (! $shop instanceof Shop) {
            return null;
        }

        $candidates = $this->eligiblePromotions($shop, $subtotal);

        usort($candidates, static function (array $left, array $right): int {
            return $right['discount']->cents <=> $left['discount']->cents
                ?: (int) $right['promotion']->priority <=> (int) $left['promotion']->priority
                ?: ($right['promotion']->starts_at?->getTimestamp() ?? 0) <=> ($left['promotion']->starts_at?->getTimestamp() ?? 0);
        });

        return $candidates[0] ?? null;
    }

    /** @return list<array{promotion: PromotionModel, discount: Money, label: string}> */
    private function eligiblePromotions(Shop $shop, Money $subtotal): array
    {
        $now = now($shop->timezone ?: config('app.timezone'));

        return $this->promotionRecords($shop)
            ->filter(static function (PromotionModel $promotion) use ($now): bool {
                return $promotion->is_active
                    && $promotion->starts_at !== null
                    && $promotion->starts_at->lessThanOrEqualTo($now)
                    && ($promotion->ends_at === null || $promotion->ends_at->greaterThan($now));
            })
            ->map(function (PromotionModel $promotion) use ($subtotal, $shop): ?array {
                if ($subtotal->cents < (int) $promotion->minimum_subtotal_minor) {
                    return null;
                }

                $discountMinor = match ($promotion->type) {
                    'percentage' => (int) round($subtotal->cents * ((int) $promotion->value) / 100),
                    'fixed_amount' => (int) $promotion->value,
                    default => 0,
                };

                if ($promotion->maximum_discount_minor !== null) {
                    $discountMinor = min($discountMinor, (int) $promotion->maximum_discount_minor);
                }

                $discountMinor = min($discountMinor, $subtotal->cents);

                if ($discountMinor <= 0) {
                    return null;
                }

                return [
                    'promotion' => $promotion,
                    'discount' => new Money($discountMinor, $shop->currency ?: 'USD'),
                    'label' => $this->formatLabel($promotion),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveCurrentPromotion(Shop $shop): ?PromotionModel
    {
        $now = now($shop->timezone ?: config('app.timezone'));

        return $this->promotionRecords($shop)
            ->filter(static function (PromotionModel $promotion) use ($now): bool {
                return $promotion->is_active
                    && $promotion->starts_at !== null
                    && $promotion->starts_at->lessThanOrEqualTo($now)
                    && ($promotion->ends_at === null || $promotion->ends_at->greaterThan($now));
            })
            ->sortByDesc('priority')
            ->sortByDesc('starts_at')
            ->first();
    }

    /** @return Collection<int, PromotionModel> */
    private function promotionRecords(Shop $shop): Collection
    {
        $records = collect();

        foreach ([
            config('multitenancy.landlord_database_connection_name'),
            config('multitenancy.tenant_database_connection_name'),
        ] as $connection) {
            if (! is_string($connection) || $connection === '') {
                continue;
            }

            try {
                $records = $records->merge(
                    PromotionModel::on($connection)
                        ->where('shop_id', $shop->id)
                        ->get(),
                );
            } catch (Throwable) {
                continue;
            }
        }

        return $records
            ->filter()
            ->unique('id')
            ->values();
    }

    private function formatLabel(PromotionModel $promotion): string
    {
        $detail = match ($promotion->type) {
            'percentage' => $promotion->value.'% off',
            'fixed_amount' => (new Money((int) $promotion->value))->formatted().' off',
            default => 'automatic discount',
        };

        return trim($promotion->name.' · '.$detail);
    }
}
