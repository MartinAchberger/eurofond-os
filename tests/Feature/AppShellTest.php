<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login from app pages', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with([
    'dashboard', 'dnes', 'inbox', 'projekty.index', 'dokumenty.index',
    'poziadavky.index', 'ulohy.index', 'rizika.index', 'rozhodnutia.index',
    'nastavenia', 'profile',
]);

test('root redirects to dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});

test('sidebar shows all slovak sections for authenticated user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['EUROFOND OS', 'Dashboard', 'Dnes', 'Inbox', 'Projekty', 'Dokumenty', 'Požiadavky', 'Úlohy', 'Riziká', 'Rozhodnutia', 'Nastavenia']);
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

test('projekty sidebar item stays active on project detail page', function () {
    $project = Project::factory()->create();

    $html = $this->actingAs(User::factory()->create())
        ->get(route('projekty.show', $project))
        ->assertOk()
        ->getContent();

    preg_match('#<a href="'.preg_quote(route('projekty.index'), '#').'".*?</a>#s', $html, $matches);

    expect($matches)->not->toBeEmpty();
    expect($matches[0])->toContain('bg-blue-600');
});
