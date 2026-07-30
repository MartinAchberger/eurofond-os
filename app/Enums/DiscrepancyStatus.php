<?php

namespace App\Enums;

enum DiscrepancyStatus: string
{
    case Otvoreny = 'otvoreny';
    case Vyrieseny = 'vyrieseny';
    case Zamietnuty = 'zamietnuty';

    public function label(): string
    {
        return match ($this) {
            self::Otvoreny => 'Otvorený',
            self::Vyrieseny => 'Vyriešený',
            self::Zamietnuty => 'Zamietnutý',
        };
    }
}
