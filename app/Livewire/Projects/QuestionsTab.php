<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class QuestionsTab extends Component
{
    public Project $project;

    #[Computed]
    public function questions()
    {
        return $this->project->questions()
            ->with('answers')
            ->latest('asked_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.projects.questions-tab');
    }
}
