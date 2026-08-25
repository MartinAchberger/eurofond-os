<?php

namespace App\Livewire\Projects;

use App\Enums\AnswerBindingness;
use App\Enums\QuestionStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AnswerQuestionForm extends Component
{
    public Question $question;

    public bool $open = false;

    public string $answeredBy = '';

    public ?string $answeredAt = null;

    public string $body = '';

    public string $source = '';

    public string $bindingness = AnswerBindingness::Pracovne->value;

    public function mount(): void
    {
        $this->answeredAt = today()->toDateString();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'answeredBy' => ['required', 'string', 'max:255'],
            'answeredAt' => ['required', 'date'],
            'body' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'bindingness' => ['required', Rule::enum(AnswerBindingness::class)],
        ]);

        if ($this->question->status === QuestionStatus::Uzavreta) {
            $this->addError('body', 'Uzavretá otázka už nemôže dostať odpoveď.');

            return;
        }

        Answer::create([
            'question_id' => $this->question->id,
            'answered_by' => $validated['answeredBy'],
            'answered_at' => $validated['answeredAt'],
            'body' => $validated['body'],
            'source' => $this->source ?: null,
            'bindingness' => $validated['bindingness'],
            'recorded_by' => auth()->id(),
        ]);

        $this->reset('answeredBy', 'body', 'source', 'open');
        $this->bindingness = AnswerBindingness::Pracovne->value;
        $this->answeredAt = today()->toDateString();
        $this->resetValidation();
        $this->dispatch('answer-created');
    }

    public function render()
    {
        return view('livewire.projects.answer-question-form');
    }
}
