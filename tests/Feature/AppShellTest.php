<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login from app pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('root redirects to dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});

test('sidebar shows all slovak sections for authenticated user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['EUROFUND OS', 'Dashboard', 'Dnes', 'Inbox', 'Projekty', 'Dokumenty', 'Požiadavky', 'Úlohy', 'Riziká', 'Rozhodnutia', 'Nastavenia']);
});

test('every page route renders', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertOk();
})->with(['dnes', 'inbox', 'projekty.index', 'dokumenty.index', 'poziadavky.index', 'ulohy.index', 'rizika.index', 'rozhodnutia.index', 'nastavenia']);

test('project show renders project code', function () {
    $project = Project::factory()->create(['code' => 'PRJ-777']);
    $this->actingAs(User::factory()->create())
        ->get(route('projekty.show', $project))
        ->assertOk()
        ->assertSee('PRJ-777');
});
