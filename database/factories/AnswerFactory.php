<?php

namespace Database\Factories;

use App\Enums\AnswerBindingness;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'answered_by' => fake()->name(),
            'answered_at' => now(),
            'body' => fake()->sentence(),
            'bindingness' => AnswerBindingness::Pracovne,
            'recorded_by' => User::factory(),
        ];
    }
}
