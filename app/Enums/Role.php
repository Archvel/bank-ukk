<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Teller = 'teller';
    case Nasabah = 'nasabah';

    /**
     * Get human readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator & Supervisor',
            self::Teller => 'Piket Teller',
            self::Nasabah => 'Nasabah / Siswa',
        };
    }
}
