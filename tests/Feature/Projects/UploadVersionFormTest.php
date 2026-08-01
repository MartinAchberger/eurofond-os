<?php

use App\Enums\DocumentVersionStatus;
use App\Livewire\Projects\UploadVersionForm;
use App\Models\{Document, DocumentVersion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('uploads a file and creates an unconfirmed version', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user);
    $document = Document::factory()->create();

    Livewire::test(UploadVersionForm::class, ['document' => $document])
        ->set('file', UploadedFile::fake()->create('Rozpocet_v3.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))
        ->set('versionLabel', 'v3.0')
        ->set('issuedAt', today()->toDateString())
        ->set('author', 'Ing. Novák')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('version-uploaded');

    $version = DocumentVersion::first();
    expect($version->status)->toBe(DocumentVersionStatus::Nepotvrdena)
        ->and($version->original_filename)->toBe('Rozpocet_v3.xlsx')
        ->and($version->uploaded_by)->toBe($user->id)
        ->and($version->file_path)->toStartWith('documents/'.$document->project_id.'/');
    Storage::disk('local')->assertExists($version->file_path);
});

test('rejects disallowed file types and missing label', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());
    $document = Document::factory()->create();

    Livewire::test(UploadVersionForm::class, ['document' => $document])
        ->set('file', UploadedFile::fake()->create('script.sh', 10))
        ->call('save')
        ->assertHasErrors(['file', 'versionLabel']);
});
