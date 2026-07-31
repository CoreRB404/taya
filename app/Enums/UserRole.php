<?php

namespace App\Enums;

enum UserRole: string
{
    case STAFF = 'staff';
    case ADMIN = 'admin';
    case LAWYER = 'lawyer';
    case AUDITOR = 'auditor';
    case AUTHORIZED_USER = 'authorized_user';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return list<string> */
    public static function assignableValues(): array
    {
        return [self::ADMIN->value, self::STAFF->value, self::LAWYER->value, self::AUDITOR->value];
    }

    /** @return list<self> */
    public static function assignable(): array
    {
        return [self::STAFF, self::LAWYER, self::AUDITOR, self::ADMIN];
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'System Admin',
            self::STAFF => 'Facility Staff',
            self::LAWYER => 'Lawyer',
            self::AUDITOR => 'Auditor',
            self::AUTHORIZED_USER => 'Authorized User (Legacy)',
        };
    }
}
