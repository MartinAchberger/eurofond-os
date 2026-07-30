<?php

namespace App\Enums;

enum AiSuggestionKind: string
{
    case InboxKlasifikacia = 'inbox_klasifikacia';
    case KrizovaKontrola = 'krizova_kontrola';
    case NavrhTextu = 'navrh_textu';
    case Priorizacia = 'priorizacia';

    public function label(): string
    {
        return match ($this) {
            self::InboxKlasifikacia => 'Inbox klasifikácia',
            self::KrizovaKontrola => 'Krížová kontrola',
            self::NavrhTextu => 'Návrh textu',
            self::Priorizacia => 'Prioritizácia',
        };
    }
}
