<?php

namespace Database\Factories;

use App\Enums\ProjectPhase;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'PRJ-'.str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => fake()->sentence(3),
            'client_id' => Client::factory(),
            'owner_id' => User::factory(),
            'phase' => ProjectPhase::Screening,
            'health' => 'dobre',
        ];
    }
}
