<?php

namespace Database\Factories;

use App\Models\Discrepancy;
use App\Models\DiscrepancySource;
use App\Models\DocumentVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscrepancySource>
 */
class DiscrepancySourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'discrepancy_id' => Discrepancy::factory(),
            'document_version_id' => DocumentVersion::factory(),
        ];
    }
}
