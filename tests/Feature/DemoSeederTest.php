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

test('demo seed covers the full question lifecycle', function () {
    $this->seed();

    expect(\App\Models\Answer::count())->toBeGreaterThanOrEqual(2)
        ->and(\App\Models\Answer::where('bindingness', \App\Enums\AnswerBindingness::Zavazne)->exists())->toBeTrue()
        ->and(Decision::whereNotNull('answer_id')->exists())->toBeTrue()
        ->and(ProjectTask::whereNotNull('answer_id')->exists())->toBeTrue()
        ->and(\App\Models\Question::where('status', \App\Enums\QuestionStatus::Zodpovedana)->exists())->toBeTrue()
        ->and(\App\Models\Question::where('status', \App\Enums\QuestionStatus::Uzavreta)->exists())->toBeTrue();
});

test('demo seed shows waiting-on and gate checklist states', function () {
    $this->seed();

    $maleHoste = Project::where('code', 'PRJ-001')->first();
    $hronskaDubrava = Project::where('code', 'PRJ-005')->first();

    expect($maleHoste->waitingOn()['type'])->toBe('odpoved')
        ->and($maleHoste->waitingOn()['overdue'])->toBeTrue()
        ->and($hronskaDubrava->waitingOn()['type'])->toBe('dokument')
        ->and(\App\Models\GateItem::where('is_met', false)->exists())->toBeTrue();
});
