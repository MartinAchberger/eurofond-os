<?php

use App\Enums\QuestionStatus;
use App\Livewire\Projects\QuestionsTab;
use App\Models\{Project, Question, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('close question from UI marks it uzavreta', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create();

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $question->id)
        ->assertSet('error', null);

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('closing an already closed question surfaces the domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create(['status' => QuestionStatus::Uzavreta]);

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $question->id)
        ->assertSet('error', 'Otázka je už uzavretá.');
});

test('cannot close another projects question', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = Question::factory()->create();

    expect(fn () => Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $foreign->id))
        ->toThrow(ModelNotFoundException::class);

    expect($foreign->fresh()->status)->toBe(QuestionStatus::Otvorena);
});
