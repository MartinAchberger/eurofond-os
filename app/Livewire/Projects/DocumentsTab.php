<?php

namespace App\Livewire\Projects;

use App\Models\DocumentVersion;
use App\Models\Project;
use DomainException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DocumentsTab extends Component
{
    public Project $project;

    public ?string $error = null;

    #[Computed]
    public function documents()
    {
        return $this->project->documents()
            ->with(['type', 'versions' => fn ($q) => $q->latest('id'), 'versions.confirmedBy'])
            ->get();
    }

    #[On('document-created')]
    #[On('version-uploaded')]
    public function refreshDocuments(): void
    {
        unset($this->documents);
    }

    public function confirmVersion(int $versionId): void
    {
        $version = DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))
            ->findOrFail($versionId);

        try {
            $version->activate(auth()->user());
            $this->error = null;
            unset($this->documents);
        } catch (DomainException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function archiveVersion(int $versionId): void
    {
        $version = DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))
            ->findOrFail($versionId);

        try {
            $version->archive(auth()->user());
            $this->error = null;
            unset($this->documents);
        } catch (DomainException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.projects.documents-tab');
    }
}
