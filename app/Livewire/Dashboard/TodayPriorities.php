<?php

namespace App\Livewire\Dashboard;

use App\Enums\TaskStatus;
use App\Models\ProjectTask;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TodayPriorities extends Component
{
    #[Computed]
    public function tasks()
    {
        return ProjectTask::with('project')
            ->where('status', '!=', TaskStatus::Hotova)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(5)
            ->get();
    }

    public function dueLabel(ProjectTask $task): string
    {
        return match (true) {
            $task->isOverdue() => 'Po termíne',
            $task->due_at->isToday() => 'Dnes',
            $task->due_at->isTomorrow() => 'Zajtra',
            default => $task->due_at->format('j. n. Y'),
        };
    }

    public function render()
    {
        return view('livewire.dashboard.today-priorities');
    }
}
