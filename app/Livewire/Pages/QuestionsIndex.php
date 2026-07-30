<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Požiadavky — EUROFOND OS')]
class QuestionsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.questions-index');
    }
}
