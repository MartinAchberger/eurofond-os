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

    $response = $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk()
        ->assertDownload('Projektová dokumentácia v1.2.pdf');

    $disposition = $response->headers->get('content-disposition');
    expect($disposition)->toContain("filename*=UTF-8''");
});

test('control characters in original filename are stripped from Content-Disposition', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/1/evil.pdf', 'obsah');
    $version = DocumentVersion::factory()->create([
        'file_path' => 'documents/1/evil.pdf',
        'original_filename' => "zla\rverzia\n.pdf",
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk();

    $disposition = $response->headers->get('content-disposition');

    expect($disposition)->not->toBeNull();
    expect($disposition)->toContain('zlaverzia.pdf');
    expect($disposition)->not->toContain("\r");
    expect($disposition)->not->toContain("\n");
});

test('version without file returns 404', function () {
    $version = DocumentVersion::factory()->create(['file_path' => null]);
    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertNotFound();
});

test('response has hardening headers', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/1/abc.pdf', 'obsah');
    $version = DocumentVersion::factory()->create([
        'file_path' => 'documents/1/abc.pdf',
        'original_filename' => 'abc.pdf',
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk();

    expect($response->headers->get('x-content-type-options'))->toBe('nosniff');
    expect($response->headers->get('cache-control'))->toContain('private')
        ->and($response->headers->get('cache-control'))->toContain('no-store');
});

test('file_path outside the documents directory is rejected', function () {
    Storage::fake('local');
    Storage::disk('local')->put('secrets/passwords.txt', 'obsah');
    $version = DocumentVersion::factory()->create(['file_path' => 'secrets/passwords.txt']);

    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertNotFound();
});

// Pins the v1 authorization contract: EUROFOND OS is a single-PM app with no per-project
// membership/ownership concept yet, so ANY authenticated user may download ANY document
// version — there's no meaningful boundary to enforce until roles/ownership are introduced.
// This test must be revisited (and very likely made stricter) once that lands.
test('any authenticated user can download a version regardless of project ownership', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/1/abc.pdf', 'obsah');
    $version = DocumentVersion::factory()->create([
        'file_path' => 'documents/1/abc.pdf',
        'original_filename' => 'abc.pdf',
    ]);

    $unrelatedUser = User::factory()->create();

    $this->actingAs($unrelatedUser)
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk();
});
