<?php

use App\Enums\TaskStatus;
use App\Livewire\Projects\TasksTab;
use App\Models\{Document, DocumentVersion, Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('task created from an answer shows its origin', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = \App\Models\Question::factory()->for($project)->create(['body' => 'Je LV aktuálny?']);
    $answer = \App\Models\Answer::factory()->for($question)->create();
    ProjectTask::factory()->for($project)->create(['title' => 'Vyžiadať výpis', 'answer_id' => $answer->id]);

    Livewire::test(TasksTab::class, ['project' => $project])
        ->assertSee('Vyžiadať výpis')
        ->assertSee('Z odpovede na: Je LV aktuálny?');
});

test('tasks tab refreshes on task-created', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    $component = Livewire::test(TasksTab::class, ['project' => $project]);

    ProjectTask::factory()->for($project)->create(['title' => 'Nová úloha po udalosti']);

    $component->dispatch('task-created')
        ->assertSee('Nová úloha po udalosti');
});

test('completing a task without evidence shows the domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create();

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->call('confirmComplete')
        ->assertSet('error', 'Úlohu nemožno uzavrieť bez dôkazu.');

    expect($task->fresh()->status)->toBe(TaskStatus::Otvorena);
});

test('completing a task with evidence note closes it', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create(['title' => 'Overiť úhradu faktúry']);

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->set('evidenceNote', 'Bankový výpis č. 7/2026 priložený.')
        ->call('confirmComplete')
        ->assertSet('error', null)
        ->assertSet('completingTaskId', null);

    expect($task->fresh()->status)->toBe(TaskStatus::Hotova);
});

test('completing with a document version stores the reference', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create();
    $doc = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($doc)->create();

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->set('evidenceVersionId', $version->id)
        ->call('confirmComplete')
        ->assertSet('error', null);

    expect($task->fresh()->evidence_document_version_id)->toBe($version->id);
});
