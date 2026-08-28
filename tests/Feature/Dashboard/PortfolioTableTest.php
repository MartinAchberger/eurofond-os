<?php

use App\Enums\ProjectPhase;
use App\Livewire\Dashboard\{AuditHistory, PortfolioTable};
use App\Models\{Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('portfolio lists active projects with status, phase and health', function () {
    $this->actingAs(User::factory()->create());
    Project::factory()->create([
        'code' => 'PRJ-001', 'name' => 'Malé Hoste',
        'status_label' => 'Čaká na PD', 'phase' => ProjectPhase::TechnickaFinancnaKontrola,
        'health' => 'dobre', 'next_deadline' => now()->addDays(3),
    ]);
    Project::factory()->create(['code' => 'PRJ-099', 'phase' => ProjectPhase::Udrzatelnost]);

    Livewire::test(PortfolioTable::class)
        ->assertSee('PRJ-001')->assertSee('Malé Hoste')->assertSee('Čaká na PD')
        ->assertSee('Technická a finančná kontrola')
        ->assertDontSee('PRJ-099'); // inactive phase 12
});

test('audit history shows recent activity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create(['code' => 'PRJ-001']);
    $project->update(['main_blocker' => 'Čaká sa na PD']);

    Livewire::test(AuditHistory::class)->assertSee('Auditná história');
});

test('portfolio shows the next step for each project', function () {
    $this->actingAs(\App\Models\User::factory()->create());
    $project = \App\Models\Project::factory()->create();
    \App\Models\ProjectTask::factory()->for($project)->create([
        'title' => 'Doložiť list vlastníctva',
        'priority' => \App\Enums\TaskPriority::Blokator,
    ]);

    Livewire::test(PortfolioTable::class)
        ->assertSee('Najbližší krok')
        ->assertSee('Doložiť list vlastníctva');
});
