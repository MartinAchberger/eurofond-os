<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Otvorena = 'otvorena';
    case Zodpovedana = 'zodpovedana';
    case Uzavreta = 'uzavreta';

    public function label(): string
    {
        return match ($this) {
            self::Otvorena => 'Otvorená',
            self::Zodpovedana => 'Zodpovedaná',
            self::Uzavreta => 'Uzavretá',
        };
    }
}
