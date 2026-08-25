<?php

use App\Enums\QuestionStatus;
use App\Livewire\Projects\CreateQuestionForm;
use App\Models\{Document, Project, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates required fields', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->call('save')
        ->assertHasErrors(['askedBy' => 'required', 'askedTo' => 'required', 'body' => 'required']);
});

test('creates a question for the project with optional document link', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->set('askedBy', 'Denis')
        ->set('askedTo', 'Obec Malé Hoste')
        ->set('body', 'Je list vlastníctva aktuálny?')
        ->set('reason', 'Podklad pre prílohu č. 3')
        ->set('dueAt', today()->addDays(7)->toDateString())
        ->set('documentId', $document->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('question-created');

    $question = Question::sole();
    expect($question->project_id)->toBe($project->id)
        ->and($question->status)->toBe(QuestionStatus::Otvorena)
        ->and($question->document_id)->toBe($document->id)
        ->and($question->created_by)->toBe($user->id)
        ->and($question->asked_at)->not->toBeNull();
});

test('rejects a document from another project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = Document::factory()->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->set('askedBy', 'Denis')
        ->set('askedTo', 'Obec')
        ->set('body', 'Otázka?')
        ->set('documentId', $foreign->id)
        ->call('save')
        ->assertHasErrors(['documentId']);
});
