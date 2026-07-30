<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Nizka = 'nizka';
    case Stredna = 'stredna';
    case Vysoka = 'vysoka';
    case Blokator = 'blokator';

    public function label(): string
    {
        return match ($this) {
            self::Nizka => 'Nízka',
            self::Stredna => 'Stredná',
            self::Vysoka => 'Vysoká',
            self::Blokator => 'Blokátor',
        };
    }
}
