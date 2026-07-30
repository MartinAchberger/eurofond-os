<?php

namespace App\Enums;

enum AiSuggestionStatus: string
{
    case Navrhnute = 'navrhnute';
    case Schvalene = 'schvalene';
    case Upravene = 'upravene';
    case Zamietnute = 'zamietnute';

    public function label(): string
    {
        return match ($this) {
            self::Navrhnute => 'Navrhnuté',
            self::Schvalene => 'Schválené',
            self::Upravene => 'Upravené',
            self::Zamietnute => 'Zamietnuté',
        };
    }
}
