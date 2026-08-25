<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Question;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateQuestionForm extends Component
{
    public Project $project;

    public bool $open = false;

    public string $askedBy = '';

    public string $askedTo = '';

    public string $body = '';

    public string $reason = '';

    public ?string $dueAt = null;

    public ?int $documentId = null;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'askedBy' => ['required', 'string', 'max:255'],
            'askedTo' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
            'dueAt' => ['nullable', 'date'],
            'documentId' => [
                'nullable',
                'integer',
                Rule::exists('documents', 'id')->where('project_id', $this->project->id),
            ],
        ]);

        Question::create([
            'project_id' => $this->project->id,
            'asked_by' => $validated['askedBy'],
            'asked_to' => $validated['askedTo'],
            'body' => $validated['body'],
            'reason' => $this->reason ?: null,
            'due_at' => $this->dueAt ?: null,
            'document_id' => $this->documentId,
            'asked_at' => now(),
            'created_by' => auth()->id(),
        ]);

        $this->reset('askedBy', 'askedTo', 'body', 'reason', 'dueAt', 'documentId', 'open');
        $this->resetValidation();
        $this->dispatch('question-created');
    }

    #[Computed]
    public function documents()
    {
        return $this->project->documents()->orderBy('title')->get();
    }

    public function render()
    {
        return view('livewire.projects.create-question-form');
    }
}
