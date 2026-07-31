<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use DomainException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PhasesTab extends Component
{
    public Project $project;

    public ?string $error = null;

    #[Computed]
    public function gates()
    {
        return $this->project->gates()
            ->with('items')
            ->get()
            ->keyBy('phase');
    }

    public function advance(): void
    {
        try {
            $this->project->advancePhase(auth()->user());
            $this->error = null;
            $this->project->refresh();
            unset($this->gates);
        } catch (DomainException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function passGate(int $gateId): void
    {
        $gate = $this->project->gates()->findOrFail($gateId);

        try {
            $gate->pass(auth()->user());
            $this->error = null;
            $this->project->refresh();
            unset($this->gates);
        } catch (DomainException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.projects.phases-tab');
    }
}
