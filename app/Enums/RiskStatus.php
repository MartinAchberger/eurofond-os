<?php

namespace App\Enums;

enum RiskStatus: string
{
    case Otvorene = 'otvorene';
    case Mitigovane = 'mitigovane';
    case Uzavrete = 'uzavrete';
    case PrejaviloSa = 'prejavilo_sa';

    public function label(): string
    {
        return match ($this) {
            self::Otvorene => 'Otvorené',
            self::Mitigovane => 'Mitigované',
            self::Uzavrete => 'Uzavreté',
            self::PrejaviloSa => 'Prejavilo sa',
        };
    }
}
