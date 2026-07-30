<?php

namespace App\Enums;

enum AnswerBindingness: string
{
    case Zavazne = 'zavazne';
    case Pracovne = 'pracovne';
    case Neformalne = 'neformalne';

    public function label(): string
    {
        return match ($this) {
            self::Zavazne => 'Záväzné',
            self::Pracovne => 'Pracovné',
            self::Neformalne => 'Neformálne',
        };
    }
}
