<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo seed creates the mockup portfolio', function () {
    $this->seed();

    expect(User::where('email', 'denis@eurofond.test')->exists())->toBeTrue()
        ->and(Project::count())->toBeGreaterThanOrEqual(4)
        ->and(Project::where('code', 'PRJ-001')->first()->name)->toBe('Malé Hoste')
        ->and(Project::where('code', 'PRJ-005')->first()->name)->toBe('Hronská Dúbrava');
});
