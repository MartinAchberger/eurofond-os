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

test('waitingOn prefers the most pressing open question', function () {
    $project = Project::factory()->create();
    \App\Models\Question::factory()->for($project)->create([
        'asked_to' => 'Stavebný úrad',
        'due_at' => null,
    ]);
    \App\Models\Question::factory()->for($project)->create([
        'asked_to' => 'Obec Malé Hoste',
        'due_at' => today()->subDay(),
    ]);
    $answered = \App\Models\Question::factory()->for($project)->create(['asked_to' => 'Projektant']);
    \App\Models\Answer::factory()->for($answered)->create();

    $waiting = $project->waitingOn();

    expect($waiting['type'])->toBe('odpoved')
        ->and($waiting['label'])->toBe('Odpoveď od Obec Malé Hoste')
        ->and($waiting['overdue'])->toBeTrue();
});

test('waitingOn falls back to an unconfirmed document version', function () {
    $project = Project::factory()->create();
    $document = \App\Models\Document::factory()->for($project)->create(['title' => 'Projektová dokumentácia']);
    \App\Models\DocumentVersion::factory()->for($document)->create([
        'version_label' => 'v2.0',
        'status' => \App\Enums\DocumentVersionStatus::Nepotvrdena,
    ]);

    $waiting = $project->waitingOn();

    expect($waiting['type'])->toBe('dokument')
        ->and($waiting['label'])->toBe('Potvrdenie: Projektová dokumentácia — v2.0')
        ->and($waiting['overdue'])->toBeFalse();
});

test('waitingOn is null when nothing is pending', function () {
    $project = Project::factory()->create();
    $answered = \App\Models\Question::factory()->for($project)->create();
    \App\Models\Answer::factory()->for($answered)->create();

    expect($project->waitingOn())->toBeNull();
});
