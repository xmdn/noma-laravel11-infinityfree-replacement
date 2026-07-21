<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop()->firstOrFail();

        return view('admin.categories.index', [
            'categories' => $shop->categories()->orderBy('sort_order')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new CatalogCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop()->firstOrFail();
        $attributes = $this->validateCategory($request);
        $attributes['shop_id'] = $shop->id;
        $attributes['slug'] = Str::slug($attributes['slug'] ?: $attributes['name']);
        $attributes['is_active'] = $request->boolean('is_active');

        CatalogCategory::query()->create($attributes);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, CatalogCategory $category): View
    {
        $this->authorizeShopCategory($request, $category);

        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, CatalogCategory $category): RedirectResponse
    {
        $this->authorizeShopCategory($request, $category);
        $attributes = $this->validateCategory($request, $category);
        $attributes['slug'] = Str::slug($attributes['slug'] ?: $attributes['name']);
        $attributes['is_active'] = $request->boolean('is_active');

        $category->update($attributes);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, CatalogCategory $category): RedirectResponse
    {
        $this->authorizeShopCategory($request, $category);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    /** @return array{name:string,slug:string,is_active?:bool,sort_order:int} */
    private function validateCategory(Request $request, ?CatalogCategory $category = null): array
    {
        $shopId = $request->user()->shop_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('categories', 'slug')->where('shop_id', $shopId)->ignore($category?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
    }

    private function authorizeShopCategory(Request $request, CatalogCategory $category): void
    {
        abort_unless($category->shop_id === $request->user()->shop_id, 404);
    }
}
