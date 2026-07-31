<?php

use App\Livewire\Pages\{Dnes, InboxPage, TasksIndex};
use App\Models\{InboxItem, Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('dnes splits overdue and upcoming tasks', function () {
    $this->actingAs(User::factory()->create());
    ProjectTask::factory()->create(['title' => 'Zmeškaná úloha', 'due_at' => today()->subDays(2)]);
    ProjectTask::factory()->create(['title' => 'Blízka úloha', 'due_at' => today()->addDays(3)]);

    Livewire::test(Dnes::class)
        ->assertSeeInOrder(['Po termíne', 'Zmeškaná úloha', 'Dnes a najbližšie dni', 'Blízka úloha']);
});

test('inbox lists items with unconfirmed badge', function () {
    $this->actingAs(User::factory()->create());
    InboxItem::factory()->create(['raw_content' => 'Nová výzva pre obce na zateplenie budov.']);

    Livewire::test(InboxPage::class)
        ->assertSee('Nová výzva pre obce')
        ->assertSee('Nepotvrdené')
        ->assertSee('Nové');
});

test('tasks index links tasks to their project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['code' => 'PRJ-001']);
    ProjectTask::factory()->for($project)->create(['title' => 'Skontrolovať PD']);

    Livewire::test(TasksIndex::class)
        ->assertSee('Skontrolovať PD')
        ->assertSee('PRJ-001');
});

test('remaining global pages render with demo seed', function () {
    $this->seed();
    $this->actingAs(User::factory()->create());
    foreach (['dokumenty.index', 'poziadavky.index', 'rizika.index', 'rozhodnutia.index', 'nastavenia'] as $route) {
        $this->get(route($route))->assertOk();
    }
});
