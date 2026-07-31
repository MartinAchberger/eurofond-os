<?php

namespace App\Livewire\Tasks;

use App\Enums\TaskPriority;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateTaskModal extends Component
{
    public bool $open = false;

    public string $title = '';

    public ?int $projectId = null;

    public ?int $assigneeId = null;

    public string $priority = 'stredna';

    public ?string $dueAt = null;

    public string $note = '';

    public string $requiredEvidence = '';

    #[On('open-create-task')]
    public function open(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->reset('open', 'title', 'projectId', 'assigneeId', 'dueAt', 'note', 'requiredEvidence');
        $this->priority = 'stredna';
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'projectId' => ['nullable', 'integer', 'exists:projects,id'],
            'assigneeId' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'dueAt' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'requiredEvidence' => ['nullable', 'string'],
        ]);

        ProjectTask::create([
            'title' => $validated['title'],
            'project_id' => $validated['projectId'],
            'assignee_id' => $validated['assigneeId'],
            'priority' => $validated['priority'],
            'due_at' => $validated['dueAt'] ?: null,
            'note' => $validated['note'] ?: null,
            'required_evidence' => $validated['requiredEvidence'] ?: null,
        ]);

        $this->reset('open', 'title', 'projectId', 'assigneeId', 'dueAt', 'note', 'requiredEvidence');
        $this->priority = 'stredna';
        $this->resetValidation();
        $this->dispatch('task-created');
    }

    #[Computed]
    public function projects()
    {
        return Project::orderBy('code')->get();
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.tasks.create-task-modal');
    }
}
