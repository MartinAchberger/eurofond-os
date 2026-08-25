<?php

use App\Enums\AnswerBindingness;
use App\Enums\QuestionStatus;
use App\Livewire\Projects\AnswerQuestionForm;
use App\Models\{Answer, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates required fields', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create();

    Livewire::test(AnswerQuestionForm::class, ['question' => $question])
        ->set('answeredAt', '')
        ->call('save')
        ->assertHasErrors(['answeredBy' => 'required', 'answeredAt' => 'required', 'body' => 'required']);
});

test('records an answer and marks the question as answered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $question = Question::factory()->create();

    Livewire::test(AnswerQuestionForm::class, ['question' => $question])
        ->set('answeredBy', 'Ing. Kováčová, OÚ Bánovce')
        ->set('answeredAt', today()->toDateString())
        ->set('body', 'List vlastníctva je aktuálny k 15. 8. 2026.')
        ->set('source', 'e-mail z 20. 8. 2026')
        ->set('bindingness', AnswerBindingness::Zavazne->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('answer-created');

    $answer = Answer::sole();
    expect($answer->question_id)->toBe($question->id)
        ->and($answer->bindingness)->toBe(AnswerBindingness::Zavazne)
        ->and($answer->source)->toBe('e-mail z 20. 8. 2026')
        ->and($answer->recorded_by)->toBe($user->id)
        ->and($question->refresh()->status)->toBe(QuestionStatus::Zodpovedana);
});

test('rejects an invalid bindingness value', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create();

    Livewire::test(AnswerQuestionForm::class, ['question' => $question])
        ->set('answeredBy', 'Denis')
        ->set('answeredAt', today()->toDateString())
        ->set('body', 'Odpoveď.')
        ->set('bindingness', 'neplatne')
        ->call('save')
        ->assertHasErrors(['bindingness']);
});

test('refuses to record an answer on a closed question', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create(['status' => QuestionStatus::Uzavreta]);

    Livewire::test(AnswerQuestionForm::class, ['question' => $question])
        ->set('answeredBy', 'Denis')
        ->set('answeredAt', today()->toDateString())
        ->set('body', 'Odpoveď.')
        ->call('save')
        ->assertHasErrors(['body'])
        ->assertNotDispatched('answer-created');

    expect(Answer::count())->toBe(0);
});
