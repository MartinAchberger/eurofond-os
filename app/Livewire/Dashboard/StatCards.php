<?php

namespace App\Livewire\Dashboard;

use App\Enums\ProjectPhase;
use App\Enums\RiskStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Risk;
use Livewire\Component;

class StatCards extends Component
{
    public int $activeProjects;

    public int $upcomingDeadlines;

    public int $openRisks;

    public int $waitingOnClient;

    public function mount(): void
    {
        $this->activeProjects = Project::where('phase', '<', ProjectPhase::Ukoncenie->value)->count();
        $this->upcomingDeadlines = Project::whereBetween('next_deadline', [today(), today()->addDays(14)])->count();
        $this->openRisks = Risk::where('status', RiskStatus::Otvorene)->count();
        $this->waitingOnClient = ProjectTask::where('status', TaskStatus::Caka)->count();
    }

    public function render()
    {
        return view('livewire.dashboard.stat-cards');
    }
}
