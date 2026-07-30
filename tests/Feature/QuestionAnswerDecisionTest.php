<?php

use App\Enums\AnswerBindingness;
use App\Enums\QuestionStatus;
use App\Models\Answer;
use App\Models\Decision;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recording an answer marks question as zodpovedana', function () {
    $question = Question::factory()->create();
    expect($question->status)->toBe(QuestionStatus::Otvorena);

    Answer::factory()->for($question)->create(['bindingness' => AnswerBindingness::Zavazne]);

    expect($question->fresh()->status)->toBe(QuestionStatus::Zodpovedana)
        ->and($question->answers()->first()->bindingness)->toBe(AnswerBindingness::Zavazne);
});

test('decision can reference question and answer', function () {
    $answer = Answer::factory()->create();
    $decision = Decision::factory()->create([
        'question_id' => $answer->question_id,
        'answer_id' => $answer->id,
    ]);
    expect($decision->answer->id)->toBe($answer->id)
        ->and($decision->question->id)->toBe($answer->question_id);
});
