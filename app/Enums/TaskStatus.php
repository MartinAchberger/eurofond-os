<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Otvorena = 'otvorena';
    case Caka = 'caka';
    case Hotova = 'hotova';

    public function label(): string
    {
        return match ($this) {
            self::Otvorena => 'Otvorená',
            self::Caka => 'Čaká',
            self::Hotova => 'Hotová',
        };
    }
}
