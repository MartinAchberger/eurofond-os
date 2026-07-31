<?php

use App\Livewire\Tasks\CreateTaskModal;
use App\Models\{Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('create task modal validates and creates a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $this->actingAs($user);

    Livewire::test(CreateTaskModal::class)
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    Livewire::test(CreateTaskModal::class)
        ->set('title', 'Skontrolovať PD a doplniť podklady')
        ->set('projectId', $project->id)
        ->set('assigneeId', $user->id)
        ->set('priority', 'vysoka')
        ->set('dueAt', today()->addDays(5)->toDateString())
        ->set('note', 'Podľa checklistu.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('open', false)
        ->assertDispatched('task-created');

    expect(ProjectTask::count())->toBe(1)
        ->and(ProjectTask::first()->title)->toBe('Skontrolovať PD a doplniť podklady')
        ->and(ProjectTask::first()->priority->value)->toBe('vysoka');
});

test('invalid priority is rejected', function () {
    $this->actingAs(User::factory()->create());
    Livewire::test(CreateTaskModal::class)
        ->set('title', 'X')->set('priority', 'extra')
        ->call('save')
        ->assertHasErrors(['priority']);
});

test('clearing the due date creates a task with no due date, not today', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateTaskModal::class)
        ->set('title', 'Úloha bez termínu')
        ->set('dueAt', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(ProjectTask::first()->due_at)->toBeNull();
});

test('close resets stale validation errors from a previous failed save', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(CreateTaskModal::class)
        ->call('save')
        ->assertHasErrors(['title']);

    $component->call('close')->assertHasNoErrors();
});

test('empty string project selection coerces to no project, not a validation error', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateTaskModal::class)
        ->set('title', 'Úloha bez projektu')
        ->set('projectId', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(ProjectTask::first()->project_id)->toBeNull();
});

test('open-create-task event opens the modal', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateTaskModal::class)
        ->assertSet('open', false)
        ->dispatch('open-create-task')
        ->assertSet('open', true);
});
