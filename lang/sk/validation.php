<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slovenské validačné hlásenia
    |--------------------------------------------------------------------------
    |
    | Preklady pre validačné pravidlá, ktoré sa reálne používajú v aplikácii
    | (formuláre dokumentov, verzií a projektov). Rozsah je zámerne užší
    | ako pôvodný anglický súbor — dopĺňajte podľa potreby pri pridávaní
    | ďalších pravidiel.
    |
    */

    'required' => 'Pole :attribute je povinné.',
    'string' => 'Pole :attribute musí byť reťazec.',
    'integer' => 'Pole :attribute musí byť celé číslo.',
    'file' => 'Pole :attribute musí byť súbor.',
    'date' => 'Pole :attribute musí byť platný dátum.',
    'exists' => 'Vybraná hodnota poľa :attribute nie je platná.',
    'mimes' => 'Pole :attribute musí byť súbor typu: :values.',
    'mimetypes' => 'Pole :attribute musí byť súbor typu: :values.',
    'extensions' => 'Pole :attribute musí mať jednu z nasledujúcich prípon: :values.',

    'max' => [
        'array' => 'Pole :attribute nesmie obsahovať viac ako :max položiek.',
        'file' => 'Pole :attribute nesmie byť väčšie ako :max kilobajtov.',
        'numeric' => 'Pole :attribute nesmie byť väčšie ako :max.',
        'string' => 'Pole :attribute nesmie byť dlhšie ako :max znakov.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vlastné validačné hlásenia
    |--------------------------------------------------------------------------
    */

    'custom' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Názvy polí (attributes)
    |--------------------------------------------------------------------------
    |
    | Čitateľné slovenské názvy polí použité v hláseniach vyššie namiesto
    | technických názvov premenných z formulárov.
    |
    */

    'attributes' => [
        'title' => 'názov',
        'versionLabel' => 'označenie verzie',
        'documentTypeId' => 'typ dokumentu',
        'file' => 'súbor',
        'issuedAt' => 'dátum vydania',
        'author' => 'autor',
        'priority' => 'priorita',
        'dueAt' => 'termín',
        'projectId' => 'projekt',
        'assigneeId' => 'zodpovedná osoba',
        'evidenceNote' => 'dôkaz',
        'q' => 'hľadanie',
    ],

];
