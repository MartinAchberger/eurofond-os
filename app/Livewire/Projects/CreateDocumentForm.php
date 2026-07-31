<?php

namespace App\Livewire\Projects;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateDocumentForm extends Component
{
    public Project $project;

    public bool $open = false;

    public string $title = '';

    public ?int $documentTypeId = null;

    #[On('open-create-document')]
    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'documentTypeId' => ['required', 'integer', 'exists:document_types,id'],
        ]);

        Document::create([
            'project_id' => $this->project->id,
            'document_type_id' => $validated['documentTypeId'],
            'title' => $validated['title'],
        ]);

        $this->reset('open', 'title', 'documentTypeId');
        $this->resetValidation();
        $this->dispatch('document-created');
    }

    #[Computed]
    public function documentTypes()
    {
        return DocumentType::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.projects.create-document-form');
    }
}
