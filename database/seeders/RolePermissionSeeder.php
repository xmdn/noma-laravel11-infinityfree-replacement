<?php

namespace Database\Seeders;

use App\Domain\Identity\PermissionName;
use App\Domain\Identity\SystemRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            PermissionName::AccessAdmin->value => 'Access the administration area',
            PermissionName::ManageShopSettings->value => 'Manage shop identity and operational settings',
            PermissionName::ManageUsers->value => 'View users and assign their roles',
            PermissionName::ManageCatalog->value => 'Create and update catalog content',
            PermissionName::ManageOrders->value => 'View and update orders',
            PermissionName::ManageCustomers->value => 'View and update customer records',
            PermissionName::ViewAuditLog->value => 'Review administrative audit activity',
            PermissionName::ManagePlatformShops->value => 'Suspend and restore shops across the platform',
            PermissionName::ModeratePlatformProducts->value => 'Block products across shops',
            PermissionName::ViewPlatformOrders->value => 'View orders across shops',
            PermissionName::ViewSentEmails->value => 'View outbound email records',
        ];

        $permissions = collect($descriptions)->mapWithKeys(function (string $description, string $name): array {
            $permission = Permission::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );

            return [$name => $permission->id];
        });

        $rolePermissions = [
            SystemRole::Owner->value => [
                PermissionName::AccessAdmin->value,
                PermissionName::ManageShopSettings->value,
                PermissionName::ManageUsers->value,
                PermissionName::ManageCatalog->value,
                PermissionName::ManageOrders->value,
                PermissionName::ManageCustomers->value,
                PermissionName::ViewAuditLog->value,
            ],
            SystemRole::Administrator->value => [
                PermissionName::AccessAdmin->value,
                PermissionName::ManageUsers->value,
                PermissionName::ManagePlatformShops->value,
                PermissionName::ModeratePlatformProducts->value,
                PermissionName::ViewPlatformOrders->value,
                PermissionName::ViewSentEmails->value,
            ],
            SystemRole::CatalogManager->value => [
                PermissionName::AccessAdmin->value,
                PermissionName::ManageCatalog->value,
            ],
            SystemRole::OrderManager->value => [
                PermissionName::AccessAdmin->value,
                PermissionName::ManageOrders->value,
                PermissionName::ManageCustomers->value,
            ],
            SystemRole::Support->value => [
                PermissionName::AccessAdmin->value,
                PermissionName::ManageCustomers->value,
            ],
            SystemRole::Customer->value => [],
        ];

        foreach (SystemRole::cases() as $systemRole) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $systemRole->value],
                ['name' => $systemRole->label(), 'is_system' => true],
            );

            $role->permissions()->sync(
                collect($rolePermissions[$systemRole->value])->map(fn (string $name): int => $permissions[$name]),
            );
        }
    }
}
