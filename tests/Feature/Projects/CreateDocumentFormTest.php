<?php

use App\Livewire\Projects\CreateDocumentForm;
use App\Models\{Document, DocumentType, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates and creates a document for the project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $type = DocumentType::factory()->create(['name' => 'Rozpočet']);

    Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->set('title', 'Rozpočet stavby')
        ->set('documentTypeId', $type->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('document-created');

    expect(Document::count())->toBe(1)
        ->and(Document::first()->project_id)->toBe($project->id)
        ->and(Document::first()->title)->toBe('Rozpočet stavby');
});

test('close resets fields, validation, and open state', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $type = DocumentType::factory()->create();

    $component = Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->set('open', true)
        ->set('title', 'X')
        ->set('documentTypeId', $type->id)
        ->call('close')
        ->assertSet('open', false)
        ->assertSet('title', '')
        ->assertSet('documentTypeId', null);

    $component->call('open')
        ->assertSet('open', true)
        ->assertSet('title', '')
        ->assertSet('documentTypeId', null);
});

test('close clears stale validation errors', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->call('save')
        ->assertHasErrors(['title' => 'required'])
        ->call('close')
        ->assertHasNoErrors();
});
