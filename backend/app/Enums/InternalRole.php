<?php

namespace App\Enums;

enum InternalRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case SUPPORT = 'support';
    case OPS = 'ops';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::SUPPORT => 'Support',
            self::OPS => 'Operations',
        };
    }
}
