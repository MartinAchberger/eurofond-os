<?php

namespace Database\Factories;

use App\Models\Gate;
use App\Models\GateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GateItem>
 */
class GateItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gate_id' => Gate::factory(),
            'label' => fake()->sentence(),
            'is_met' => false,
        ];
    }
}
