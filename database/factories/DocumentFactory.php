<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'document_type_id' => DocumentType::factory(),
            'title' => fake()->sentence(3),
        ];
    }
}
