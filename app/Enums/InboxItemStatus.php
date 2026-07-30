<?php

namespace App\Enums;

enum InboxItemStatus: string
{
    case Nove = 'nove';
    case Klasifikovane = 'klasifikovane';
    case Schvalene = 'schvalene';
    case Zamietnute = 'zamietnute';

    public function label(): string
    {
        return match ($this) {
            self::Nove => 'Nové',
            self::Klasifikovane => 'Klasifikované',
            self::Schvalene => 'Schválené',
            self::Zamietnute => 'Zamietnuté',
        };
    }
}
