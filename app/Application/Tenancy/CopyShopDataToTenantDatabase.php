<?php

namespace App\Application\Tenancy;

use App\Models\Shop;
use Illuminate\Support\Facades\DB;

final class CopyShopDataToTenantDatabase
{
    public function handle(Shop $shop): void
    {
        $tenant = DB::connection('tenant');

        $productIds = DB::table('products')->where('shop_id', $shop->id)->pluck('id');
        $categoryIds = DB::table('categories')->where('shop_id', $shop->id)->pluck('id');

        $tenant->table('product_media')->whereIn('product_id', $productIds)->delete();
        $tenant->table('category_product')->whereIn('product_id', $productIds)->delete();
        $tenant->table('product_promotion')->whereIn('product_id', $productIds)->delete();
        $tenant->table('category_promotion')->whereIn('category_id', $categoryIds)->delete();
        $tenant->table('products')->where('shop_id', $shop->id)->delete();
        $tenant->table('categories')->where('shop_id', $shop->id)->delete();
        $tenant->table('promotions')->where('shop_id', $shop->id)->delete();
        $tenant->table('users')->where('shop_id', $shop->id)->update(['shop_id' => null]);
        $tenant->table('shops')->where('id', $shop->id)->delete();

        foreach (DB::table('roles')->get() as $role) {
            $tenant->table('roles')->updateOrInsert(['id' => $role->id], (array) $role);
        }

        foreach (DB::table('permissions')->get() as $permission) {
            $tenant->table('permissions')->updateOrInsert(['id' => $permission->id], (array) $permission);
        }

        $tenant->table('permission_role')->delete();
        foreach (DB::table('permission_role')->get() as $permissionRole) {
            $tenant->table('permission_role')->insert((array) $permissionRole);
        }

        $shopUsers = DB::table('users')
            ->where('shop_id', $shop->id)
            ->orWhere('id', $shop->owner_id)
            ->get();

        foreach ($shopUsers as $user) {
            $userData = (array) $user;
            $userData['shop_id'] = null;
            $tenant->table('users')->updateOrInsert(['id' => $user->id], $userData);
        }

        $tenant->table('shop_onboardings')->where('user_id', $shop->owner_id)->delete();
        foreach (DB::table('shop_onboardings')->where('user_id', $shop->owner_id)->get() as $onboarding) {
            $tenant->table('shop_onboardings')->insert((array) $onboarding);
        }

        $tenant->table('shops')->insert((array) DB::table('shops')->where('id', $shop->id)->first());

        foreach ($shopUsers as $user) {
            $tenant->table('users')->where('id', $user->id)->update(['shop_id' => $shop->id]);
        }

        $tenant->table('role_user')->whereIn('user_id', $shopUsers->pluck('id'))->delete();
        foreach (DB::table('role_user')->whereIn('user_id', $shopUsers->pluck('id'))->get() as $roleUser) {
            $tenant->table('role_user')->insert((array) $roleUser);
        }

        foreach (DB::table('categories')->where('shop_id', $shop->id)->get() as $category) {
            $tenant->table('categories')->insert((array) $category);
        }

        foreach (DB::table('products')->where('shop_id', $shop->id)->get() as $product) {
            $tenant->table('products')->insert((array) $product);
        }

        foreach (DB::table('category_product')->whereIn('product_id', $productIds)->get() as $pivot) {
            $tenant->table('category_product')->insert((array) $pivot);
        }

        foreach (DB::table('product_media')->whereIn('product_id', $productIds)->get() as $media) {
            $tenant->table('product_media')->insert((array) $media);
        }

        foreach (DB::table('promotions')->where('shop_id', $shop->id)->get() as $promotion) {
            $tenant->table('promotions')->insert((array) $promotion);
        }
    }
}
