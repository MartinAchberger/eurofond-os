<?php

namespace App\Enums;

enum RiskLevel: string
{
    case Nizky = 'nizky';
    case Stredny = 'stredny';
    case Vysoky = 'vysoky';

    public function label(): string
    {
        return match ($this) {
            self::Nizky => 'Nízky',
            self::Stredny => 'Stredný',
            self::Vysoky => 'Vysoký',
        };
    }
}
