<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\PermissionName;
use App\Domain\Identity\SystemRole;
use App\Http\Controllers\Controller;
use App\Models\CatalogProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class ProductController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->user()->hasRole(SystemRole::Administrator->value)) {
            return view('admin.products.index', [
                'products' => CatalogProduct::query()->with(['categories', 'shop'])->latest()->paginate(30),
            ]);
        }

        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        return view('admin.products.index', [
            'products' => $request->user()->shop()->firstOrFail()->products()->with('categories')->latest()->paginate(30),
        ]);
    }

    public function create(Request $request): View
    {
        abort_if($request->user()->hasRole(SystemRole::Administrator->value), 403);
        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        return view('admin.products.create', [
            'product' => new CatalogProduct(['currency' => $request->user()->shop->currency, 'status' => 'draft']),
            'categories' => $request->user()->shop->categories()->orderBy('name')->get(),
            'selectedCategories' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->hasRole(SystemRole::Administrator->value), 403);
        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        $shop = $request->user()->shop()->firstOrFail();
        $attributes = $this->validatedProduct($request);
        $attributes['shop_id'] = $shop->id;
        $attributes['slug'] = Str::slug($attributes['slug'] ?: $attributes['name']);
        $attributes['colors'] = $this->parseColors((string) $request->input('colors'));
        $attributes['is_featured'] = $request->boolean('is_featured');
        $attributes['is_new'] = $request->boolean('is_new');
        $attributes['published_at'] = $attributes['status'] === 'active' ? now() : null;

        $product = CatalogProduct::query()->create($attributes);
        $product->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Request $request, CatalogProduct $product): View
    {
        if ($request->user()->hasRole(SystemRole::Administrator->value)) {
            return view('admin.products.edit', [
                'product' => $product,
                'categories' => collect(),
                'selectedCategories' => $product->categories()->pluck('categories.id')->all(),
            ]);
        }

        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        $this->authorizeShopProduct($request, $product);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $request->user()->shop->categories()->orderBy('name')->get(),
            'selectedCategories' => $product->categories()->pluck('categories.id')->all(),
        ]);
    }

    public function update(Request $request, CatalogProduct $product): RedirectResponse
    {
        abort_if($request->user()->hasRole(SystemRole::Administrator->value), 403);
        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        $this->authorizeShopProduct($request, $product);
        $attributes = $this->validatedProduct($request, $product);
        $attributes['slug'] = Str::slug($attributes['slug'] ?: $attributes['name']);
        $attributes['colors'] = $this->parseColors((string) $request->input('colors'));
        $attributes['is_featured'] = $request->boolean('is_featured');
        $attributes['is_new'] = $request->boolean('is_new');
        $attributes['published_at'] = $attributes['status'] === 'active'
            ? ($product->published_at ?? now())
            : null;

        $product->update($attributes);
        $product->categories()->sync($request->input('category_ids', []));

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Request $request, CatalogProduct $product): RedirectResponse
    {
        abort_if($request->user()->hasRole(SystemRole::Administrator->value), 403);
        abort_unless($request->user()->hasPermission(PermissionName::ManageCatalog->value), 403);

        $this->authorizeShopProduct($request, $product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /** @return array<string, mixed> */
    private function validatedProduct(Request $request, ?CatalogProduct $product = null): array
    {
        $shopId = $request->user()->shop_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                Rule::unique('products', 'slug')->where('shop_id', $shopId)->ignore($product?->id),
            ],
            'description' => ['required', 'string', 'max:4000'],
            'price_minor' => ['required', 'integer', 'min:0', 'max:999999999'],
            'currency' => ['required', 'string', 'size:3', 'uppercase:ascii'],
            'primary_image_url' => ['required', 'url', 'max:2048'],
            'colors' => ['nullable', 'string', 'max:200'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'category_ids' => ['array'],
            'category_ids.*' => [
                'string',
                Rule::exists('categories', 'id')->where('shop_id', $shopId),
            ],
        ]);
    }

    /** @return list<string> */
    private function parseColors(string $colors): array
    {
        return collect(explode(',', $colors))
            ->map(fn (string $color): string => trim($color))
            ->filter(fn (string $color): bool => preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1)
            ->take(8)
            ->values()
            ->all();
    }

    private function authorizeShopProduct(Request $request, CatalogProduct $product): void
    {
        abort_unless($product->shop_id === $request->user()->shop_id, 404);
    }
}
