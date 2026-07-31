<?php

namespace App\Livewire\Pages;

use App\Models\ProjectTask;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Úlohy — EUROFOND OS')]
class TasksIndex extends Component
{
    #[Computed]
    public function tasks()
    {
        return ProjectTask::with('project')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.tasks-index');
    }
}
