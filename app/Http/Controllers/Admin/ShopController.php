<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShopController extends Controller
{
    public function index(): View
    {
        return view('admin.shops.index', [
            'shops' => Shop::query()->with('owner')->withCount('products')->latest()->paginate(40),
        ]);
    }

    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $attributes = $request->validate([
            'is_accessible' => ['nullable', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $accessible = $request->boolean('is_accessible');

        $shop->forceFill([
            'is_accessible' => $accessible,
            'blocked_at' => $accessible ? null : now(),
            'blocked_reason' => $accessible ? null : ($attributes['blocked_reason'] ?? 'Suspended by platform administrator.'),
        ])->save();

        return redirect()->route('admin.shops.index')->with('status', 'Shop access updated.');
    }
}
