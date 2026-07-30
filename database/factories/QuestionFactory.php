<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
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
            'asked_by' => fake()->name(),
            'asked_to' => fake()->name(),
            'asked_at' => now(),
            'body' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
