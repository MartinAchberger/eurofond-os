<?php

use App\Enums\QuestionStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('close marks the question uzavreta', function () {
    $question = Question::factory()->create();

    $question->close();

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('an answered question can be closed', function () {
    $question = Question::factory()->create();
    Answer::factory()->for($question)->create();
    expect($question->fresh()->status)->toBe(QuestionStatus::Zodpovedana);

    $question->fresh()->close();

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('closing an already closed question throws', function () {
    $question = Question::factory()->create();
    $question->close();
    $question->fresh()->close();
})->throws(DomainException::class, 'Otázka je už uzavretá.');

test('question has a decisions relation', function () {
    $question = Question::factory()->create();
    expect($question->decisions)->toBeEmpty();
});
