<?php

use App\Enums\InboxItemStatus;
use App\Enums\QuestionStatus;
use App\Enums\RiskLevel;
use App\Enums\TaskPriority;
use App\Livewire\Pages\InboxPage;
use App\Models\{InboxItem, Project, ProjectTask, Question, Risk, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('startTriage prefills content and suggested project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $item = InboxItem::factory()->create([
        'raw_content' => 'Obec posiela aktualizovaný rozpočet.',
        'suggested_project_id' => $project->id,
    ]);

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->assertSet('triagingId', $item->id)
        ->assertSet('body', 'Obec posiela aktualizovaný rozpočet.')
        ->assertSet('projectId', $project->id);
});

test('triage creates a question and approves the item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $item = InboxItem::factory()->create(['raw_content' => 'Kedy bude podpísaný dodatok?']);

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->set('type', 'otazka')
        ->set('projectId', $project->id)
        ->set('body', 'Kedy bude podpísaný dodatok č. 1?')
        ->set('askedTo', 'Obec Malé Hoste')
        ->set('dueAt', today()->addDays(7)->toDateString())
        ->call('confirmTriage')
        ->assertSet('error', null)
        ->assertSet('triagingId', null);

    $question = Question::sole();
    expect($question->project_id)->toBe($project->id)
        ->and($question->asked_to)->toBe('Obec Malé Hoste')
        ->and($question->asked_by)->toBe($user->name)
        ->and($question->status)->toBe(QuestionStatus::Otvorena)
        ->and($question->created_by)->toBe($user->id)
        ->and($item->fresh()->status)->toBe(InboxItemStatus::Schvalene)
        ->and($item->fresh()->unconfirmed)->toBeFalse();
});

test('triage creates a task and approves the item', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $item = InboxItem::factory()->create(['raw_content' => 'Doplniť energetický certifikát.']);

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->set('type', 'uloha')
        ->set('projectId', $project->id)
        ->set('body', 'Doplniť energetický certifikát')
        ->set('priority', TaskPriority::Vysoka->value)
        ->set('dueAt', today()->addDays(3)->toDateString())
        ->call('confirmTriage')
        ->assertSet('error', null);

    $task = ProjectTask::sole();
    expect($task->project_id)->toBe($project->id)
        ->and($task->title)->toBe('Doplniť energetický certifikát')
        ->and($task->priority)->toBe(TaskPriority::Vysoka)
        ->and($item->fresh()->status)->toBe(InboxItemStatus::Schvalene);
});

test('triage creates a risk and approves the item', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $item = InboxItem::factory()->create(['raw_content' => 'VO môže byť napadnuté.']);

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->set('type', 'riziko')
        ->set('projectId', $project->id)
        ->set('body', 'VO môže byť napadnuté')
        ->set('impact', RiskLevel::Vysoky->value)
        ->set('likelihood', RiskLevel::Stredny->value)
        ->call('confirmTriage')
        ->assertSet('error', null);

    $risk = Risk::sole();
    expect($risk->project_id)->toBe($project->id)
        ->and($risk->impact)->toBe(RiskLevel::Vysoky)
        ->and($item->fresh()->status)->toBe(InboxItemStatus::Schvalene);
});

test('triage requires a project and question requires asked_to', function () {
    $this->actingAs(User::factory()->create());
    $item = InboxItem::factory()->create();

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->set('type', 'otazka')
        ->set('projectId', null)
        ->set('askedTo', '')
        ->call('confirmTriage')
        ->assertHasErrors(['projectId' => 'required', 'askedTo' => 'required']);

    expect(Question::count())->toBe(0)
        ->and($item->fresh()->status)->toBe(InboxItemStatus::Nove);
});

test('reject marks the item zamietnute', function () {
    $this->actingAs(User::factory()->create());
    $item = InboxItem::factory()->create();

    Livewire::test(InboxPage::class)
        ->call('reject', $item->id)
        ->assertSet('error', null);

    expect($item->fresh()->status)->toBe(InboxItemStatus::Zamietnute);
});

test('an already processed item cannot be triaged again', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $item = InboxItem::factory()->create(['status' => InboxItemStatus::Schvalene]);

    Livewire::test(InboxPage::class)
        ->call('startTriage', $item->id)
        ->set('type', 'uloha')
        ->set('projectId', $project->id)
        ->set('body', 'Duplicitné spracovanie')
        ->call('confirmTriage')
        ->assertSet('error', 'Položka inboxu je už spracovaná.');

    expect(ProjectTask::count())->toBe(0);
});
