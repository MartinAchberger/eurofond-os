<?php

use App\Enums\DocumentVersionStatus;
use App\Livewire\Projects\DocumentsTab;
use App\Models\{Document, DocumentVersion, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('confirm version activates it and supersedes the old one', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $v1 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v2.0']);
    $v1->activate($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('confirmVersion', $v2->id)
        ->assertSet('error', null);

    expect($v2->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->status)->toBe(DocumentVersionStatus::Nahradena);
});

test('archive version from UI', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($document)->create();

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('archiveVersion', $version->id)
        ->assertSet('error', null);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka);
});

test('archiving an archived version surfaces the domain error', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($document)->create();
    $version->archive($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('archiveVersion', $version->id)
        ->assertSet('error', 'Verzia je už archivovaná.');
});

test('cannot act on another projects version', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = DocumentVersion::factory()->create(); // different project via factory chain

    expect(fn () => Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('confirmVersion', $foreign->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect($foreign->fresh()->status)->not->toBe(DocumentVersionStatus::Aktualna);
});

test('download link renders only for versions with a file', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    DocumentVersion::factory()->for($document)->create(['file_path' => 'documents/1/a.pdf', 'version_label' => 'v9.9']);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->assertSee('Stiahnuť');
});
