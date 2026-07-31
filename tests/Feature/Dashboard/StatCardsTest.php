<?php

use App\Enums\ProjectPhase;
use App\Enums\RiskStatus;
use App\Enums\TaskStatus;
use App\Livewire\Dashboard\StatCards;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('stat cards count active projects, deadlines, risks and waiting tasks', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov, 'next_deadline' => now()->addDays(5)]);
    Project::factory()->create(['phase' => ProjectPhase::Udrzatelnost, 'next_deadline' => now()->addDays(3)]);
    Project::factory()->create(['phase' => ProjectPhase::Realizacia, 'next_deadline' => now()->addDays(30)]);
    // Risk/ProjectTask factories default to a fresh Project::factory() for project_id,
    // which would silently inflate activeProjects with orphan Screening-phase projects.
    // Bind them to an existing project so only the 3 projects above are counted.
    Risk::factory()->for($project)->create();                                    // default otvorene
    Risk::factory()->for($project)->create(['status' => RiskStatus::Uzavrete]);
    ProjectTask::factory()->for($project)->create(['status' => TaskStatus::Caka]);
    ProjectTask::factory()->for($project)->create();                             // otvorena

    Livewire::test(StatCards::class)
        ->assertSet('activeProjects', 2)      // phase < 11
        ->assertSet('upcomingDeadlines', 2)   // within 14 days (incl. the udrzatelnost one)
        ->assertSet('openRisks', 1)
        ->assertSet('waitingOnClient', 1)
        ->assertSee('Aktívne projekty')
        ->assertSee('Blížiace sa termíny')
        ->assertSee('Otvorené riziká')
        ->assertSee('Čaká sa na klienta');
});
