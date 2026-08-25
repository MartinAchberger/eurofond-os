<?php

use App\Enums\AnswerBindingness;
use App\Enums\RiskLevel;
use App\Livewire\Projects\{DocumentsTab, QuestionsTab, RisksTab};
use App\Models\{Answer, Document, DocumentVersion, Project, Question, Risk, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('documents tab shows version history with statuses', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $doc = Document::factory()->for($project)->create(['title' => 'Projektová dokumentácia']);
    $v1 = DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.2']);
    $v1->activate($user);
    $v2->activate($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->assertSee('Projektová dokumentácia')
        ->assertSee('v1.0')->assertSee('Nahradená')
        ->assertSee('v1.2')->assertSee('Aktuálna');
});

test('questions tab shows answers with bindingness', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $q = Question::factory()->for($project)->create(['body' => 'Kedy dodáme stanovisko VO?']);
    Answer::factory()->for($q)->create(['body' => 'Do konca augusta.', 'bindingness' => AnswerBindingness::Zavazne]);

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSee('Kedy dodáme stanovisko VO?')
        ->assertSee('Do konca augusta.')
        ->assertSee('Záväzné')->assertSee('Zodpovedaná');
});

test('questions tab embeds the answer form and refreshes on answer-created', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create();

    $component = Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSeeLivewire(\App\Livewire\Projects\AnswerQuestionForm::class);

    Answer::factory()->for($question)->create(['body' => 'Nová odpoveď po udalosti.']);

    $component->dispatch('answer-created')
        ->assertSee('Nová odpoveď po udalosti.');
});

test('risks tab lists project risks', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Risk::factory()->for($project)->create(['title' => 'Stará PD nesedí s rozpočtom', 'impact' => RiskLevel::Vysoky]);

    Livewire::test(RisksTab::class, ['project' => $project])
        ->assertSee('Stará PD nesedí s rozpočtom')->assertSee('Vysoký');
});
