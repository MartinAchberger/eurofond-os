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
