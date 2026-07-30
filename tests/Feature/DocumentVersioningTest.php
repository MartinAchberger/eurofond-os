<?php

use App\Enums\DocumentVersionStatus;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('activating a new version supersedes the old one, nothing is deleted', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    $v1 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v2.0']);

    $v1->activate($user);
    expect($v1->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->confirmed_by)->toBe($user->id);

    $v2->activate($user);
    expect($v2->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->status)->toBe(DocumentVersionStatus::Nahradena)
        ->and($document->versions()->count())->toBe(2)
        ->and($document->currentVersion()->id)->toBe($v2->id);
});

test('new versions start as nepotvrdena', function () {
    $v = DocumentVersion::factory()->create();
    expect($v->status)->toBe(DocumentVersionStatus::Nepotvrdena);
});

test('supersede transition is audit-logged', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    $v1 = DocumentVersion::factory()->for($document)->create();
    $v2 = DocumentVersion::factory()->for($document)->create();
    $v1->activate($user);
    $v2->activate($user);

    expect(\Spatie\Activitylog\Models\Activity::where('subject_type', DocumentVersion::class)
        ->where('subject_id', $v1->id)
        ->get()
        ->contains(fn ($a) => data_get($a->attribute_changes, 'attributes.status') === 'nahradena'))->toBeTrue();
});
