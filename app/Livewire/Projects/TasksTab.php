<?php

namespace App\Livewire\Projects;

use App\Enums\DocumentVersionStatus;
use App\Models\DocumentVersion;
use App\Models\Project;
use DomainException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TasksTab extends Component
{
    public Project $project;

    public ?int $completingTaskId = null;

    public ?int $evidenceVersionId = null;

    public string $evidenceNote = '';

    public ?string $error = null;

    #[Computed]
    public function tasks()
    {
        return $this->project->tasks()
            ->with(['assignee', 'evidenceDocumentVersion.document'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function evidenceVersions()
    {
        return DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereIn('status', [DocumentVersionStatus::Aktualna, DocumentVersionStatus::Nepotvrdena])
            ->with('document')
            ->get();
    }

    public function startComplete(int $taskId): void
    {
        $this->completingTaskId = $taskId;
        $this->evidenceVersionId = null;
        $this->evidenceNote = '';
        $this->error = null;
    }

    public function cancelComplete(): void
    {
        $this->reset('completingTaskId', 'evidenceVersionId', 'evidenceNote', 'error');
    }

    public function confirmComplete(): void
    {
        if ($this->completingTaskId === null) {
            return;
        }

        $task = $this->project->tasks()->findOrFail($this->completingTaskId);

        $version = null;
        if ($this->evidenceVersionId !== null) {
            $version = DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))
                ->findOrFail($this->evidenceVersionId);
        }

        try {
            $task->complete($version, $this->evidenceNote ?: null);
            $this->reset('completingTaskId', 'evidenceVersionId', 'evidenceNote', 'error');
        } catch (DomainException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.projects.tasks-tab');
    }
}
