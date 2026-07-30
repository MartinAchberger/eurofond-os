<?php

namespace Database\Factories;

use App\Models\AiSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSuggestion>
 */
class AiSuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => 'inbox_klasifikacia',
            'project_id' => null,
            'payload' => ['typ' => 'dokument', 'istota' => 0.5],
        ];
    }
}
