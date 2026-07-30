<?php

namespace App\Enums;

enum ProjectPhase: int
{
    case Screening = 1;
    case RozhodnutieOPriprave = 2;
    case ZberPodkladov = 3;
    case TechnickaFinancnaKontrola = 4;
    case PripravaZiadosti = 5;
    case Podanie = 6;
    case SchvalenieAZmluva = 7;
    case VerejneObstaravanie = 8;
    case Realizacia = 9;
    case PlatbyAMonitorovanie = 10;
    case Ukoncenie = 11;
    case Udrzatelnost = 12;

    public function label(): string
    {
        return match ($this) {
            self::Screening => 'Prvotný screening',
            self::RozhodnutieOPriprave => 'Rozhodnutie o príprave',
            self::ZberPodkladov => 'Zber podkladov',
            self::TechnickaFinancnaKontrola => 'Technická a finančná kontrola',
            self::PripravaZiadosti => 'Príprava žiadosti',
            self::Podanie => 'Podanie',
            self::SchvalenieAZmluva => 'Schválenie a zmluva',
            self::VerejneObstaravanie => 'Verejné obstarávanie',
            self::Realizacia => 'Realizácia',
            self::PlatbyAMonitorovanie => 'Platby a monitorovanie',
            self::Ukoncenie => 'Ukončenie',
            self::Udrzatelnost => 'Udržateľnosť',
        };
    }
}
