<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use DomainException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class QuestionsTab extends Component
{
    public Project $project;

    public ?string $error = null;

    #[Computed]
    public function questions()
    {
        return $this->project->questions()
            ->with('answers')
            ->latest('asked_at')
            ->get();
    }

    public function closeQuestion(int $questionId): void
    {
        $this->error = null;

        $question = $this->project->questions()->findOrFail($questionId);

        try {
            $question->close();
        } catch (DomainException $e) {
            $this->error = $e->getMessage();

            return;
        }

        unset($this->questions);
    }

    #[On('question-created')]
    #[On('answer-created')]
    public function refreshQuestions(): void
    {
        unset($this->questions);
    }

    public function render()
    {
        return view('livewire.projects.questions-tab');
    }
}
