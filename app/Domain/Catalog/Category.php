<?php

namespace App\Domain\Catalog;

enum Category: string
{
    case All = 'All';
    case Living = 'Living';
    case Carry = 'Carry';
    case Wear = 'Wear';
    case Objects = 'Objects';
}
