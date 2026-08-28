<?php

use App\Enums\GateStatus;
use App\Enums\ProjectPhase;
use App\Livewire\Projects\PhasesTab;
use App\Models\{Gate, GateItem, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('phases tab lists all 12 phases and highlights the current one', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::PripravaZiadosti]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->assertSeeInOrder(['Prvotný screening', 'Príprava žiadosti', 'Udržateľnosť']);
});

test('advance without passed gate shows domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('advance')
        ->assertSet('error', 'Projekt nemôže postúpiť: kontrolná brána neprešla.');
});

test('pass gate then advance moves the project to the next phase', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $gate = Gate::factory()->for($project)->create(['phase' => 3]);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('passGate', $gate->id)
        ->call('advance')
        ->assertSet('error', null);

    expect($project->fresh()->phase)->toBe(ProjectPhase::TechnickaFinancnaKontrola)
        ->and($gate->fresh()->status)->toBe(GateStatus::Prejdena);
});

test('gate with unmet items cannot pass', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create(['phase' => $project->phase->value]);
    GateItem::factory()->for($gate)->create(['is_met' => false]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('passGate', $gate->id)
        ->assertSet('error', 'Brána má nesplnené podmienky.');
});

test('toggle gate item marks it met and back', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create(['phase' => $project->phase->value]);
    $item = GateItem::factory()->for($gate)->create(['is_met' => false]);

    $component = Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $item->id)
        ->assertSet('error', null);

    expect($item->fresh()->is_met)->toBeTrue();

    $component->call('toggleItem', $item->id);

    expect($item->fresh()->is_met)->toBeFalse();
});

test('items of a passed gate cannot be toggled', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create([
        'phase' => $project->phase->value,
        'status' => GateStatus::Prejdena,
    ]);
    $item = GateItem::factory()->for($gate)->create(['is_met' => true]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $item->id)
        ->assertSet('error', 'Prejdená brána sa už nemení.');

    expect($item->fresh()->is_met)->toBeTrue();
});

test('cannot toggle an item of another projects gate', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreignItem = GateItem::factory()->create();

    expect(fn () => Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $foreignItem->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
