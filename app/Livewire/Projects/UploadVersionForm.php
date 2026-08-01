<?php

namespace App\Livewire\Projects;

use App\Models\Document;
use App\Models\DocumentVersion;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadVersionForm extends Component
{
    use WithFileUploads;

    public Document $document;

    public bool $open = false;

    public $file = null;

    public string $versionLabel = '';

    public ?string $issuedAt = null;

    public string $author = '';

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->reset('open', 'file', 'versionLabel', 'issuedAt', 'author');
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
            'versionLabel' => ['required', 'string', 'max:50'],
            'issuedAt' => ['nullable', 'date'],
            'author' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $this->file->store('documents/'.$this->document->project_id, 'local');

        DocumentVersion::create([
            'document_id' => $this->document->id,
            'version_label' => $validated['versionLabel'],
            'file_path' => $path,
            'original_filename' => $this->file->getClientOriginalName(),
            'file_size' => $this->file->getSize(),
            'issued_at' => $validated['issuedAt'] ?: null,
            'author' => $validated['author'] ?: null,
            'uploaded_by' => auth()->id(),
        ]);

        $this->reset('open', 'file', 'versionLabel', 'issuedAt', 'author');
        $this->resetValidation();
        $this->dispatch('version-uploaded');
    }

    public function render()
    {
        return view('livewire.projects.upload-version-form');
    }
}
