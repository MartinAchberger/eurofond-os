<?php

use App\Livewire\Pages\ProjectsIndex;
use App\Models\{Client, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('projects index lists and filters projects', function () {
    $this->actingAs(User::factory()->create());
    $obec = Client::factory()->create(['name' => 'Obec Malé Hoste']);
    Project::factory()->create(['code' => 'PRJ-001', 'name' => 'Malé Hoste', 'client_id' => $obec->id]);
    Project::factory()->create(['code' => 'PRJ-002', 'name' => 'Tornaľa']);

    Livewire::test(ProjectsIndex::class)
        ->assertSee('PRJ-001')->assertSee('PRJ-002')
        ->set('q', 'Malé')
        ->assertSee('PRJ-001')->assertDontSee('PRJ-002');
});
