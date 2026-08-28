<?php

namespace App\Livewire\Projects;

use App\Enums\DocumentVersionStatus;
use App\Enums\QuestionStatus;
use App\Enums\TaskStatus;
use App\Models\Gate;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OverviewTab extends Component
{
    public Project $project;

    #[Computed]
    public function nextStep()
    {
        return $this->project->nextStep();
    }

    #[Computed]
    public function currentVersions()
    {
        return $this->project->documents()
            ->with(['versions' => fn ($q) => $q->where('status', DocumentVersionStatus::Aktualna)])
            ->get()
            ->filter(fn ($d) => $d->versions->isNotEmpty());
    }

    #[Computed]
    public function missingEvidence()
    {
        return $this->project->tasks()
            ->where('status', '!=', TaskStatus::Hotova)
            ->whereNotNull('required_evidence')
            ->get();
    }

    #[Computed]
    public function openQuestions()
    {
        return $this->project->questions()
            ->where('status', QuestionStatus::Otvorena)
            ->latest('asked_at')
            ->get();
    }

    #[Computed]
    public function currentGate(): ?Gate
    {
        return $this->project->gates()
            ->where('phase', $this->project->phase->value)
            ->first();
    }

    public function render()
    {
        return view('livewire.projects.overview-tab');
    }
}
