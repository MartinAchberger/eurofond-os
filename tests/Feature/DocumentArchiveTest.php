<?php

use App\Enums\DocumentVersionStatus;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('archive marks version historicka and records who archived', function () {
    $user = User::factory()->create();
    $version = DocumentVersion::factory()->create();

    $version->archive($user);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka)
        ->and($version->fresh()->confirmed_by)->toBe($user->id);
});

test('an active version can be archived, leaving the document without a current version', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->for($document)->create();
    $version->activate($user);

    $version->archive($user);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka)
        ->and($document->currentVersion())->toBeNull();
});

test('archiving an already archived version throws', function () {
    $user = User::factory()->create();
    $version = DocumentVersion::factory()->create();
    $version->archive($user);
    $version->fresh()->archive($user);
})->throws(DomainException::class, 'Verzia je už archivovaná.');

test('file metadata columns are fillable', function () {
    $version = DocumentVersion::factory()->create([
        'original_filename' => 'Rozpocet_v3.xlsx',
        'file_size' => 123456,
    ]);
    expect($version->original_filename)->toBe('Rozpocet_v3.xlsx')
        ->and($version->file_size)->toBe(123456);
});
