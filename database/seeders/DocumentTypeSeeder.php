<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Seed the document type catalogue.
     */
    public function run(): void
    {
        $names = [
            'PD',
            'Rozpočet',
            'Energetické hodnotenie',
            'LV',
            'Zmluva',
            'Faktúra',
            'Stanovisko VO',
            'Iné',
        ];

        foreach ($names as $name) {
            DocumentType::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
