<?php

namespace App\Enums;

enum InboxSource: string
{
    case Email = 'email';
    case Poznamka = 'poznamka';
    case Subor = 'subor';
    case Telefonat = 'telefonat';
    case Ine = 'ine';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Poznamka => 'Poznámka',
            self::Subor => 'Súbor',
            self::Telefonat => 'Telefonát',
            self::Ine => 'Iné',
        };
    }
}
