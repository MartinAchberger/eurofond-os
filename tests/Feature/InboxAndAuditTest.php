<?php

use App\Enums\AiSuggestionStatus;
use App\Enums\InboxItemStatus;
use App\Models\AiSuggestion;
use App\Models\InboxItem;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

test('inbox item defaults to nove and unconfirmed', function () {
    $item = InboxItem::factory()->create();
    expect($item->status)->toBe(InboxItemStatus::Nove)
        ->and($item->unconfirmed)->toBeTrue();
});

test('ai suggestion stores payload and starts as navrhnute', function () {
    $s = AiSuggestion::factory()->create(['payload' => ['typ' => 'dokument', 'istota' => 0.4]]);
    expect($s->status)->toBe(AiSuggestionStatus::Navrhnute)
        ->and($s->payload['typ'])->toBe('dokument');
});

test('project changes are audit-logged', function () {
    $project = Project::factory()->create();
    $project->update(['main_blocker' => 'Čaká sa na novú PD']);
    expect(Activity::where('subject_type', Project::class)->count())->toBeGreaterThanOrEqual(1);
});
