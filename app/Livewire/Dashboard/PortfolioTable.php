<?php

namespace App\Livewire\Dashboard;

use App\Enums\ProjectPhase;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PortfolioTable extends Component
{
    #[Computed]
    public function projects()
    {
        return Project::with('client')
            ->where('phase', '<', ProjectPhase::Ukoncenie->value)
            ->orderBy('next_deadline')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.portfolio-table');
    }
}
