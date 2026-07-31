<?php

use App\Enums\QuestionStatus;
use App\Livewire\Pages\ProjectShow;
use App\Livewire\Projects\OverviewTab;
use App\Models\{Document, DocumentVersion, Gate, GateItem, Project, ProjectTask, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('workspace header shows project identity and tabs', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['code' => 'PRJ-005', 'name' => 'Hronská Dúbrava', 'status_label' => 'Rozpočet / audit']);

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->assertSee('PRJ-005')->assertSee('Hronská Dúbrava')->assertSee('Rozpočet / audit')
        ->assertSeeInOrder(['Prehľad', 'Dokumenty', 'Požiadavky', 'Úlohy', 'Riziká', 'Fázy']);
});

test('overview tab shows source of truth, missing evidence, questions and gate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $doc = Document::factory()->for($project)->create(['title' => 'Projektový zámer']);
    DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.2'])->activate($user);
    ProjectTask::factory()->for($project)->create(['title' => 'Doložiť LV', 'required_evidence' => 'Výpis z LV']);
    Question::factory()->for($project)->create(['body' => 'Je rozpočet v súlade s usmernením?', 'status' => QuestionStatus::Otvorena]);
    $gate = Gate::factory()->for($project)->create(['phase' => $project->phase->value, 'name' => 'Brána 1 – Screening']);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    Livewire::test(OverviewTab::class, ['project' => $project])
        ->assertSee('Zdroj pravdy')->assertSee('Projektový zámer')->assertSee('v1.2')
        ->assertSee('Chýbajúce podklady')->assertSee('Doložiť LV')
        ->assertSee('Otvorené otázky')->assertSee('Je rozpočet v súlade s usmernením?')
        ->assertSee('Kontrolná brána')->assertSee('Brána 1 – Screening');
});

test('overview tab CTAs switch the parent tab', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->assertSeeHtml('wire:click="$parent.set(\'tab\', \'dokumenty\')"')
        ->assertSeeHtml('wire:click="$parent.set(\'tab\', \'poziadavky\')"')
        ->assertSeeHtml('wire:click="$parent.set(\'tab\', \'ulohy\')"')
        ->assertSeeHtml('wire:click="$parent.set(\'tab\', \'fazy\')"');
});

test('unknown tab query falls back to overview', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->set('tab', 'xyz')
        ->assertSee('Zdroj pravdy');
});
