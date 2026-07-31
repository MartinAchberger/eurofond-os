<?php

use App\Enums\TaskStatus;
use App\Livewire\Dashboard\TodayPriorities;
use App\Models\{ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('priorities list open tasks by due date with Dnes/Zajtra labels', function () {
    $this->actingAs(User::factory()->create());
    ProjectTask::factory()->create(['title' => 'Dnešná úloha', 'due_at' => today()]);
    ProjectTask::factory()->create(['title' => 'Zajtrajšia úloha', 'due_at' => today()->addDay()]);
    ProjectTask::factory()->create(['title' => 'Hotová vec', 'due_at' => today(), 'status' => TaskStatus::Hotova, 'completed_at' => now()]);

    Livewire::test(TodayPriorities::class)
        ->assertSeeInOrder(['Dnešná úloha', 'Zajtrajšia úloha'])
        ->assertSee('Dnes')->assertSee('Zajtra')
        ->assertDontSee('Hotová vec');
});
