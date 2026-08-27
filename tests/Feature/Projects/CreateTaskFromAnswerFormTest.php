<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Livewire\Projects\CreateTaskFromAnswerForm;
use App\Models\{Answer, ProjectTask, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates required fields', function () {
    $this->actingAs(User::factory()->create());
    $answer = Answer::factory()->create();

    Livewire::test(CreateTaskFromAnswerForm::class, ['answer' => $answer])
        ->set('priority', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required', 'priority' => 'required']);
});

test('creates a task linked to the project and answer', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $this->actingAs($user);
    $question = Question::factory()->create();
    $answer = Answer::factory()->for($question)->create();

    Livewire::test(CreateTaskFromAnswerForm::class, ['answer' => $answer])
        ->set('title', 'Vyžiadať aktuálny list vlastníctva')
        ->set('assigneeId', $assignee->id)
        ->set('priority', TaskPriority::Vysoka->value)
        ->set('dueAt', today()->addDays(5)->toDateString())
        ->set('requiredEvidence', 'Priložený výpis z katastra')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('task-created');

    $task = ProjectTask::sole();
    expect($task->project_id)->toBe($question->project_id)
        ->and($task->answer_id)->toBe($answer->id)
        ->and($task->assignee_id)->toBe($assignee->id)
        ->and($task->priority)->toBe(TaskPriority::Vysoka)
        ->and($task->required_evidence)->toBe('Priložený výpis z katastra')
        ->and($task->status)->toBe(TaskStatus::Otvorena);
});
