<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ProductModerationController extends Controller
{
    public function update(Request $request, CatalogProduct $product): RedirectResponse
    {
        $attributes = $request->validate([
            'is_blocked' => ['nullable', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $blocked = $request->boolean('is_blocked');

        $product->forceFill([
            'is_blocked' => $blocked,
            'blocked_at' => $blocked ? now() : null,
            'blocked_by' => $blocked ? $request->user()->id : null,
            'blocked_reason' => $blocked ? ($attributes['blocked_reason'] ?? 'Blocked by platform administrator.') : null,
        ])->save();

        return redirect()->route('admin.products.index')->with('status', 'Product moderation updated.');
    }
}
