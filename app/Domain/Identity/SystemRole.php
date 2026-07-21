<?php

namespace App\Domain\Identity;

enum SystemRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case CatalogManager = 'catalog-manager';
    case OrderManager = 'order-manager';
    case Support = 'support';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Business owner',
            self::Administrator => 'Administrator',
            self::CatalogManager => 'Catalog manager',
            self::OrderManager => 'Order manager',
            self::Support => 'Customer support',
            self::Customer => 'Customer',
        };
    }
}
