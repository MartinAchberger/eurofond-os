<?php

namespace Database\Factories;

use App\Models\Decision;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Decision>
 */
class DecisionFactory extends Factory
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
            'body' => fake()->sentence(),
            'approved_by' => fake()->name(),
            'approved_at' => now(),
            'recorded_by' => User::factory(),
        ];
    }
}
