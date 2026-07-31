<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RisksTab extends Component
{
    public Project $project;

    #[Computed]
    public function risks()
    {
        return $this->project->risks()
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.projects.risks-tab');
    }
}
