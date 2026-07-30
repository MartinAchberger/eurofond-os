<?php

namespace App\Enums;

enum ProjectHealth: string
{
    case Dobre = 'dobre';
    case Stredne = 'stredne';
    case Riziko = 'riziko';

    public function label(): string
    {
        return match ($this) {
            self::Dobre => 'Dobré',
            self::Stredne => 'Stredne',
            self::Riziko => 'Riziko',
        };
    }
}
