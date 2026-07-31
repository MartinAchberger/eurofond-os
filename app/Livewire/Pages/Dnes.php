<?php

namespace App\Livewire\Pages;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Dnes — EUROFOND OS')]
class Dnes extends Component
{
    #[Computed]
    public function overdueTasks()
    {
        return ProjectTask::with('project')
            ->where('status', '!=', TaskStatus::Hotova)
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', today())
            ->orderBy('due_at')
            ->get();
    }

    #[Computed]
    public function upcomingTasks()
    {
        return ProjectTask::with('project')
            ->where('status', '!=', TaskStatus::Hotova)
            ->whereBetween('due_at', [today(), today()->addDays(7)])
            ->orderBy('due_at')
            ->get();
    }

    #[Computed]
    public function upcomingProjectDeadlines()
    {
        return Project::whereBetween('next_deadline', [today(), today()->addDays(14)])
            ->orderBy('next_deadline')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.dnes');
    }
}
