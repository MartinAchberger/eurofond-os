<?php

namespace App\Livewire\Pages;

use App\Models\Question;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Požiadavky — EUROFOND OS')]
class QuestionsIndex extends Component
{
    #[Computed]
    public function questions()
    {
        return Question::with('project')
            ->latest('asked_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.questions-index');
    }
}
