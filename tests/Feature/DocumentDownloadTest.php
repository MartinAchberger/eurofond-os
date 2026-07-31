<?php

use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guest cannot download', function () {
    $version = DocumentVersion::factory()->create(['file_path' => 'documents/1/x.pdf']);
    $this->get(route('dokumenty.stiahnut', $version))->assertRedirect(route('login'));
});

test('authenticated user downloads with original filename', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/1/abc.pdf', 'obsah');
    $version = DocumentVersion::factory()->create([
        'file_path' => 'documents/1/abc.pdf',
        'original_filename' => 'Projektová dokumentácia v1.2.pdf',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk()
        ->assertDownload('Projektová dokumentácia v1.2.pdf');
});

test('version without file returns 404', function () {
    $version = DocumentVersion::factory()->create(['file_path' => null]);
    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertNotFound();
});
