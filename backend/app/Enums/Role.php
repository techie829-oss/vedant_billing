<?php

namespace App\Enums;

enum Role: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case ACCOUNTANT = 'accountant';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Admin',
            self::STAFF => 'Staff',
            self::ACCOUNTANT => 'Accountant',
        };
    }
}
