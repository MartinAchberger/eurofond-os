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

test('questions tab shows decisions and refreshes on decision-created', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create();
    $answer = Answer::factory()->for($question)->create();

    $component = Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSeeLivewire(\App\Livewire\Projects\CreateDecisionForm::class);

    \App\Models\Decision::factory()->create([
        'project_id' => $project->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'body' => 'Rozhodnutie po odpovedi.',
    ]);

    $component->dispatch('decision-created')
        ->assertSee('Rozhodnutie po odpovedi.');
});

test('risks tab lists project risks', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Risk::factory()->for($project)->create(['title' => 'Stará PD nesedí s rozpočtom', 'impact' => RiskLevel::Vysoky]);

    Livewire::test(RisksTab::class, ['project' => $project])
        ->assertSee('Stará PD nesedí s rozpočtom')->assertSee('Vysoký');
});

test('overview tab shows the next step banner', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    \App\Models\ProjectTask::factory()->for($project)->create([
        'title' => 'Doložiť list vlastníctva',
        'priority' => \App\Enums\TaskPriority::Blokator,
    ]);

    Livewire::test(\App\Livewire\Projects\OverviewTab::class, ['project' => $project])
        ->assertSee('Najbližší krok')
        ->assertSee('Doložiť list vlastníctva');
});

test('overview tab next step banner without tasks shows placeholder', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(\App\Livewire\Projects\OverviewTab::class, ['project' => $project])
        ->assertSee('Žiadna otvorená úloha');
});

test('questions tab shows the response deadline and overdue marker', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Question::factory()->for($project)->create([
        'body' => 'Zmeškaná otázka?',
        'due_at' => today()->subDays(2),
    ]);

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSee('Termín na odpoveď')
        ->assertSee('Po termíne');
});

test('answered question past deadline is not marked overdue', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create([
        'body' => 'Zodpovedaná po termíne?',
        'due_at' => today()->subDays(2),
    ]);
    Answer::factory()->for($question)->create();

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSee('Termín na odpoveď')
        ->assertDontSee('Po termíne');
});

test('overview tab shows what the project waits on', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Question::factory()->for($project)->create(['asked_to' => 'Stavebný úrad']);

    Livewire::test(\App\Livewire\Projects\OverviewTab::class, ['project' => $project])
        ->assertSee('Čaká na')
        ->assertSee('Odpoveď od Stavebný úrad');
});
