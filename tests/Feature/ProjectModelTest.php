<?php

use App\Enums\ProjectHealth;
use App\Enums\ProjectPhase;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project belongs to client and owner, casts enums', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->create(['name' => 'Obec Malé Hoste', 'type' => 'obec']);
    $project = Project::factory()->create([
        'code' => 'PRJ-001',
        'client_id' => $client->id,
        'owner_id' => $owner->id,
        'phase' => ProjectPhase::ZberPodkladov,
        'health' => ProjectHealth::Dobre,
    ]);

    expect($project->client->name)->toBe('Obec Malé Hoste')
        ->and($project->owner->id)->toBe($owner->id)
        ->and($project->phase)->toBe(ProjectPhase::ZberPodkladov)
        ->and($project->health)->toBe(ProjectHealth::Dobre)
        ->and($client->projects)->toHaveCount(1);
});

test('project code is unique', function () {
    Project::factory()->create(['code' => 'PRJ-002']);
    Project::factory()->create(['code' => 'PRJ-002']);
})->throws(QueryException::class);

test('nextStep returns the most urgent unfinished task', function () {
    $project = Project::factory()->create();
    \App\Models\ProjectTask::factory()->for($project)->create([
        'title' => 'Hotový blokátor',
        'priority' => \App\Enums\TaskPriority::Blokator,
        'status' => \App\Enums\TaskStatus::Hotova,
        'completed_at' => now(),
        'evidence_note' => 'x',
    ]);
    \App\Models\ProjectTask::factory()->for($project)->create([
        'title' => 'Stredná úloha',
        'priority' => \App\Enums\TaskPriority::Stredna,
    ]);
    $urgent = \App\Models\ProjectTask::factory()->for($project)->create([
        'title' => 'Otvorený blokátor',
        'priority' => \App\Enums\TaskPriority::Blokator,
    ]);

    expect($project->nextStep()?->id)->toBe($urgent->id);
});

test('nextStep is null without unfinished tasks', function () {
    $project = Project::factory()->create();

    expect($project->nextStep())->toBeNull();
});
