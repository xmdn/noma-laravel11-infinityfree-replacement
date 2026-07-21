<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Shop;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.promotions.index', [
            'promotions' => $request->user()->shop()->firstOrFail()->promotions()->latest()->paginate(30),
        ]);
    }

    public function create(Request $request): View
    {
        $shop = $request->user()->shop()->firstOrFail();

        return view('admin.promotions.create', [
            'promotion' => new Promotion([
                'type' => 'percentage',
                'value' => 10,
                'starts_at' => now($shop->timezone ?: config('app.timezone')),
                'stacking_mode' => 'best_price',
                'is_active' => true,
            ]),
            'shop' => $shop,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop()->firstOrFail();
        $attributes = $this->validated($request);
        $attributes = $this->normalizePromotionWindow($attributes, $shop);
        $attributes['shop_id'] = $shop->id;
        $attributes['is_active'] = $request->boolean('is_active');

        Promotion::query()->create($attributes);

        return redirect()->route('admin.promotions.index')->with('status', 'Discount created.');
    }

    public function edit(Request $request, Promotion $promotion): View
    {
        $this->authorizeShopPromotion($request, $promotion);

        return view('admin.promotions.edit', [
            'promotion' => $promotion,
            'shop' => $request->user()->shop()->firstOrFail(),
        ]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $this->authorizeShopPromotion($request, $promotion);
        $shop = $request->user()->shop()->firstOrFail();
        $attributes = $this->validated($request, $promotion);
        $attributes = $this->normalizePromotionWindow($attributes, $shop);
        $attributes['is_active'] = $request->boolean('is_active');
        $promotion->update($attributes);

        return redirect()->route('admin.promotions.index')->with('status', 'Discount updated.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Promotion $promotion = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:80', Rule::unique('promotions', 'code')->where('shop_id', $request->user()->shop_id)->ignore($promotion?->id)],
            'type' => ['required', Rule::in(['percentage', 'fixed_amount'])],
            'value' => ['required', 'integer', 'min:1', 'max:999999'],
            'minimum_subtotal_minor' => ['required', 'integer', 'min:0'],
            'maximum_discount_minor' => ['nullable', 'integer', 'min:0'],
            'priority' => ['required', 'integer', 'min:0', 'max:10000'],
            'stacking_mode' => ['required', Rule::in(['exclusive', 'stackable', 'best_price'])],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);
    }

    private function authorizeShopPromotion(Request $request, Promotion $promotion): void
    {
        abort_unless($promotion->shop_id === $request->user()->shop_id, 404);
    }

    /** @param array<string, mixed> $attributes */
    private function normalizePromotionWindow(array $attributes, Shop $shop): array
    {
        $timezone = $shop->timezone ?: config('app.timezone');

        $attributes['starts_at'] = CarbonImmutable::parse((string) $attributes['starts_at'], $timezone)->utc();
        $attributes['ends_at'] = filled($attributes['ends_at'] ?? null)
            ? CarbonImmutable::parse((string) $attributes['ends_at'], $timezone)->utc()
            : null;

        return $attributes;
    }
}
