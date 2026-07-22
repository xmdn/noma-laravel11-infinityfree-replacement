<?php

namespace App\Services;

use App\Domain\Identity\PermissionName;
use App\Domain\Identity\SystemRole;
use App\Models\CatalogCategory;
use App\Models\CatalogProduct;
use App\Models\Shop;
use App\Models\User;

final class AdminDashboardModuleFactory
{
    /**
     * @return list<array{label: string, value: int|string, link?: string|null, action?: string}>
     */
    public function makeForUser(User $user): array
    {
        if ($user->hasRole(SystemRole::Administrator->value)) {
            return [
                [
                    'label' => 'Users',
                    'value' => User::query()->count(),
                    'link' => route('admin.users.index'),
                    'action' => 'Manage users',
                ],
                [
                    'label' => 'Shops',
                    'value' => Shop::query()->count(),
                    'link' => route('admin.shops.index'),
                    'action' => 'Manage shops',
                ],
                [
                    'label' => 'Moderation',
                    'value' => CatalogProduct::query()->where('is_blocked', true)->count(),
                    'link' => route('admin.products.index'),
                    'action' => 'Review products',
                ],
            ];
        }

        $modules = [];

        if ($user->hasPermission(PermissionName::ManageUsers->value)) {
            $modules[] = [
                'label' => 'Shop users',
                'value' => User::query()->where('shop_id', $user->shop_id)->count(),
                'link' => route('admin.users.index'),
                'action' => 'Manage shop users',
            ];
        }

        if ($user->hasPermission(PermissionName::ManageCatalog->value)) {
            $modules[] = [
                'label' => 'Catalog',
                'value' => 'Enabled',
                'link' => route('admin.products.index'),
                'action' => 'Open catalog',
            ];
            $modules[] = [
                'label' => 'Products',
                'value' => CatalogProduct::query()->where('shop_id', $user->shop_id)->count(),
                'link' => route('admin.products.index'),
                'action' => 'Manage products',
            ];
            $modules[] = [
                'label' => 'Categories',
                'value' => CatalogCategory::query()->where('shop_id', $user->shop_id)->count(),
                'link' => route('admin.categories.index'),
                'action' => 'Manage categories',
            ];
        }

        foreach ([
            PermissionName::ManageOrders->value => [
                'label' => 'Discounts',
                'link' => route('admin.promotions.index'),
                'action' => 'Manage discounts',
            ],
            PermissionName::ManageCustomers->value => ['label' => 'Customers'],
            PermissionName::ViewAuditLog->value => ['label' => 'Audit log'],
        ] as $permission => $module) {
            if ($user->hasPermission($permission)) {
                $modules[] = ['value' => 'Enabled', ...$module];
            }
        }

        if ($user->hasPermission(PermissionName::ManageShopSettings->value)) {
            $modules[] = [
                'label' => 'Public shop',
                'value' => $user->shop?->name ?? 'Missing',
                'link' => $user->shop?->publicUrl(),
                'action' => 'Open storefront',
            ];
            $modules[] = [
                'label' => 'Shop settings',
                'value' => 'Enabled',
                'link' => $user->shop ? route('admin.shop.settings.edit') : null,
                'action' => 'Configure shop',
            ];
        }

        return $modules;
    }
}
