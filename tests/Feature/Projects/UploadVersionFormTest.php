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
        ->and($version->file_path)->toStartWith('documents/'.$document->project_id.'/')
        ->and($version->file_size)->toBeGreaterThan(0);
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

test('accepts a legacy OLE2 .doc file', function () {
    // Legacy .doc/.xls files are OLE2 compound documents. finfo reports these as
    // application/x-ole-storage (or application/CDFV2), which the old `mimes` rule had no
    // mapping for — they'd be rejected purely because of the underlying binary format, even
    // though the extension is exactly what we accept. This pins that these files upload cleanly.
    Storage::fake('local');
    $this->actingAs(User::factory()->create());
    $document = Document::factory()->create();

    $ole2Bytes = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1".str_repeat("\x00", 512);

    Livewire::test(UploadVersionForm::class, ['document' => $document])
        ->set('file', UploadedFile::fake()->createWithContent('stara-ziadost.doc', $ole2Bytes))
        ->set('versionLabel', 'v1.0')
        ->call('save')
        ->assertHasNoErrors('file');
});
