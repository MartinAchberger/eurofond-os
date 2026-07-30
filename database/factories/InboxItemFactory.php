<?php

namespace Database\Factories;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InboxItem>
 */
class InboxItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => 'email',
            'raw_content' => fake()->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}
