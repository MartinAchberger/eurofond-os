<?php

namespace App\Livewire\Pages;

use App\Enums\InboxItemStatus;
use App\Enums\RiskLevel;
use App\Enums\TaskPriority;
use App\Models\InboxItem;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Question;
use App\Models\Risk;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Inbox — EUROFOND OS')]
class InboxPage extends Component
{
    public ?int $triagingId = null;

    public string $type = 'uloha';

    public ?int $projectId = null;

    public string $body = '';

    public string $askedTo = '';

    public ?string $dueAt = null;

    public string $priority = TaskPriority::Stredna->value;

    public string $impact = RiskLevel::Stredny->value;

    public string $likelihood = RiskLevel::Stredny->value;

    public ?string $error = null;

    #[Computed]
    public function items()
    {
        return InboxItem::with('suggestedProject')
            ->latest()
            ->get();
    }

    #[Computed]
    public function projects()
    {
        return Project::orderBy('code')->get();
    }

    public function startTriage(int $itemId): void
    {
        $item = InboxItem::findOrFail($itemId);

        $this->resetTriageForm();
        $this->triagingId = $item->id;
        $this->body = $item->raw_content;
        $this->projectId = $item->suggested_project_id;
    }

    public function cancelTriage(): void
    {
        $this->resetTriageForm();
    }

    public function confirmTriage(): void
    {
        if ($this->triagingId === null) {
            return;
        }

        $validated = $this->validate([
            'type' => ['required', Rule::in(['otazka', 'uloha', 'riziko'])],
            'projectId' => ['required', 'integer', 'exists:projects,id'],
            'body' => ['required', 'string'],
            'askedTo' => [Rule::requiredIf($this->type === 'otazka'), 'nullable', 'string', 'max:255'],
            'dueAt' => ['nullable', 'date'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'impact' => ['required', Rule::enum(RiskLevel::class)],
            'likelihood' => ['required', Rule::enum(RiskLevel::class)],
        ]);

        $item = InboxItem::findOrFail($this->triagingId);

        if (in_array($item->status, [InboxItemStatus::Schvalene, InboxItemStatus::Zamietnute], true)) {
            $this->error = 'Položka inboxu je už spracovaná.';

            return;
        }

        match ($validated['type']) {
            'otazka' => Question::create([
                'project_id' => $validated['projectId'],
                'asked_by' => auth()->user()->name,
                'asked_to' => $validated['askedTo'],
                'asked_at' => now(),
                'body' => $validated['body'],
                'reason' => 'Zachytené v inboxe ('.$item->source->label().').',
                'due_at' => $this->dueAt ?: null,
                'created_by' => auth()->id(),
            ]),
            'uloha' => ProjectTask::create([
                'project_id' => $validated['projectId'],
                'title' => $validated['body'],
                'priority' => $validated['priority'],
                'due_at' => $this->dueAt ?: null,
                'note' => 'Zachytené v inboxe ('.$item->source->label().').',
            ]),
            'riziko' => Risk::create([
                'project_id' => $validated['projectId'],
                'title' => $validated['body'],
                'description' => 'Zachytené v inboxe ('.$item->source->label().').',
                'impact' => $validated['impact'],
                'likelihood' => $validated['likelihood'],
            ]),
        };

        $item->update([
            'status' => InboxItemStatus::Schvalene,
            'unconfirmed' => false,
        ]);

        $this->resetTriageForm();
        unset($this->items);
    }

    public function reject(int $itemId): void
    {
        $item = InboxItem::findOrFail($itemId);

        $item->update(['status' => InboxItemStatus::Zamietnute]);

        if ($this->triagingId === $itemId) {
            $this->resetTriageForm();
        }

        $this->error = null;
        unset($this->items);
    }

    private function resetTriageForm(): void
    {
        $this->reset('triagingId', 'projectId', 'body', 'askedTo', 'dueAt', 'error');
        $this->type = 'uloha';
        $this->priority = TaskPriority::Stredna->value;
        $this->impact = RiskLevel::Stredny->value;
        $this->likelihood = RiskLevel::Stredny->value;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pages.inbox-page');
    }
}
