<?php

namespace App\Livewire\Projects;

use App\Enums\TaskPriority;
use App\Models\Answer;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateTaskFromAnswerForm extends Component
{
    public Answer $answer;

    public bool $open = false;

    public string $title = '';

    public ?int $assigneeId = null;

    public string $priority = TaskPriority::Stredna->value;

    public ?string $dueAt = null;

    public string $requiredEvidence = '';

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigneeId' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'dueAt' => ['nullable', 'date'],
            'requiredEvidence' => ['nullable', 'string'],
        ]);

        ProjectTask::create([
            'project_id' => $this->answer->question->project_id,
            'answer_id' => $this->answer->id,
            'title' => $validated['title'],
            'assignee_id' => $validated['assigneeId'],
            'priority' => $validated['priority'],
            'due_at' => $this->dueAt ?: null,
            'required_evidence' => $this->requiredEvidence ?: null,
        ]);

        $this->reset('title', 'assigneeId', 'dueAt', 'requiredEvidence', 'open');
        $this->priority = TaskPriority::Stredna->value;
        $this->resetValidation();
        $this->dispatch('task-created');
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.projects.create-task-from-answer-form');
    }
}
