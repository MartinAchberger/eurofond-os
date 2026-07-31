<?php

use App\Models\Decision;
use App\Models\InboxItem;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo seed creates the mockup portfolio', function () {
    $this->seed();

    expect(User::where('email', 'denis@eurofond.test')->exists())->toBeTrue()
        ->and(Project::count())->toBeGreaterThanOrEqual(4)
        ->and(Project::where('code', 'PRJ-001')->first()->name)->toBe('Malé Hoste')
        ->and(Project::where('code', 'PRJ-005')->first()->name)->toBe('Hronská Dúbrava')
        ->and(\Spatie\Activitylog\Models\Activity::count())->toBeGreaterThan(0)
        ->and(InboxItem::count())->toBeGreaterThanOrEqual(8)
        ->and(Decision::count())->toBeGreaterThanOrEqual(2)
        ->and(ProjectTask::where('due_at', '<', today())->exists())->toBeTrue()
        ->and(Project::whereBetween('next_deadline', [today(), today()->addDays(14)])->count())->toBeGreaterThanOrEqual(2);
});
