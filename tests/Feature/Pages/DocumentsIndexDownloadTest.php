<?php

use App\Livewire\Pages\DocumentsIndex;
use App\Models\{Document, DocumentVersion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('documents index shows download link for current version with file', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->for($document)->create(['file_path' => 'documents/1/a.pdf']);
    $version->activate($user);

    Livewire::test(DocumentsIndex::class)->assertSee('Stiahnuť');
});

test('demo seed provides a downloadable file', function () {
    $this->seed();
    $version = App\Models\DocumentVersion::whereNotNull('file_path')->first();
    expect($version)->not->toBeNull();
    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk();
});
