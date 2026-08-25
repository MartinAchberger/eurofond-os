<?php

namespace App\Livewire\Projects;

use App\Models\Answer;
use App\Models\Decision;
use Livewire\Component;

class CreateDecisionForm extends Component
{
    public Answer $answer;

    public bool $open = false;

    public string $body = '';

    public string $approvedBy = '';

    public ?string $approvedAt = null;

    public string $rationale = '';

    public function mount(): void
    {
        $this->approvedAt = today()->toDateString();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string'],
            'approvedBy' => ['required', 'string', 'max:255'],
            'approvedAt' => ['required', 'date'],
            'rationale' => ['nullable', 'string'],
        ]);

        $question = $this->answer->question;

        Decision::create([
            'project_id' => $question->project_id,
            'question_id' => $question->id,
            'answer_id' => $this->answer->id,
            'body' => $validated['body'],
            'approved_by' => $validated['approvedBy'],
            'approved_at' => $validated['approvedAt'],
            'rationale' => $this->rationale ?: null,
            'recorded_by' => auth()->id(),
        ]);

        $this->reset('body', 'approvedBy', 'rationale', 'open');
        $this->approvedAt = today()->toDateString();
        $this->resetValidation();
        $this->dispatch('decision-created');
    }

    public function render()
    {
        return view('livewire.projects.create-decision-form');
    }
}
