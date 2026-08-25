<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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

    #[On('question-created')]
    public function refreshQuestions(): void
    {
        unset($this->questions);
    }

    public function render()
    {
        return view('livewire.projects.questions-tab');
    }
}
