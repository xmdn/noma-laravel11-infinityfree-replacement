<?php

namespace App\Http\Controllers\Admin;

use App\Application\Tenancy\UpdateShopSettings;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ShopSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.shop-settings', [
            'shop' => $request->user()->shop()->firstOrFail(),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request, UpdateShopSettings $updateShopSettings): RedirectResponse
    {
        $shop = $request->user()->shop()->firstOrFail();
        $request->merge(['slug' => $request->input('slug', $shop->slug)]);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:140',
                Rule::unique('shops', 'slug')->ignore($shop->id),
            ],
            'currency' => ['required', 'string', 'size:3', 'uppercase:ascii'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
        ]);
        $attributes['slug'] = Str::slug($attributes['slug']);
        $attributes['domain'] = $attributes['slug'].'.'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');
        $attributes['database'] = $shop->database ?: 'tenant_'.str_replace('-', '_', $attributes['slug']);

        $updateShopSettings->handle($shop, $attributes);

        return redirect()->route('admin.shop.settings.edit')->with('status', 'Shop settings updated.');
    }
}
