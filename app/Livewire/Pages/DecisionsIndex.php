<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Rozhodnutia — EUROFUND OS')]
class DecisionsIndex extends Component
{
    public function render()
    {
        return view('livewire.pages.decisions-index');
    }
}
