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
