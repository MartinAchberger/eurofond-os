<?php

namespace App\Enums;

enum DocumentVersionStatus: string
{
    case Aktualna = 'aktualna';
    case Historicka = 'historicka';
    case Nepotvrdena = 'nepotvrdena';
    case Nahradena = 'nahradena';

    public function label(): string
    {
        return match ($this) {
            self::Aktualna => 'Aktuálna',
            self::Historicka => 'Historická',
            self::Nepotvrdena => 'Nepotvrdená',
            self::Nahradena => 'Nahradená',
        };
    }
}
