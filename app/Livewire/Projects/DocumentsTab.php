<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentsTab extends Component
{
    public Project $project;

    #[Computed]
    public function documents()
    {
        return $this->project->documents()
            ->with(['type', 'versions' => fn ($q) => $q->latest('id'), 'versions.confirmedBy'])
            ->get();
    }

    #[On('document-created')]
    public function refreshDocuments(): void
    {
        unset($this->documents);
    }

    public function render()
    {
        return view('livewire.projects.documents-tab');
    }
}
