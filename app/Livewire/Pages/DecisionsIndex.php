<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofond')]
#[Title('Rozhodnutia — EUROFOND OS')]
class DecisionsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.decisions-index');
    }
}
