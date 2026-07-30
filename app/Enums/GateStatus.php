<?php

namespace App\Enums;

enum GateStatus: string
{
    case Cakajuca = 'cakajuca';
    case Prejdena = 'prejdena';
    case Zamietnuta = 'zamietnuta';

    public function label(): string
    {
        return match ($this) {
            self::Cakajuca => 'Čakajúca',
            self::Prejdena => 'Prejdená',
            self::Zamietnuta => 'Zamietnutá',
        };
    }
}
