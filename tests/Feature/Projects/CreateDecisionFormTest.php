<?php

use App\Livewire\Projects\CreateDecisionForm;
use App\Models\{Answer, Decision, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates required fields', function () {
    $this->actingAs(User::factory()->create());
    $answer = Answer::factory()->create();

    Livewire::test(CreateDecisionForm::class, ['answer' => $answer])
        ->set('approvedAt', '')
        ->call('save')
        ->assertHasErrors(['body' => 'required', 'approvedBy' => 'required', 'approvedAt' => 'required']);
});

test('creates a decision linked to the answer, question and project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $question = Question::factory()->create();
    $answer = Answer::factory()->for($question)->create();

    Livewire::test(CreateDecisionForm::class, ['answer' => $answer])
        ->set('body', 'Pokračujeme s aktuálnym listom vlastníctva.')
        ->set('approvedBy', 'Denis')
        ->set('approvedAt', today()->toDateString())
        ->set('rationale', 'Odpoveď OÚ potvrdila platnosť.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('decision-created');

    $decision = Decision::sole();
    expect($decision->project_id)->toBe($question->project_id)
        ->and($decision->question_id)->toBe($question->id)
        ->and($decision->answer_id)->toBe($answer->id)
        ->and($decision->recorded_by)->toBe($user->id)
        ->and($decision->rationale)->toBe('Odpoveď OÚ potvrdila platnosť.');
});
