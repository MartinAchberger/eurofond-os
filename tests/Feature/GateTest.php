<?php

use App\Enums\GateStatus;
use App\Enums\ProjectPhase;
use App\Models\Gate;
use App\Models\GateItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gate cannot pass with unmet items', function () {
    $gate = Gate::factory()->create();
    GateItem::factory()->for($gate)->create(['is_met' => false]);
    $gate->pass(User::factory()->create());
})->throws(DomainException::class, 'Brána má nesplnené podmienky.');

test('project cannot advance phase without passed gate', function () {
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $project->advancePhase(User::factory()->create());
})->throws(DomainException::class);

test('project advances phase after gate passes', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $gate = Gate::factory()->for($project)->create(['phase' => 3]);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    $gate->pass($user);
    expect($gate->fresh()->status)->toBe(GateStatus::Prejdena);

    $project->advancePhase($user);
    expect($project->fresh()->phase)->toBe(ProjectPhase::TechnickaFinancnaKontrola);
});
