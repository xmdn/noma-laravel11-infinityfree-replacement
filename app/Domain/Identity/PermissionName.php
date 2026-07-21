<?php

namespace App\Domain\Identity;

enum PermissionName: string
{
    case AccessAdmin = 'access-admin';
    case ManageShopSettings = 'manage-shop-settings';
    case ManageUsers = 'manage-users';
    case ManageCatalog = 'manage-catalog';
    case ManageOrders = 'manage-orders';
    case ManageCustomers = 'manage-customers';
    case ViewAuditLog = 'view-audit-log';
    case ManagePlatformShops = 'manage-platform-shops';
    case ModeratePlatformProducts = 'moderate-platform-products';
    case ViewPlatformOrders = 'view-platform-orders';
    case ViewSentEmails = 'view-sent-emails';
}
