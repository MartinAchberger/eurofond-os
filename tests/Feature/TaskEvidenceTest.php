<?php

use App\Enums\TaskStatus;
use App\Models\DocumentVersion;
use App\Models\ProjectTask;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('task cannot be completed without evidence', function () {
    $task = ProjectTask::factory()->create();
    $task->complete();
})->throws(DomainException::class, 'Úlohu nemožno uzavrieť bez dôkazu.');

test('task completes with document evidence', function () {
    $task = ProjectTask::factory()->create();
    $version = DocumentVersion::factory()->create();

    $task->complete(evidence: $version);

    expect($task->fresh()->status)->toBe(TaskStatus::Hotova)
        ->and($task->fresh()->evidence_document_version_id)->toBe($version->id)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

test('task completes with written evidence note', function () {
    $task = ProjectTask::factory()->create();
    $task->complete(evidenceNote: 'Bankový výpis priložený v spise č. 12');
    expect($task->fresh()->status)->toBe(TaskStatus::Hotova);
});
